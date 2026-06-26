<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Helper;

class Data
{
    /**
     * @param \Magento\Framework\Module\PackageInfo $packageInfo
     */
    public function __construct(
        protected \Magento\Framework\Module\PackageInfo $packageInfo
    ) {
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
        return 'Kount_Kount360';
    }
}
