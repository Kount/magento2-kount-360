<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Observer\Condition;

use Magento\Sales\Model\Order\Payment;
use Kount\Kount360\Model\Observer\ConditionInterface;

class Positive implements ConditionInterface
{
    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param int|null $storeId
     * @return bool
     */
    public function isCheckNotNeededForPayment(Payment $payment, $storeId = null): bool
    {
        return true;
    }
}
