<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Update\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class Braintree implements PaymentInterface
{
    /**
     * @param \Magento\Framework\DataObject|\Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment)
    {
        $request->setAvst($this->getValue($payment, 'avsStreetAddressResponseCode'));
        $request->setAvsz($this->getValue($payment, 'avsPostalCodeResponseCode'));
        $request->setCvvr($this->getValue($payment, 'cvvResponseCode'));
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @param string $code
     * @return string
     */
    protected function getValue(OrderPaymentInterface $payment, $code)
    {
        $value = $payment->getAdditionalInformation($code);
        return in_array($value, ['M', 'N', 'X'], true) ? $value : 'X';
    }
}
