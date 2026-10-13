<?php

namespace Kount\Kount360\Model\Config;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Authorization
{
    const ACCESS_TOKEN_CONFIG_PATH_TEST = 'kount360/account/access_token_test';
    const ACCESS_TOKEN_CONFIG_PATH_PROD = 'kount360/account/access_token_production';

    const MIN_TOKEN_LENGTH = 16;

    const MAX_TOKEN_LENGTH = 8192;

    public function __construct(
        private \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        private \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        private \Kount\Kount360\Model\Config\Account $configAccount,
        private ?\Magento\Framework\Encryption\EncryptorInterface $encryptor = null,
        private ?\Magento\Store\Model\StoreManagerInterface $storeManager = null,
        private ?\Kount\Kount360\Model\Logger $logger = null
    ) {
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        $stored = $this->scopeConfig->getValue($this->getPath(), ScopeInterface::SCOPE_WEBSITE);

        if (empty($stored)) {
            return null;
        }

        if ($this->encryptor === null) {
            return (string)$stored;
        }

        try {
            $decrypted = $this->encryptor->decrypt((string)$stored);
        } catch (\Exception $e) {
            $decrypted = '';
        }

        if (!$this->isPlausibleToken($decrypted)) {
            $this->logger?->info('Stored Kount access token is unusable; re-authenticating.');
            return null;
        }

        return $decrypted;
    }

    /**
     * @param string $value
     * @return bool
     */
    private function isPlausibleToken(string $value): bool
    {
        $length = strlen($value);

        if ($length < self::MIN_TOKEN_LENGTH || $length > self::MAX_TOKEN_LENGTH) {
            return false;
        }

        return ctype_print($value);
    }

    /**
     * @param $accessToken
     * @return void
     */
    public function setAccessToken($accessToken): void
    {
        if (!$accessToken) {
            return;
        }

        $this->setOauthData($accessToken);

        $this->cacheTypeList->cleanType('config');
    }

    /**
     * @param $data
     * @return void
     */
    private function setOauthData($data): void
    {
        $value = $this->encryptor !== null ? $this->encryptor->encrypt((string)$data) : $data;

        [$scope, $scopeId] = $this->resolveWriteScope();

        $this->configWriter->save($this->getPath(), $value, $scope, $scopeId);
    }

    /**
     * @return string
     */
    private function getPath(): string
    {
        return $this->configAccount->isTestMode()
            ? self::ACCESS_TOKEN_CONFIG_PATH_TEST
            : self::ACCESS_TOKEN_CONFIG_PATH_PROD;
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function resolveWriteScope(): array
    {
        if ($this->storeManager === null) {
            return [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0];
        }

        try {
            return [ScopeInterface::SCOPE_WEBSITES, (int)$this->storeManager->getWebsite()->getId()];
        } catch (\Exception $e) {
            return [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0];
        }
    }
}
