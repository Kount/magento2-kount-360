<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class Account
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param string|null $websiteCode
     * @return bool
     */
    public function isEnabled($websiteCode = null)
    {
        return $this->scopeConfig->isSetFlag('kount360/account/enabled', ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }

    public function getAuthUrl($websiteCode = null)
    {
        return $this->isTestMode($websiteCode) ? $this->scopeConfig->getValue(
            'kount360/account/auth_url_test',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
        )
            : $this->scopeConfig->getValue(
                'kount360/account/auth_url_production',
                ScopeInterface::SCOPE_WEBSITE,
                $websiteCode
            );
    }

    /**
     * @param string|null $websiteCode
     * @return bool
     */
    public function isAvailable($websiteCode = null)
    {
        return $this->isEnabled($websiteCode)
            && !empty($this->getClientId($websiteCode))
            && !empty($this->getApiKey($websiteCode));
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->scopeConfig->getValue(
            Store::XML_PATH_PRICE_SCOPE,
            ScopeInterface::SCOPE_STORE
        ) == Store::PRICE_SCOPE_GLOBAL
            ? $this->scopeConfig->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE)
            : $this->scopeConfig->getValue('kount360/account/currency');
    }

    /**
     * @param string|null $websiteCode
     * @return string
     */
    public function getClientId($websiteCode = null)
    {
        return $this->isTestMode($websiteCode) ? $this->scopeConfig->getValue('kount360/account/client_id_test', ScopeInterface::SCOPE_WEBSITE, $websiteCode)
        : $this->scopeConfig->getValue('kount360/account/client_id_production', ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }

    /**
     * @param string|null $websiteCode
     * @return string
     */
    public function getWebsite($websiteCode = null)
    {
        return $this->scopeConfig->getValue('kount360/account/website', ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }

    /**
     * @param string|null $websiteCode
     * @return string
     */
    public function getApiKey($websiteCode = null)
    {
        $env = $this->isTestMode($websiteCode) ? 'test' : 'production';
        return $this->scopeConfig->getValue(
            "kount360/account/api_key_{$env}",
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
        );
    }

    public function getKountOrderEndpoint($websiteCode = null)
    {
        $env = $this->isTestMode($websiteCode) ? 'test' : 'production';
        return $this->scopeConfig->getValue(
            "kount360/account/orders_endpoint_{$env}",
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
        );
    }

    /**
     * @return string
     */
    public function getConfigKey($websiteCode = null)
    {
        return $this->scopeConfig->getValue('kount360/account/config_key', ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }

    /**
     * @param string|null $websiteCode
     * @return bool
     */
    public function isTestMode($websiteCode = null)
    {
        return $this->scopeConfig->isSetFlag('kount360/account/test', ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }

    /**
     * @param string|null $websiteCode
     * @return string
     */
    public function getAwcUrl($websiteCode = null)
    {
        return $this->scopeConfig->getValue('kount360/account/awc_url' . $this->getModeSuffix($websiteCode));
    }

    /**
     * @param string|null $websiteCode
     * @return string
     */
    public function getRisUrl($websiteCode = null)
    {
        return $this->scopeConfig->getValue('kount360/account/ris_url' . $this->getModeSuffix($websiteCode));
    }

    /**
     * @param string|null $websiteCode
     * @return string
     */
    public function getDataCollectorUrl($websiteCode = null)
    {
        return $this->scopeConfig->getValue('kount360/account/data_collector_url' . $this->getModeSuffix($websiteCode));
    }

    /**
     * @return int
     */
    public function getDataCollectorWidth()
    {
        return (int)$this->scopeConfig->isSetFlag('kount360/account/data_collector_width');
    }

    /**
     * @return int
     */
    public function getDataCollectorHeight()
    {
        return (int)$this->scopeConfig->isSetFlag('kount360/account/data_collector_height');
    }

    /**
     * @param string|null $websiteCode
     * @return string
     */
    protected function getModeSuffix($websiteCode = null)
    {
        return $this->isTestMode($websiteCode) ? '_test_mode' : '';
    }

    /**
     * @param $websiteCode
     * @return array
     */
    public function getStoreInformation($websiteCode = null): array
    {
        $storeInformation = [];
        $storeInformation['name'] = $this->scopeConfig->getValue(
            'general/store_information/street_line1',
            ScopeInterface::SCOPE_STORE,
            $websiteCode
        );
        $storeInformation['address'] = [];
        $storeInformation['address']['line1'] = $this->scopeConfig->getValue(
            'general/store_information/street_line1',
            ScopeInterface::SCOPE_STORE,
            $websiteCode
        );
        $storeInformation['address']['line2'] = $this->scopeConfig->getValue(
            'general/store_information/street_line2',
            ScopeInterface::SCOPE_STORE,
            $websiteCode
        );
        $storeInformation['address']['city'] = $this->scopeConfig->getValue(
            'general/store_information/city',
            ScopeInterface::SCOPE_STORE,
            $websiteCode
        );
        $storeInformation['address']['region'] = $this->scopeConfig->getValue(
            'general/store_information/region_id',
            ScopeInterface::SCOPE_STORE,
            $websiteCode
        );
        $storeInformation['address']['countryCode'] = $this->scopeConfig->getValue(
            'general/store_information/country_id',
            ScopeInterface::SCOPE_STORE,
            $websiteCode
        );
        $storeInformation['address']['postalCode'] = $this->scopeConfig->getValue(
            'general/store_information/postcode',
            ScopeInterface::SCOPE_STORE,
            $websiteCode
        );
        return $storeInformation;
    }
}
