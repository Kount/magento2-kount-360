<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Ens
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param string $ips
     * @return array
     */
    protected function explodeIps($ips): array
    {
        return empty($ips) ? [] : explode(',', str_replace(' ', '', $ips));
    }

    /**
     * @return array
     */
    public function getKountIps(): array
    {
        $ips = $this->scopeConfig->getValue('kount360/ens/kount_ips');
        return $this->explodeIps($ips);
    }

    /**
     * @param string|null $websiteCode
     * @return array
     */
    public function getAdditionIps($websiteCode = null): array
    {
        $ips = $this->scopeConfig->getValue('kount360/ens/addition_ips', ScopeInterface::SCOPE_WEBSITE, $websiteCode);
        return $this->explodeIps($ips);
    }

    /**
     *
     * @param $ip
     * @param array $ips
     * @return bool
     */
    public function isInCidrRange($ip, array $ips) : bool
    {
        $cidrRanges = array_filter(
            $ips,
            function ($ipAddress) {
                return is_string($ipAddress) && strpos($ipAddress, '/') !== false;
            }
        );

        if (empty($cidrRanges)) {
            return false;
        }

        $packedIp = @inet_pton((string)$ip);
        if ($packedIp === false) {
            return false;
        }

        foreach ($cidrRanges as $cidrIp) {
            if ($this->matchesCidr($packedIp, (string)$cidrIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $packedIp
     * @param string $cidrIp
     * @return bool
     */
    private function matchesCidr(string $packedIp, string $cidrIp): bool
    {
        $parts = explode('/', $cidrIp, 2);
        if (count($parts) !== 2 || !is_numeric($parts[1])) {
            return false;
        }

        $packedSubnet = @inet_pton(trim($parts[0]));
        if ($packedSubnet === false || strlen($packedSubnet) !== strlen($packedIp)) {
            return false;
        }

        $maxBits = strlen($packedIp) * 8;
        $prefixBits = (int)$parts[1];
        if ($prefixBits < 0 || $prefixBits > $maxBits) {
            return false;
        }

        if ($prefixBits === 0) {
            return true;
        }

        $wholeBytes = intdiv($prefixBits, 8);
        $remainderBits = $prefixBits % 8;

        if ($wholeBytes > 0 && strncmp($packedIp, $packedSubnet, $wholeBytes) !== 0) {
            return false;
        }

        if ($remainderBits === 0) {
            return true;
        }

        $mask = chr((0xFF << (8 - $remainderBits)) & 0xFF);

        return ($packedIp[$wholeBytes] & $mask) === ($packedSubnet[$wholeBytes] & $mask);
    }

    /**
     * @param string|null $websiteCode
     * @return array
     */
    public function getAllowedIps($websiteCode = null): array
    {
        return array_merge($this->getKountIps(), $this->getAdditionIps($websiteCode));
    }

    /**
     * @param string $ip
     * @param string|null $websiteCode
     * @return bool
     */
    public function isAllowedIp($ip, $websiteCode = null): bool
    {
        $allowedIps = $this->getAllowedIps($websiteCode);
        return in_array($ip, $allowedIps, true) || $this->isInCidrRange($ip, $allowedIps) ;
    }

    /**
     * @param string|null $websiteCode
     * @return bool
     */
    public function isEnabled($websiteCode = null): bool
    {
        return (bool)$this->scopeConfig->getValue('kount360/ens/enabled', ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }
}
