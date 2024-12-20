<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Api\Data;

interface OrderQueueMessageInterface
{
    /**
     * Get the order ID.
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Set the order ID.
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);
}
