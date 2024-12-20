<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ens\EventHandler;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Parent class for WORKFLOW events. The class contains common methods.
 */
class EventHandlerOrder
{
    const ORDER_INCREMENT_ID_FIELD = 'increment_id';

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        protected \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @param $orderId
     * @return \Magento\Framework\DataObject
     */
    public function loadOrder($orderId)
    {
        if (empty($orderId)) {
            throw new \InvalidArgumentException('Invalid Order number.');
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(self::ORDER_INCREMENT_ID_FIELD, $orderId)
            ->create();
        $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();

        if (!$order->getId()) {
            throw new \InvalidArgumentException("Unable to locate order for: {$orderId}");
        }
        return $order;
    }
}
