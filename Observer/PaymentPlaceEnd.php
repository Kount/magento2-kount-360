<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Observer;

use Magento\Framework\Event\Observer;

class PaymentPlaceEnd implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \Kount\Kount360\Helper\Workflow $helperWorkflow,
        protected \Kount\Kount360\Model\Config\Workflow $configWorkflow,
        protected \Kount\Kount360\Model\WorkflowFactory $workflowFactory,
        protected \Kount\Kount360\Model\Observer\ConditionInterface $condition,
        protected \Kount\Kount360\Model\Logger $logger,
        array $allowedMethods = []
    ) {
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $this->logger->info('sales_order_payment_place_end Start');

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getData('payment');
        $order = $payment->getOrder();

        if (!$this->helperWorkflow->isProcessable($order)) {
            return;
        }

        if (!$this->condition->isCheckNotNeededForPayment($payment, $order->getStoreId())) {
            $this->logger->info("Skip for {$payment->getMethod()} payment method.");
            return;
        }

        $workflow = $this->workflowFactory->create($this->configWorkflow->getWorkflowMode($order->getStoreId()));
        $workflow->success($payment->getOrder());

        $this->logger->info('sales_order_payment_place_end Done ');
    }
}
