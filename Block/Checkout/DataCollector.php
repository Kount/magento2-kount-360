<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Block\Checkout;

class DataCollector extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        private \Magento\Framework\View\Element\Template\Context $context,
        private \Kount\Kount360\Model\Session $kountSession,
        private \Kount\Kount360\Model\Config\Account $configAccount,
        private \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        private \Kount\Kount360\Model\Logger $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\View\Element\Template
     */
    protected function _prepareLayout()
    {
        $this->logger->info('Data collector block.');
        $this->kountSession->incrementKountSessionId();
        $this->logger->info('Setting Kount session ID: ' . $this->kountSession->getKountSessionId());
        return parent::_prepareLayout();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->configAccount->isEnabled();
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->configAccount->isTestMode();
    }

    /**
     * @return string
     */
    public function getJsDataCollectorUrl(): string
    {
        return $this->generateJsUrl();
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->configAccount->getDataCollectorWidth();
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->configAccount->getDataCollectorHeight();
    }

    /**
     * @return string
     */
    protected function _toHtml(): string
    {
        return $this->isAvailable()
            ? parent::_toHtml()
            : '';
    }

    /**
     * @return bool
     */
    private function isAvailable(): bool
    {
        if (!$this->configAccount->isEnabled()) {
            $this->logger->info('Kount extension is disabled in system configuration, skipping action.');
            return false;
        }

        if (!$this->configAccount->isAvailable()) {
            $this->logger->info('Kount is not configured, skipping action.');
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    private function generateJsUrl(): string
    {
        return sprintf(
            '%s/collect/sdk?m=%s&s=%s',
            $this->configAccount->getDataCollectorUrl(),
            $this->configAccount->getClientId(),
            $this->kountSession->getKountSessionId()
        );
    }
}
