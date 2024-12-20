<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Order\Action;

use Kount\Kount360\Model\Order\ActionInterface;

class Restore implements ActionInterface
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
        $orderState = $order->getHoldBeforeState();
        $orderStatus = $order->getHoldBeforeStatus();
        if (!$orderState || !$orderStatus) {
            $this->logger->info('Restore order status/state by ENS Kount request - incomplete data, skipping');
            return;
        }

        $this->logger->info('Restore order status/state by ENS Kount request.');

        $order->setState($orderState);
        $order->addStatusToHistory($orderStatus, __('Order status updated from Kount.'), false);

        $order->setHoldBeforeState(null);
        $order->setHoldBeforeStatus(null);
    }
}
