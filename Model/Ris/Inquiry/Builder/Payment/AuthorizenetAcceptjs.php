<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Inquiry\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class AuthorizenetAcceptjs extends \Kount\Kount360\Model\Ris\Update\Builder\Payment\AuthorizenetAcceptjs
    implements PaymentInterface
{
    /**
     * @param \Magento\Framework\DataObject|\Magento\Framework\DataObject_Inquiry $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment)
    {
        $request->setNoPayment();
        $request->setData('payment_type', 'authorizenet_acceptjs');

        parent::process($request, $payment);
    }
}
