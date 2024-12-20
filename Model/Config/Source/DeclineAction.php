<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Config\Source;

class DeclineAction implements \Magento\Framework\Option\ArrayInterface
{
    const ACTION_HOLD = 'hold';
    const ACTION_CANCEL = 'cancel';
    const ACTION_REFUND = 'refund';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::ACTION_HOLD, 'label' => __('Hold Order / Decline Status')],
            ['value' => self::ACTION_CANCEL, 'label' => __('Cancel Order / Void Payment')],
            ['value' => self::ACTION_REFUND, 'label' => __('Refund / Credit Order')]
        ];
    }
}
