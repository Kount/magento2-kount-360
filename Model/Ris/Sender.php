<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris;

class Sender
{
    /**
     * @var \Kount\Kount360\Model\Config\Account
     */
    protected $configAccount;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Kount\Kount360\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Kount\Kount360\Model\Logger
     */
    protected $logger;

    /**
     * @param \Kount\Kount360\Model\Config\Account $configAccount
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Kount\Kount360\Helper\Data $helperData
     * @param \Kount\Kount360\Model\Logger $logger
     */
    public function __construct(
        \Kount\Kount360\Model\Config\Account $configAccount,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Kount\Kount360\Helper\Data $helperData,
        \Kount\Kount360\Model\Logger $logger
    ) {
        $this->configAccount = $configAccount;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @return bool|\Kount_Ris_Response
     */
    public function send(\Magento\Framework\DataObject $request)
    {
        try {
            $response = $this->getResponse($request);
        } catch (\Kount_Ris_Exception $e) {
            $this->logger->error('Exception while making RIS request: ' . $e->getMessage());
            return false;
        }

        return $response;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @return \Kount_Ris_Response
     * @throws \Kount_Ris_Exception
     */
    protected function getResponse(\Magento\Framework\DataObject $request)
    {
        $response = $request->getResponse();
        if (!$response) {
            throw new \Kount_Ris_Exception('Invalid response from Kount RIS.');
        }

        $this->checkAndLogError($response);
        $this->checkAndLogWarnings($response);
        $this->checkAndLogErrorCode($response);
        return $response;
    }

    /**
     * @param \Kount_Ris_Response $response
     * @return $this
     */
    protected function checkAndLogError($response)
    {
        $errors = $response->getErrors();
        foreach ($errors as $error) {
            $this->logger->error('RIS returned error: ' . $error);
        }
        return $this;
    }

    /**
     * @param \Kount_Ris_Response $response
     * @return $this
     */
    protected function checkAndLogErrorCode($response)
    {
        if ($response->getErrorCode() !== null) {
            $this->logger->warning('RIS returned error code: ' . $response->getErrorCode());
        }
    }

    /**
     * @param \Kount_Ris_Response $response
     * @return $this
     */
    protected function checkAndLogWarnings($response)
    {
        $warnings = $response->getWarnings();
        foreach ($warnings as $warning) {
            $this->logger->warning('RIS returned warning: ' . $warning);
        }
        return $this;
    }
}
