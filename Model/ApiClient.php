<?php
/**
 * Copyright (c) 2026 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Kount\Kount360\Helper\Data;
use Kount\Kount360\Model\Config\Account;
use Kount\Kount360\Model\Config\Authorization;
use Kount\Kount360\Model\Config\Log as ConfigLog;
use Kount\Kount360\Model\Lib\PayloadSanitizer;

class ApiClient
{
    const DEFAULT_TIMEOUT = 20;

    const DEFAULT_CONNECT_TIMEOUT = 10;

    protected string $accessToken = '';

    /** @var Client */
    private $httpClient;

    private bool $authenticationRetried = false;

    /**
     * @param \Kount\Kount360\Model\Config\Account $configAccount
     * @param \Kount\Kount360\Model\Logger $logger
     * @param \Kount\Kount360\Model\Config\Authorization $authorizationConfig
     * @param \GuzzleHttp\ClientFactory|null $clientFactory
     * @param \Kount\Kount360\Model\Lib\PayloadSanitizer|null $payloadSanitizer
     * @param \Kount\Kount360\Model\Config\Log|null $configLog
     * @param array $clientConfig
     */
    public function __construct(
        private Account $configAccount,
        private Logger $logger,
        private Authorization $authorizationConfig,
        private ?ClientFactory $clientFactory = null,
        private ?PayloadSanitizer $payloadSanitizer = null,
        private ?ConfigLog $configLog = null,
        private array $clientConfig = []
    ) {
        $config = $this->clientConfig + [
            'timeout' => self::DEFAULT_TIMEOUT,
            'connect_timeout' => self::DEFAULT_CONNECT_TIMEOUT,
        ];

        $this->httpClient = $this->clientFactory !== null
            ? $this->clientFactory->create(['config' => $config])
            : new Client($config);
    }

    /**
     * @param string $label
     * @param mixed $payload
     * @return void
     */
    private function logPayload(string $label, $payload): void
    {
        if ($this->configLog === null || !$this->configLog->isRisMetricsEnabled()) {
            return;
        }

        $sanitized = $this->payloadSanitizer !== null
            ? $this->payloadSanitizer->sanitize($payload)
            : '[payload logging unavailable]';

        $this->logger->info($label . ': ' . $sanitized);
    }

    /**
     * @param bool $refresh
     * @return void
     * @throws \Exception
     */
    public function authenticate($refresh = false): void
    {
        $authUrl = $this->configAccount->getAuthUrl();
        $accessToken = $this->authorizationConfig->getAccessToken();
        if (!empty($accessToken) && !$refresh) {
            $this->accessToken = $accessToken;
            return;
        }

        $this->accessToken = '';

        // Only a refresh consumes the retry budget. A cold-start mint (no cached token --
        // the state every merchant is in right after RemoveLegacyAccessTokens runs) must not
        // burn it, or the one designed post-401 refresh would throw instead of retrying.
        if ($refresh) {
            if ($this->authenticationRetried) {
                throw new Exception('Kount Authentication failed. Please ensure API Key is Valid');
            }

            $this->authenticationRetried = true;
        }

        try {
            $authenticationRequest = [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->configAccount->getApiKey(),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'scope' => 'k1_integration_api',
                ],
            ];
            $this->logger->info('Kount Authentication Request Initiated');
            $this->logger->info('Start Request Time:' . gmdate('Y-m-d\TH:i:s\Z'));
            $response = $this->httpClient->request('POST', $authUrl, $authenticationRequest);
            $this->logger->info('End Request Time:' . gmdate('Y-m-d\TH:i:s\Z'));
            $data = json_decode($response->getBody()->getContents(), true);
            $this->logger->info('Kount Authentication Response Received');
            if (!isset($data['access_token'])) {
                throw new Exception('Kount Authentication failed. Access token not found in response.');
            }

            $this->accessToken = $data['access_token'];
            $this->authorizationConfig->setAccessToken($this->accessToken);
        } catch (RequestException $e) {
            $this->logger->error('Kount Authentication on Request error: ' . $e->getMessage());
            throw new Exception('Kount Authentication failed. Check error logs for details');
        } catch (GuzzleException $e) {
            $this->logger->error('Kount Authentication error: ' . $e->getMessage());
            throw new Exception('Kount Authentication failed. Check error logs for details');
        }
    }

    /**
     * Make a POST request to the API
     *
     * @param string $url
     * @param array $body
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($action, $url, $body = [])
    {
        if (!$this->accessToken) {
            $this->authenticate();
        }
        try {
            $this->logPayload('Kount 360 POST Request', $body);
            $this->logger->info('Start Request Time:' . gmdate('Y-m-d\TH:i:s\Z'));
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'riskInquiry' => 'true',
                    'excludeDevice' => 'false',
                ],
                'json' => $body,
            ]);
            $this->logger->info('End Request Time:' . gmdate('Y-m-d\TH:i:s\Z'));
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) {
                $this->logger->info('Kount 360 POST Authentication error, retrying...');
                $this->authenticate(true);
                return $this->post($action, $url, $body);
            }
            if ($e->hasResponse()) {
                $this->logPayload('Kount 360 Full Request', $body);
                $this->logPayload('Kount 360 Full Response', $e->getResponse()->getBody()->getContents());
            } else {
                $this->logger->error('Kount 360 Error: No response received from the server.');
            }
            throw new Exception('Kount 360 Authentication error: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            if ($this->getStatusCode($e) === 500) {
                $responseBody = $e->getMessage();
                if (stripos($responseBody, 'invalid token') !== false || stripos($responseBody, '401') !== false) {
                    $this->logger->info('Kount 360 401.04 Authentication error, retrying...');
                    $this->authenticate(true);
                    return $this->post($action, $url, $body);
                }
            }
            $this->logger->error('POST request failed during action ' . $action . ': ' . $e->getMessage());
            throw new Exception('Kount 360 Failed to Update during  ' . $action);
        }
    }

    public function patch($action, $url, $body = [])
    {
        if (!$this->accessToken) {
            $this->authenticate();
        }

        try {
            $this->logPayload('Kount 360 PATCH Request', $body);
            $this->logger->info('Start Request Time:' . gmdate('Y-m-d\TH:i:s\Z'));
            $response = $this->httpClient->request('PATCH', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);
            $this->logger->info('End Request Time:' . gmdate('Y-m-d\TH:i:s\Z'));
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) {
                $this->logger->info('Kount 360 PATCH Authentication error, retrying...');
                $this->authenticate(true);
                return $this->patch($action, $url, $body);
            }
            if ($e->hasResponse()) {
                $this->logPayload('Kount 360 Full Response', $e->getResponse()->getBody()->getContents());
            } else {
                $this->logger->error('Kount 360 Error: No response received from the server.');
            }
            throw new Exception('Kount 360 Authentication error: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            if ($this->getStatusCode($e) === 500) {
                $responseBody = $e->getMessage();
                if (stripos($responseBody, 'invalid token') !== false || stripos($responseBody, '401') !== false) {
                    $this->logger->info('Kount 360 401.04 Authentication error, retrying...');
                    $this->authenticate(true);
                    return $this->patch($action, $url, $body);
                }
            }
            if ($this->hasResponse($e)) {
                $this->logPayload('Kount 360 Full Request', $body);
                $this->logPayload('Kount 360 Full Response', $e->getResponse()->getBody()->getContents());
            } else {
                $this->logger->error('Kount 360 Error: No response received from the server.');
            }
            throw new Exception('Kount 360 Failed to Update during  ' . $action);
        }
    }

    /**
     * @param \GuzzleHttp\Exception\GuzzleException $e
     * @return bool
     */
    private function hasResponse(GuzzleException $e): bool
    {
        return $e instanceof RequestException && $e->hasResponse();
    }

    /**
     * @param \GuzzleHttp\Exception\GuzzleException $e
     * @return int|null
     */
    private function getStatusCode(GuzzleException $e): ?int
    {
        return $this->hasResponse($e) ? $e->getResponse()->getStatusCode() : null;
    }
}
