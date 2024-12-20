<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Base\Builder;

use Magento\Sales\Api\Data\OrderPaymentInterface;

interface PaymentInterface
{
    /**
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function process(\Magento\Framework\DataObject $request, OrderPaymentInterface $payment);
}
