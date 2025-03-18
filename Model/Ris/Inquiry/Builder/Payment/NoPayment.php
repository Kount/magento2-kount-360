<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Inquiry\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class NoPayment implements PaymentInterface
{
    /**
     * @param \Magento\Framework\DataObject|\Magento\Framework\DataObject_Inquiry $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment)
    {
        $request->setNoPayment();
        $request->setAvst('X');
        $request->setAvsz('X');
    }
}
