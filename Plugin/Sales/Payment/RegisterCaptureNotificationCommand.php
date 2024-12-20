<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Plugin\Sales\Payment;

use Magento\Sales\Model\Order;
use Kount\Kount360\Model\Order\Ris as OrderRis;

class RegisterCaptureNotificationCommand
{
    /**
     * @var \Kount\Kount360\Model\Config\Workflow
     */
    private $configWorkflow;

    /**
     * @param \Kount\Kount360\Model\Config\Workflow $configWorkflow
     */
    public function __construct(
        \Kount\Kount360\Model\Config\Workflow $configWorkflow
    ) {
        $this->configWorkflow = $configWorkflow;
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment\State\RegisterCaptureNotificationCommand $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float|int|string $amount
     * @param \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order
     * @return \Magento\Framework\Phrase
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Magento\Sales\Model\Order\Payment\State\RegisterCaptureNotificationCommand $subject,
        \Closure $proceed,
        \Magento\Sales\Api\Data\OrderPaymentInterface $payment,
        $amount,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $state = Order::STATE_PROCESSING;
        $message = 'Registered notification about captured amount of %1.';

        if ($payment->getIsTransactionPending()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $message = 'An amount of %1 will be captured after being approved at the payment gateway.';
        }

        if ($payment->getIsFraudDetected()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $message = 'Order is suspended as its capture amount %1 is suspected to be fraudulent.';
        }

        if ($state == Order::STATE_PROCESSING
            || ($this->isHoldByKount($order) && $this->configWorkflow->isPreventResettingOrderStatus(
                    $order->getStoreId()
                ))
        ) {
            return __($message, $order->getBaseCurrency()->formatTxt($amount));
        }

        return $proceed($payment, $amount, $order);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     */
    private function isHoldByKount(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return $order->getState() == Order::STATE_HOLDED
            && in_array($order->getStatus(), [OrderRis::STATUS_KOUNT_REVIEW, OrderRis::STATUS_KOUNT_DECLINE]);
    }
}
