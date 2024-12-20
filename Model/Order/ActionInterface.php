<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Order;

interface ActionInterface
{
    /**
     * @param $order
     * @param bool $isInPaymentWorkflow
     * @return mixed
     */
    public function process($order, $isInPaymentWorkflow = false);
}
