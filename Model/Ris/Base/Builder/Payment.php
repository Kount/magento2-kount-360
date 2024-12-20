<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Base\Builder;

use Magento\Sales\Api\Data\OrderPaymentInterface;

class Payment implements \Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface
{
    /**
     * @var array
     */
    protected $payments;

    /**
     * @var string
     */
    protected $defaultPayment;

    /**
     * @param string $defaultPayment
     * @param array $payments
     */
    public function __construct(
        $defaultPayment,
        array $payments
    ) {
        $this->defaultPayment = $defaultPayment;
        $this->payments = $payments;
    }

    /**
     * @param string $paymentCode
     * @return string
     */
    protected function getClassByCode($paymentCode)
    {
        return $this->payments[$paymentCode] ?? $this->defaultPayment;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment)
    {
        $paymentBuilderClass = $this->getClassByCode($payment->getMethod());
        //$paymentBuilder = $this->paymentBuilderFactory->create($paymentBuilderClass);
        $paymentBuilderClass->process($request, $payment);
    }
}
