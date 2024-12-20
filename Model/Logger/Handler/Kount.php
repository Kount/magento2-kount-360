<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Logger\Handler;

use Monolog\Logger;

class Kount extends \Magento\Framework\Logger\Handler\System
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/kount360.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
