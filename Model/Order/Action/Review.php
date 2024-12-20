<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Order\Action;

use Magento\Sales\Model\Order;
use Kount\Kount360\Model\Order\ActionInterface;
use Kount\Kount360\Model\Order\Ris as OrderRis;

class Review implements ActionInterface
{
    /**
     * @var \Kount\Kount360\Model\Logger
     */
    protected $logger;

    /**
     * @param \Kount\Kount360\Model\Logger $logger
     */
    public function __construct(
        \Kount\Kount360\Model\Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param $order
     * @param $isInPaymentWorkflow
     * @return void
     */
    public function process($order, $isInPaymentWorkflow = false)
    {
        $orderState = $order->getState();
        $orderStatus = $order->getStatus();
        if ($orderState === Order::STATE_HOLDED && $orderStatus === OrderRis::STATUS_KOUNT_REVIEW) {
            $this->logger->info('Setting order to Kount Review status/state - already set, skipping');
            return;
        }

        $this->logger->info('Setting order to Kount Review status/state');

        $order->setHoldBeforeState($orderState);
        $order->setHoldBeforeStatus($orderStatus);

        $order->setState(Order::STATE_HOLDED);
        $order->addStatusToHistory(OrderRis::STATUS_KOUNT_REVIEW, __('Order on review from Kount.'), false);
    }
}
