<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Helper;

use Magento\Framework\App\Area;

class Workflow
{
    public function __construct(
        protected \Kount\Kount360\Model\Config\Account $configAccount,
        protected \Kount\Kount360\Model\Config\PaymentMethods $configPaymentMethods,
        protected \Kount\Kount360\Model\Config\Admin $configAdmin,
        protected \Magento\Framework\App\State $appState,
        protected \Kount\Kount360\Model\Logger $logger
    ) {
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isProcessable($order): bool
    {
        $paymentMethodCode = $order->getPayment()->getMethod();
        $websiteId = $order->getStore()->getWebsiteId();

        if (!$this->configAccount->isAvailable($websiteId)) {
            $this->logger->info('Kount extension is disabled or not configured.');
            return false;
        }

        if ($paymentMethodCode && in_array(
                $paymentMethodCode,
                $this->configPaymentMethods->getDisableMethods($websiteId),
                true
            )) {
            $this->logger->info('Kount disabled for payment method: ' . $paymentMethodCode);
            return false;
        }

        if ($this->isBackendArea($order) && !$this->configAdmin->isEnabled($websiteId)) {
            $this->logger->info('Kount disabled for Admin panel order.');
            return false;
        }

        return true;
    }

    /**
     * @param $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isBackendArea($order): bool
    {
        return $this->appState->getAreaCode() === Area::AREA_ADMINHTML || empty($order->getRemoteIp());
    }
}
