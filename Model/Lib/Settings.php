<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Lib;

use Kount\Kount360\Model\Config\Log;
use Magento\Framework\App\Filesystem\DirectoryList;
use Kount\Kount360\Exception\KountException;

class Settings
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Settings constructor.
     */
    public function __construct(
        protected \Kount\Kount360\Model\Config\Account $configAccount,
        protected \Kount\Kount360\Model\Config\Log $configLog,
        protected \Magento\Framework\Filesystem $filesystem
    ) {
    }

    /**
     * @param string|null $websiteCode
     */
    protected function initSettings($websiteCode = null)
    {
        $this->settings = [
            'MERCHANT_ID' => $this->configAccount->getClientId($websiteCode),
            'URL' => $this->configAccount->getRisUrl($websiteCode),
            'PEM_CERTIFICATE' => null,
            'PEM_KEY_FILE' => null,
            'PEM_PASS_PHRASE' => null,
            'API_KEY' => $this->configAccount->getApiKey($websiteCode),
            'CONFIG_KEY' => $this->configAccount->getConfigKey(),
            'CONNECT_TIMEOUT' => 20,
        ];
        if ($this->configLog->isEnabled($websiteCode)) {
            $this->settings += [
                'LOGGER' => 'SIMPLE',
                'SIMPLE_LOG_LEVEL' => 'DEBUG',
                'SIMPLE_LOG_FILE' => Log::FILENAME,
                'SIMPLE_LOG_PATH' => $this->filesystem->getDirectoryRead(DirectoryList::LOG)->getAbsolutePath(),
                'SIMPLE_LOG_RIS_METRICS' => $this->configLog->isRisMetricsEnabled()
            ];
        } else {
            $this->settings += [
                'LOGGER' => 'NOP',
                'SIMPLE_LOG_LEVEL' => 'FATAL',
                'SIMPLE_LOG_FILE' => null,
                'SIMPLE_LOG_PATH' => null,
                'SIMPLE_LOG_RIS_METRICS' => false
            ];
        }
    }

    /**
     * @param string|null $websiteCode
     * @return array
     */
    public function getSettings($websiteCode = null)
    {
        $this->initSettings($websiteCode);
        return $this->settings;
    }

    /**
     * @param string $name
     * @param string|null $websiteCode
     * @return string
     * @throws \Exception
     */
    public function getConfigSetting($name, $websiteCode = null)
    {
        $settings = $this->getSettings($websiteCode);
        if (!isset($settings[$name])) {
            throw new KountException("The configuration setting [{$name}] is not defined");
        }
        return $settings[$name];
    }
}
