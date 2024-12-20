<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Lib;

class LoggerFactory
{
    /**
     * @var string
     */
    protected $websiteId;

    /**
     * @var array
     */
    protected $loggerTypes = [
        'SIMPLE' => \Kount\Kount360\Model\Lib\Logger::class
    ];

    public function __construct(
        protected \Kount\Kount360\Model\Lib\Logger $logger
    ) {
    }

    /**
     * @param string $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->websiteId = $websiteId;
        return $this;
    }

    public function create()
    {
        return $this->logger;
    }
}
