<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Update\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class NoPayment implements PaymentInterface
{
    /**
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return $this|void
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment)
    {
        return $this;
    }
}
