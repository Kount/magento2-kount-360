<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris;

class UpdateFactory
{
    /**
     * @param \Kount\Kount360\Model\Lib\Settings $libSettings
     */
    public function __construct(
        protected \Kount\Kount360\Model\Lib\Settings $libSettings
    ) {
    }

    /**
     * @param string|null $websiteCode
     * @return \Magento\Framework\DataObject
     */
    public function create($websiteCode = null)
    {
        // Create and populate DataObject
        $inquiryObject = new \Magento\Framework\DataObject();
        return $inquiryObject;
    }
}
