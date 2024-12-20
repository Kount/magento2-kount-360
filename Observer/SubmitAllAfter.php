<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Kount\Kount360\Model\Config\Source\DeclineAction;
use Magento\Sales\Model\Order;

class SubmitAllAfter implements \Magento\Framework\Event\ObserverInterface
{
    const REVIEW_STATUSES = [
        DeclineAction::ACTION_HOLD,
        DeclineAction::ACTION_CANCEL,
        DeclineAction::ACTION_REFUND,
        Order::STATE_CANCELED
    ];

    public function __construct(
        protected \Kount\Kount360\Helper\Workflow $helperWorkflow,
        protected \Kount\Kount360\Model\Config\Workflow $configWorkflow,
        protected \Kount\Kount360\Model\WorkflowFactory $workflowFactory,
        protected \Kount\Kount360\Model\Observer\ConditionInterface $condition,
        protected  \Kount\Kount360\Model\Logger $logger
    ) {
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $this->logger->info('checkout_submit_all_after Start');

        $event = $observer->getEvent();
        $orders = $event->getOrders() ?: [$event->getOrder()];

        foreach ($orders as $order) {
            $payment = $order->getPayment();
            if (!$this->helperWorkflow->isProcessable($order)) {
                continue;
            }
            if (!$this->condition->isCheckNotNeededForPayment($payment, $order->getStoreId())) {
                $this->logger->info("Skip for {$payment->getMethod()} payment method.");
                continue;
            }

            $workflow = $this->workflowFactory->create($this->configWorkflow->getWorkflowMode($order->getStoreId()));
            $workflow->success($order);
            $status = $order->getStatus();
            if (in_array($status, self::REVIEW_STATUSES)) {
                throw new LocalizedException(
                    __(
                        'Order declined. Please ensure your information is correct. If the problem persists,
                        please contact us for assistance.'
                    )
                );
            }
        }

        $this->logger->info('checkout_submit_all_after Done');
    }
}
