<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Observer;

use Magento\Sales\Model\Order\Payment;

class Condition implements ConditionInterface
{
    /**
     * @var bool
     */
    protected $default;

    /**
     * @var array
     */
    protected $conditions;

    /**
     * @param bool $default
     * @param array $conditions
     */
    public function __construct(
        $default = true,
        array $conditions = []
    ) {
        $this->default = (bool)$default;
        $this->conditions = $conditions;
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param int|null $storeId
     * @return bool
     */
    public function isCheckNotNeededForPayment(Payment $payment, $storeId = null)
    {
        $methodCode = $payment->getMethod();
        if (isset($this->conditions[$methodCode])) {
            $condition = $this->conditions[$methodCode];
            return $condition->isCheckNotNeededForPayment($payment, $storeId);
        }
        return $this->default;
    }
}
