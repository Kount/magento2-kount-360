<?php
/**
 * Copyright (c) 2026 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Kount\Kount360\Helper\Data;
use Kount\Kount360\Model\Config\Account;
use Kount\Kount360\Model\Config\Authorization;

class ApiClient
{
    protected string $accessToken = '';

    /** @var Client */
    private $httpClient;

    private bool $authenticationRetried = false;

    /**
     * @param \Kount\Kount360\Model\Config\Account $configAccount
     * @param \Kount\Kount360\Model\Logger $logger
     * @param \Kount\Kount360\Model\Config\Authorization $authorizationConfig
     */
    public function __construct(
        private Account $configAccount,
        private Logger $logger,
        private Authorization $authorizationConfig
    ) {
        $this->httpClient = new Client();
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

        if ($this->authenticationRetried) {
            throw new Exception('Kount Authentication failed. Please ensure API Key is Valid');
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
                throw new Exception(
                    'Kount Authentication failed. Access token not found in response. Response: ' . json_encode($data)
                );
            }

            $this->accessToken = $data['access_token'];
            $this->authorizationConfig->setAccessToken($this->accessToken);
            $this->authenticationRetried = true;
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
            $this->logger->info('Kount 360 POST Request: ' . json_encode($body));
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
                $responseBody = $e->getResponse()->getBody()->getContents();
                $this->logger->error('Kount 360 Full Request: ' . json_encode($body));
                $this->logger->error('Kount 360 Full Response: ' . $responseBody);
            } else {
                $this->logger->error('Kount 360 Error: No response received from the server.');
            }
            throw new Exception('Kount 360 Authentication error: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            if ($e->getResponse()->getStatusCode() === 500) {
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
            $this->logger->info('Kount 360 PATCH Request: ' . json_encode($body));
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
                $responseBody = $e->getResponse()->getBody()->getContents();
                $this->logger->error('Kount 360 Full Response: ' . $responseBody);
            } else {
                $this->logger->error('Kount 360 Error: No response received from the server.');
            }
            throw new Exception('Kount 360 Authentication error: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            if ($e->getResponse()->getStatusCode() === 500) {
                $responseBody = $e->getMessage();
                if (stripos($responseBody, 'invalid token') !== false || stripos($responseBody, '401') !== false) {
                    $this->logger->info('Kount 360 401.04 Authentication error, retrying...');
                    $this->authenticate(true);
                    return $this->patch($action, $url, $body);
                }
            }
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $this->logger->error('Kount 360 Full Request: ' . json_encode($body));
                $this->logger->error('Kount 360 Full Response: ' . $responseBody);
            } else {
                $this->logger->error('Kount 360 Error: No response received from the server.');
            }
            throw new Exception('Kount 360 Failed to Update during  ' . $action);
        }
    }
}
