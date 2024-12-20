<?php

namespace Kount\Kount360\Model\Config;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Authorization
{
    const ACCESS_TOKEN_CONFIG_PATH_TEST = 'kount360/account/access_token_test';
    const ACCESS_TOKEN_CONFIG_PATH_PROD = 'kount360/account/access_token_production';

    public function __construct(
        private \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        private \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        private \Kount\Kount360\Model\Config\Account $configAccount
    ) {
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        if ($this->configAccount->isTestMode()) {
            $this->scopeConfig->getValue(self::ACCESS_TOKEN_CONFIG_PATH_TEST, ScopeInterface::SCOPE_WEBSITE);
        }
        return $this->scopeConfig->getValue(self::ACCESS_TOKEN_CONFIG_PATH_PROD, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param $accessToken
     * @return void
     */
    public function setAccessToken($accessToken): void
    {
        if ($accessToken) {
            $this->setOauthData($accessToken);
        }
        $this->cacheTypeList->cleanType('config');
    }

    /**
     * @param $data
     * @return void
     */
    private function setOauthData($data): void
    {
        $path = $this->configAccount->isTestMode() ? self::ACCESS_TOKEN_CONFIG_PATH_TEST : self::ACCESS_TOKEN_CONFIG_PATH_PROD;
        $this->configWriter->save(
            $path,
            $data,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }
}
