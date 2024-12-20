<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class PaymentMethods
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param string|null $websiteCode
     * @return array
     */
    public function getDisableMethods($websiteCode = null): array
    {
        $methods = $this->scopeConfig->getValue(
            'kount360/paymentmethods/disable_methods',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
        );
        return !empty($methods) ? explode(',', $methods) : [];
    }
}
