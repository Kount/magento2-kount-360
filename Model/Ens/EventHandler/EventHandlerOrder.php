<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ens\EventHandler;

use Kount\Kount360\Model\Lib\PayloadSanitizer;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Parent class for WORKFLOW events. The class contains common methods.
 */
class EventHandlerOrder
{
    const ORDER_INCREMENT_ID_FIELD = 'increment_id';

    const MAX_FIELD_LENGTH = 255;

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Kount\Kount360\Model\Lib\PayloadSanitizer $payloadSanitizer
     */
    public function __construct(
        protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        protected \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        protected PayloadSanitizer $payloadSanitizer
    ) {
    }

    /**
     * @param mixed $event
     * @param string $key
     * @param bool $required
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getEventField($event, string $key, bool $required = true): string
    {
        if (!is_array($event) || !array_key_exists($key, $event)) {
            if ($required) {
                throw new \InvalidArgumentException("Missing ENS event field: {$key}");
            }
            return '';
        }

        $value = $event[$key];

        if ($value === null || $value === false) {
            if ($required) {
                throw new \InvalidArgumentException("Empty ENS event field: {$key}");
            }
            return '';
        }

        if (!is_scalar($value)) {
            throw new \InvalidArgumentException("ENS event field {$key} must be scalar.");
        }

        $value = trim((string)$value);

        if ($value === '' && $required) {
            throw new \InvalidArgumentException("Empty ENS event field: {$key}");
        }

        $value = $this->payloadSanitizer->neutralize($value);

        if (strlen($value) > self::MAX_FIELD_LENGTH) {
            $value = substr($value, 0, self::MAX_FIELD_LENGTH);
        }

        return $value;
    }

    /**
     * @param mixed $event
     * @param string $key
     * @param bool $required
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getEventFieldFlattened($event, string $key, bool $required = true): string
    {
        if (is_array($event) && isset($event[$key]) && is_array($event[$key])) {
            $list = array_filter($event[$key], 'is_scalar');
            if (empty($list)) {
                if ($required) {
                    throw new \InvalidArgumentException("ENS event field {$key} contains no scalar value.");
                }
                return '';
            }
            $event = [$key => reset($list)];
        }

        return $this->getEventField($event, $key, $required);
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
