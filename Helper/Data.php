<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\PackageInfo $packageInfo
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        protected \Magento\Framework\Module\PackageInfo $packageInfo
    ) {
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getModuleVersion(): string
    {
        return $this->packageInfo->getVersion($this->getModuleName());
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->_getModuleName();
    }
}
