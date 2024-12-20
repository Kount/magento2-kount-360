<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Log
{
    const FILENAME = 'kount360.log';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param string|null $websiteCode
     * @return bool
     */
    public function isEnabled($websiteCode = null): bool
    {
        return $this->scopeConfig->isSetFlag('kount360/log/enabled', ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }

    /**
     * @return bool
     */
    public function isRisMetricsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('kount360/log/ris_metrics_enabled');
    }
}
