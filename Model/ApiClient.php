<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class ApiClient
{
    /** @var Client */
    private $httpClient;

    protected string $accessToken = '';

    private bool $authenticationRetried = false;

    /**
     * @param \Kount\Kount360\Model\Config\Account $configAccount
     * @param \Kount\Kount360\Helper\Data $helperData
     * @param \Kount\Kount360\Model\Logger $logger
     * @param \Kount\Kount360\Model\Config\Authorization $authorizationConfig
     */
    public function __construct(
        private \Kount\Kount360\Model\Config\Account $configAccount,
        private \Kount\Kount360\Helper\Data $helperData,
        private \Kount\Kount360\Model\Logger $logger,
        private \Kount\Kount360\Model\Config\Authorization $authorizationConfig
    ) {
        $this->httpClient = new Client();
    }

    /**
     * @param $refresh
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
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
            throw new \Exception('Kount Authentication failed. Please ensure API Key is Valid');
        }

        try {
            $response = $this->httpClient->request('POST', $authUrl, [
                'headers' => [
                    'Authorization' => 'Basic' . $this->configAccount->getApiKey(),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'scope' => 'k1_integration_api',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['access_token'])) {
                throw new \Exception('Kount Authentication failed. Check error logs for details');
            }

            $this->accessToken = $data['access_token'];
            $this->authorizationConfig->setAccessToken($this->accessToken);
            $this->authenticationRetried = true;
        } catch (RequestException $e) {
            $this->logger->error('Kount Authentication on Request error: ' . $e->getMessage());
            throw new \Exception('Kount Authentication failed. Check error logs for details');
        } catch (GuzzleException $e) {
            $this->logger->error('Kount Authentication error: ' . $e->getMessage());
            throw new \Exception('Kount Authentication failed. Check error logs for details');
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
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) {
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
            throw new \Exception('Kount 360 Authentication error');

        } catch (GuzzleException $e) {
            $this->logger->error('POST request failed during action ' . $action . ': ' . $e->getMessage());
            throw new \Exception('Kount 360 Failed to Update during  ' . $action);
        }
    }


    public function patch($action, $url, $body = [])
    {
        if (!$this->accessToken) {
            $this->authenticate();
        }

        try {
            $response = $this->httpClient->request('PATCH', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) {
                $this->authenticate(true);
                return $this->patch($action, $url, $body);
            }
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $this->logger->error('Kount 360 Full Response: ' . $responseBody);
            } else {
                $this->logger->error('Kount 360 Error: No response received from the server.');
            }
            throw new \Exception('Kount 360 Authentication error');
        }
        catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $this->logger->error('Kount 360 Full Request: ' . json_encode($body));
                $this->logger->error('Kount 360 Full Response: ' . $responseBody);
            } else {
                $this->logger->error('Kount 360 Error: No response received from the server.');
            }
            throw new \Exception('Kount 360 Failed to Update during  ' . $action);
        }
    }
}
