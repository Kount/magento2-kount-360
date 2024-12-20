<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\ResourceModel;

class Ris extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'kount_360';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('kount_360', 'ris_id');
    }
}
