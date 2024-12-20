<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Update;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;

class Builder
{
    public function __construct(
        private \Kount\Kount360\Model\Ris\UpdateFactory $updateFactory,
        private \Kount\Kount360\Model\Ris\Inquiry\Builder\Order $orderBuilder,
    ) {
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $risTransactionId
     * @return \Magento\Framework\DataObject
     */
    public function build(Order $order, $risTransactionId): DataObject
    {
        $updateRequest = $this->updateFactory->create($order->getStore()->getWebsiteId());
        $this->orderBuilder->processUpdate($updateRequest, $risTransactionId, $order);
        return $updateRequest;
    }
}
