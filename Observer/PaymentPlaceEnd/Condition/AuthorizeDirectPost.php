<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Observer\PaymentPlaceEnd\Condition;

use Magento\Sales\Model\Order\Payment;
use Kount\Kount360\Model\Observer\ConditionInterface;

class AuthorizeDirectPost implements ConditionInterface
{
    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param int|null $storeId
     * @return bool
     */
    public function isCheckNotNeededForPayment(Payment $payment, $storeId = null)
    {
        return (bool)$payment->getTransactionId();
    }
}
