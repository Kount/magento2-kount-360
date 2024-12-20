<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\ResourceModel\Ris;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Kount\Kount360\Model\Ris::class, \Kount\Kount360\Model\ResourceModel\Ris::class);
    }
}
