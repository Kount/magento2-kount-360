<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Update\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class PaypalPayflowLink implements PaymentInterface
{
    /**
     * @param \Magento\Framework\DataObject|\Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment)
    {
        $request->setAvst($this->getAvst($payment));
        $request->setAvsz($this->getAvsz($payment));
        $request->setCvvr($this->getCvvr($payment));
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return string
     */
    protected function getAvst(OrderPaymentInterface $payment)
    {
        $avsCode = $payment->getAdditionalInformation('paypal_avs_code');
        switch ($avsCode) {
            case 'A': /* Matched Address only (no ZIP) */
            case 'B': /* Matched Address only (no ZIP) International */
            case 'X': /* Exact Match */
            case 'D': /* Exact Match. Address and Postal Code. International */
            case 'F': /* Exact Match. Address and Postal Code. UK-specific */
            case 'Y': /* Yes. Matched Address and five-didgit ZIP */
            case '0': /* All the address information matched */
                $result = 'M';
                break;
            case 'N': /* No Details matched */
            case 'C': /* No Details matched. International */
            case 'Z': /* Matched five-digit ZIP only (no Address) */
            case 'P': /* Matched Postal Code only (no Address) */
            case 'W': /* Matched whole nine-didgit ZIP (no Address) */
            case '1': /* None of the address information matched */
                $result = 'N';
                break;
            case 'E': /* N/A. Not allowed for MOTO (Internet/Phone) transactions */
            case 'G': /* N/A. Global Unavailable */
            case 'I': /* N/A. International Unavailable */
            case 'R': /* N/A. Retry */
            case 'S': /* N/A. Service not Supported */
            case 'U': /* N/A. Unavailable */
            case '2': /* Part of the address information matched */
            case '3': /* N/A. The merchant did not provide AVS information */
            case '4': /* N/A. Address not checked, or acquirer had no response. Service not available */
            default:
                $result = 'X';
                break;
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return string
     */
    protected function getAvsz(OrderPaymentInterface $payment)
    {
        $avsCode = $payment->getAdditionalInformation('paypal_avs_code');
        switch ($avsCode) {
            case 'X': /* Exact Match. */
            case 'D': /* Exact Match. Address and Postal Code. International */
            case 'F': /* Exact Match. Address and Postal Code. UK-specific */
            case 'Z': /* Matched five-digit ZIP only (no Address) */
            case 'P': /* Matched Postal Code only (no Address) */
            case 'W': /* Matched whole nine-didgit ZIP (no Address) */
            case 'Y': /* Yes. Matched Address and five-didgit ZIP */
                $result = 'M';
                break;
            case 'A': /* Matched Address only (no ZIP) */
            case 'B': /* Matched Address only (no ZIP) International */
            case 'N': /* No Details matched */
            case 'C': /* No Details matched. International */
                $result = 'N';
                break;
            case 'E': /* N/A. Not allowed for MOTO (Internet/Phone) transactions */
            case 'G': /* N/A. Global Unavailable */
            case 'I': /* N/A. International Unavailable */
            case 'R': /* N/A. Retry */
            case 'S': /* N/A. Service not Supported */
            case 'U': /* N/A. Unavailable */
            case '0': /* All the address information matched */
            case '1': /* None of the address information matched */
            case '2': /* Part of the address information matched */
            case '3': /* N/A. The merchant did not provide AVS information */
            case '4': /* N/A. Address not checked, or acquirer had no response. Service not available */
            default:
                $result = 'X';
                break;
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return string
     */
    protected function getCvvr(OrderPaymentInterface $payment)
    {
        $avsCode = $payment->getAdditionalInformation('paypal_cvv_2_match');
        switch ($avsCode) {
            case 'M': /* Matched (CVV2CSC) */
            case 'Y': /* Matched (CVV2CSC) */
            case '0': /* Matched (CVV2) */
                $result = 'M';
                break;
            case 'N': /* No match */
            case '1': /* No match */
                $result = 'N';
                break;
            case 'P': /* N/A. Not processed */
            case 'S': /* N/A. Service not supported */
            case 'U': /* N/A. Service not available */
            case 'X': /* N/A. No response */
            case '2': /* N/A. The merchant has not implemented CVV2 code handling */
            case '3': /* N/A. Merchant has indicated that CVV2 is not present on card */
            case '4': /* N/A. Service not available */
            default:
                $result = 'X';
                break;
        }
        return $result;
    }
}
