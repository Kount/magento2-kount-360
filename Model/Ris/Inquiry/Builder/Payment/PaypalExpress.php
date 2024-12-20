<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Inquiry\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Paypal\Model\Express\Checkout as PaypalExpressCheckout;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class PaypalExpress implements PaymentInterface
{
    /**
     * @param \Magento\Framework\DataObject|\Magento\Framework\DataObject_Inquiry $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @throws LocalizedException
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment)
    {
        $payPalId = $payment->getAdditionalInformation(PaypalExpressCheckout::PAYMENT_INFO_TRANSPORT_PAYER_ID);
        if (empty($payPalId)) {
            throw  new LocalizedException(__('Invalid Payer Id for PayPal payment.'));
        }
        $request->setPayPalPayment($payPalId);
    }
}
