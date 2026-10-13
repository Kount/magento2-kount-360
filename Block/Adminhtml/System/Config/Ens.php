<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */

namespace Kount\Kount360\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class Ens extends Field
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $frontendUrlBuilder
     * @param array $data
     */
    public function __construct(
        protected \Magento\Backend\Block\Template\Context $context,
        protected StoreManagerInterface $storeManager,
        protected UrlInterface $frontendUrlBuilder,
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
        $html = '<div id="' . $this->escapeHtmlAttr($element->getHtmlId()) . '">';
        $stores = $this->_storeManager->getStores();

        if (empty($stores)) {
            return '<span>' . $this->escapeUrl($this->getEnsUrl()) . '</span>';
        }

        $html .= '<ul style="list-style-type: none; padding-left: 0;">';
        foreach ($stores as $store) {
            $storeName = $store->getName();
            $storeCode = $store->getCode();
            $ensUrl = $this->getEnsUrlForStore($store);

            $html .= '<li style="margin-bottom: 10px;">';
            $html .= '<strong>' . $this->escapeHtml($storeName)
                . ' (' . $this->escapeHtml($storeCode) . '):</strong><br/>';
            $html .= '<span>' . $this->escapeUrl($ensUrl) . '</span>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Get ENS URL for a specific store
     *
     * @param \Magento\Store\Model\Store $store
     * @return string
     */
    protected function getEnsUrlForStore(\Magento\Store\Model\Store $store): string
    {
        $url = $store->getBaseUrl() . 'kount360/ens/';
        return $url;
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

        $url = $this->frontendUrlBuilder->getUrl('kount360/ens', ['_forced_secure' => true, '_nosid' => true]);

        // Ensure the URL includes the /html prefix if it's not already there
        if (strpos($url, '/html/') === false) {
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '';

            // If path doesn't start with /html, add it
            if (strpos($path, '/html') !== 0) {
                $newPath = '/html' . $path;
                $url = str_replace($path, $newPath, $url);
            }
        }

        return $url;
    }
}
