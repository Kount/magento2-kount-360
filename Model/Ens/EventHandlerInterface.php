<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ens;

interface EventHandlerInterface
{
    /**
     * @param \Magento\Framework\Simplexml\Element $event
     */
    public function process($event);
}
