<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ens\EventHandler;

use Kount\Kount360\Model\Ens\EventHandlerInterface;

class NotesAdd extends EventHandlerOrder implements EventHandlerInterface
{
    const EVENT_NAME = 'Order.NotesAdd';

    /**
     * @param \Kount\Kount360\Model\Logger $logger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        protected \Kount\Kount360\Model\Logger $logger,
        protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        protected \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($orderRepository, $searchCriteriaBuilder);
    }

    /**
     * @param \Magento\Framework\Simplexml\Element $event
     * @return void
     */
    public function process($event): void
    {
        $orderId = $event['merchantOrderId'];
        $transactionId  = $event['kountOrderId'];
        $oldValue = $event['oldValue'];
        $newValue = $event['newValue'];
        $this->logger->info('ENS Event Details');
        $this->logger->info('Name: ' . self::EVENT_NAME);
        $this->logger->info('order_number: ' . $orderId);
        $this->logger->info('transaction_id: ' . $transactionId);
        $this->logger->info('old_value: ' . $oldValue);
        $this->logger->info('new_value: ' . $newValue[0]);

        // Create a new comment for the order
        $newComment = "Reason Code: " . $newValue['@']['reason_code'] . "<br>"
                      . "Comment: " . $newValue[0];

        // Add the comment to the order
        $order = $this->loadOrder($orderId);
        $order->addCommentToStatusHistory($newComment);
        $this->orderRepository->save($order);
    }
}
