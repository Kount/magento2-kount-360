<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Inquiry\Builder;

class VersionInfo
{
    const SDK_VALUE = 'CUST';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Kount\Kount360\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $moduleResource;

    /**
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Kount\Kount360\Helper\Data $helperData
     * @param \Magento\Framework\Module\ResourceInterface $moduleResource
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Kount\Kount360\Helper\Data $helperData,
        \Magento\Framework\Module\ResourceInterface $moduleResource
    ) {
        $this->productMetadata = $productMetadata;
        $this->helperData = $helperData;
        $this->moduleResource = $moduleResource;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     */
    public function process(\Magento\Framework\DataObject $request)
    {
        $request->setData(
            'PLATFORM',
            $this->productMetadata->getEdition() . ':' . $this->productMetadata->getVersion()
        );
        $request->setData('EXT', $this->helperData->getModuleVersion());
        $request->setParm('SDK', self::SDK_VALUE);
        $request->setParm(
            'SDK_VERSION',
            sprintf('TPA-Magento-%s', $this->moduleResource->getDataVersion($this->helperData->getModuleName()))
        );
    }
}
