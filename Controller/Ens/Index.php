<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Controller\Ens;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;

class Index extends Action implements \Magento\Framework\App\CsrfAwareActionInterface, HttpPostActionInterface
{
    const MAX_LOGGED_PAYLOAD_LENGTH = 8192;

    public function __construct(
        protected \Magento\Framework\App\Action\Context $context,
        protected \Kount\Kount360\Model\Config\Account $configAccount,
        protected \Kount\Kount360\Model\Config\Ens $configEns,
        protected \Kount\Kount360\Model\Ens\Manager $ensManager,
        protected \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        protected \Kount\Kount360\Model\Logger $logger,
        protected \Kount\Kount360\Model\Config\Log $configLog
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            if (!$this->isEnabled()) {
                throw new LocalizedException(__('ENS is not enabled.'));
            }

            if (!$this->isAllowed()) {
                throw new AuthenticationException(
                    __(
                        'Invalid ENS IP Address: %1. Please ensure you whitelist this IP address'
                        . ' in the Magento Kount configuration settings',
                        $this->remoteAddress->getRemoteAddress()
                    )
                );
            }

            $rawInput = file_get_contents('php://input');
            $this->logger->debug(__('Raw JSON Input: %1', $this->sanitizeForLog($rawInput)));

            if (empty($rawInput)) {
                throw new LocalizedException(__('ENS Update Failed: No data received.'));
            }

            $jsonData = json_decode($rawInput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new LocalizedException(__('ENS Update Failed: Invalid JSON data received. Error: %1', [json_last_error_msg()]));
            }

            if (!is_array($jsonData)) {
                throw new LocalizedException(__('ENS Update Failed: Decoded JSON is not an array.'));
            }

            if ($this->configLog->isRisMetricsEnabled()) {
                $this->logger->info(
                    'Validated Received JSON Data: ' . $this->sanitizeForLog(json_encode($jsonData))
                );
            }

            $this->respondOnReceiptOfEvents();
            $response = $this->ensManager->handleRequest($jsonData);
            $this->logger->info($response);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->critical($e);
            $response = $this->ensManager->generateResponse(0, 1);
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setHeader('Content-Type', 'application/json');
        $resultRaw->setContents(json_encode(['response' => $response]));
        return $resultRaw;
    }

    /**
     * @param string|false $payload
     * @return string
     */
    protected function sanitizeForLog($payload): string
    {
        if (!is_string($payload)) {
            return '';
        }

        if (strlen($payload) > self::MAX_LOGGED_PAYLOAD_LENGTH) {
            $payload = substr($payload, 0, self::MAX_LOGGED_PAYLOAD_LENGTH) . '… [truncated]';
        }

        return str_replace('<?', '< ?', $payload);
    }

    /**
     * @return bool
     */
    protected function isEnabled(): bool
    {
        return $this->configEns->isEnabled();
    }

    /**
     * @return bool
     */
    protected function isAllowed(): bool
    {
        return $this->configEns->isAllowedIp($this->remoteAddress->getRemoteAddress());
    }

    /**
     * Create response upon receipt of request instead of after processing.  The initial request can be added to a queue
     * and processed via cron at a later date, for now, we will just respond upon receipt and keep processing going now.
     * For now, we will send response now and and keep session alive to process data.
     *
     * @return void
     */
    protected function respondOnReceiptOfEvents(): void
    {
        ob_start();
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        ob_end_flush();
        if(ob_get_level() > 0){
            ob_flush();
            flush();
        }
        if (session_id()) {
            session_write_close();
        }
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
