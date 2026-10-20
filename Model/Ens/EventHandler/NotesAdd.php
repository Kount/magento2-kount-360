<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ens\EventHandler;

use Kount\Kount360\Model\Ens\EventHandlerInterface;
use Kount\Kount360\Model\Lib\PayloadSanitizer;

class NotesAdd extends EventHandlerOrder implements EventHandlerInterface
{
    const EVENT_NAME = 'Order.NotesAdd';

    /**
     * @param \Kount\Kount360\Model\Logger $logger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Kount\Kount360\Model\Lib\PayloadSanitizer|null $payloadSanitizer
     */
    public function __construct(
        protected \Kount\Kount360\Model\Logger $logger,
        protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        protected \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        ?PayloadSanitizer $payloadSanitizer = null
    ) {
        parent::__construct($orderRepository, $searchCriteriaBuilder, $payloadSanitizer);
    }

    /**
     * @param \Magento\Framework\Simplexml\Element $event
     * @return void
     */
    public function process($event): void
    {
        $orderId = $this->getEventField($event, 'merchantOrderId');
        $transactionId = $this->getEventField($event, 'kountOrderId', false);
        $oldValue = $this->getEventFieldFlattened($event, 'oldValue', false);
        $newValue = $this->getEventFieldFlattened($event, 'newValue', false);
        $reasonCode = $this->getEventField($event, 'reasonCode', false);

        $this->logger->info('ENS Event Details');
        $this->logger->info('Name: ' . self::EVENT_NAME);
        $this->logger->info('order_number: ' . $orderId);
        $this->logger->info('transaction_id: ' . $transactionId);
        $this->logger->info('old_value: ' . $oldValue);
        $this->logger->info('new_value: ' . $newValue);

        $commentParts = [];
        if ($reasonCode !== '') {
            $commentParts[] = 'Reason Code: ' . $reasonCode;
        }
        $commentParts[] = 'Comment: ' . $newValue;
        $newComment = implode(' | ', $commentParts);

        $order = $this->loadOrder($orderId);
        $order->addCommentToStatusHistory($newComment);
        $this->orderRepository->save($order);
    }
}
