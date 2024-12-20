<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Config\Source;

class WorkflowMode implements \Magento\Framework\Option\ArrayInterface
{
    const MODE_PRE_AUTH = 'pre_auth';
    const MODE_POST_AUTH = 'post_auth';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::MODE_PRE_AUTH, 'label' => __('Pre-Authorization Payment Review')],
            ['value' => self::MODE_POST_AUTH, 'label' => __('Post-Authorization Payment Review')]
        ];
    }
}
