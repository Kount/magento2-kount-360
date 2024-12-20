<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Ens url for backend configuration
 */
class Ens extends Field
{
    public function __construct(
        protected \Magento\Backend\Block\Template\Context $context,
        protected \Magento\Framework\Url $frontendUrlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Override method to output our custom HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return '<span id="' . $element->getHtmlId() . '">' . $this->getEnsUrl() . '</span>';
    }

    /**
     * @return string
     */
    protected function getEnsUrl(): string
    {
        $websiteId = $this->getRequest()->getParam('website');
        if (!empty($websiteId)) {
            $store = $this->_storeManager->getWebsite($websiteId)->getDefaultStore();
            $this->frontendUrlBuilder->setScope($store);
        }
        return $this->frontendUrlBuilder->getUrl('kount360/ens', ['_forced_secure' => true, '_nosid' => true]);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderInheritCheckbox(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        return '<td/>';
    }
}
