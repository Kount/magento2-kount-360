<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Update\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class PaypalPayflowPro implements PaymentInterface
{
    /**
     * @param \Magento\Framework\DataObject|\Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment)
    {
        $request->setAvst($this->getValue($payment, 'avsaddr'));
        $request->setAvsz($this->getValue($payment, 'avszip'));
        $request->setCvvr($this->getValue($payment, 'cvv2match'));
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @param string $code
     * @return string
     */
    protected function getValue(OrderPaymentInterface $payment, $code)
    {
        $value = $payment->getAdditionalInformation($code);
        switch ($value) {
            case 'Y':
                $result = 'M';
                break;
            case 'N':
                $result = 'N';
                break;
            case 'X':
            default:
                $result = 'X';
                break;
        }
        return $result;
    }
}
