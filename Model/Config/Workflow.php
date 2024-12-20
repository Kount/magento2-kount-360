<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Workflow
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param string|null $storeCode
     * @return string
     */
    public function getWorkflowMode($storeCode = null): string
    {
        return $this->scopeConfig->getValue('kount360/workflow/workflow_mode', ScopeInterface::SCOPE_STORE, $storeCode);
    }

    /**
     * @param string|null $storeCode
     * @return string
     */
    public function getDeclineAction($storeCode = null): string
    {
        return $this->scopeConfig->getValue('kount360/workflow/decline_action', ScopeInterface::SCOPE_STORE, $storeCode);
    }

    /**
     * @param string|null $storeCode
     * @return bool
     */
    public function isNotifyProcessorDecline($storeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'kount360/workflow/notify_processor_decline',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * @param string|null $storeCode
     * @return bool
     */
    public function isPreventResettingOrderStatus($storeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'kount360/workflow/prevent_resetting_order_status',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}
