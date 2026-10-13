<?php
/**
 * Copyright (c) 2026 KOUNT, INC.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kount\Kount360\Model\Lib;

class PayloadSanitizer
{
    const MAX_LENGTH = 8192;

    const MASK = '***';

    /**
     * @var string[]
     */
    private const SENSITIVE_KEYS = [
        'email',
        'emailaddress',
        'phone',
        'phonenumber',
        'telephone',
        'ip',
        'ipaddress',
        'address',
        'addressline1',
        'addressline2',
        'line1',
        'line2',
        'street',
        'postalcode',
        'postcode',
        'zip',
        'firstname',
        'lastname',
        'name',
        'cardnumber',
        'bin',
        'last4',
        'token',
        'accesstoken',
        'authorization',
        'apikey',
        'password',
    ];

    /**
     * @param mixed $payload
     * @return string
     */
    public function sanitize($payload): string
    {
        if (is_array($payload)) {
            $payload = json_encode($this->maskArray($payload));
        }

        if (!is_scalar($payload)) {
            return '';
        }

        return $this->truncate($this->neutralize((string)$payload));
    }

    /**
     * @param string $value
     * @return string
     */
    public function neutralize(string $value): string
    {
        return str_replace('<?', '< ?', $value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function truncate(string $value): string
    {
        if (strlen($value) <= self::MAX_LENGTH) {
            return $value;
        }

        return substr($value, 0, self::MAX_LENGTH) . '… [truncated]';
    }

    /**
     * @param array $data
     * @return array
     */
    private function maskArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->maskArray($value);
                continue;
            }

            if ($this->isSensitive((string)$key)) {
                $data[$key] = self::MASK;
            }
        }

        return $data;
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isSensitive(string $key): bool
    {
        $normalized = strtolower(str_replace(['_', '-', ' '], '', $key));

        return in_array($normalized, self::SENSITIVE_KEYS, true);
    }
}
