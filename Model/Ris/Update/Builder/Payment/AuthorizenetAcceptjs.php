<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Update\Builder\Payment;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;

class AuthorizenetAcceptjs implements PaymentInterface
{
    /**
     * @param \Magento\Framework\DataObject|\Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment)
    {
        $avsResultCode = $payment->getAdditionalInformation('avsResultCode');
        if (!empty($avsResultCode)) {
            $request->setAvst($this->getAvst((string)$avsResultCode));
            $request->setAvsz($this->getAvsz((string)$avsResultCode));
        }

        $cvvResultCode = $payment->getAdditionalInformation('cvvResultCode');
        if (!empty($cvvResultCode)) {
            $request->setCvvr($this->getCvvr((string)$cvvResultCode));
        }
    }

    /**
     * @param string $avsResultCode
     * @return string
     */
    private function getAvst(string $avsResultCode): string
    {
        switch ($avsResultCode) {
            case 'A': /* The street address matched, but the postal code did not. */
            case 'X': /* Both the street address and the US ZIP+4 code matched. */
            case 'Y': /* The street address and postal code matched. */
                $result = 'M';
                break;
            case 'N': /* Neither the street address nor postal code matched. */
            case 'W': /* The US ZIP+4 code matches, but the street address does not. */
            case 'Z': /* The postal code matched, but the street address did not. */
                $result = 'N';
                break;
            case 'B': /* No address information was provided. */
            case 'E': /* The AVS check returned an error. */
            case 'G': /* The card was issued by a bank outside the U.S. and does not support AVS. */
            case 'P': /* AVS is not applicable for this transaction. */
            case 'R': /* Retry — AVS was unavailable or timed out. */
            case 'S': /* AVS is not supported by card issuer. */
            case 'U': /* Address information is unavailable. */
            default:
                $result = 'X';
                break;
        }
        return $result;
    }

    /**
     * @param string $avsResultCode
     * @return string
     */
    private function getAvsz(string $avsResultCode): string
    {
        switch ($avsResultCode) {
            case 'W': /* The US ZIP+4 code matches, but the street address does not. */
            case 'X': /* Both the street address and the US ZIP+4 code matched. */
            case 'Y': /* The street address and postal code matched. */
            case 'Z': /* The postal code matched, but the street address did not. */
                $result = 'M';
                break;
            case 'A': /* The street address matched, but the postal code did not. */
            case 'N': /* Neither the street address nor postal code matched. */
                $result = 'N';
                break;
            case 'B': /* No address information was provided. */
            case 'E': /* The AVS check returned an error. */
            case 'G': /* The card was issued by a bank outside the U.S. and does not support AVS. */
            case 'P': /* AVS is not applicable for this transaction. */
            case 'R': /* Retry — AVS was unavailable or timed out. */
            case 'S': /* AVS is not supported by card issuer. */
            case 'U': /* Address information is unavailable. */
            default:
                $result = 'X';
                break;
        }
        return $result;
    }

    /**
     * @param string $cvvResultCode
     * @return string
     */
    private function getCvvr(string $cvvResultCode): string
    {
        switch ($cvvResultCode) {
            case 'M': /* CVV matched. */
                $result = 'M';
                break;
            case 'N': /* CVV did not match. */
                $result = 'N';
                break;
            case 'P': /* CVV was not processed. */
            case 'S': /* CVV should have been present but was not indicated. */
            case 'U': /* The issuer was unable to process the CVV check. */
            default:
                $result = 'X';
                break;
        }
        return $result;
    }
}
