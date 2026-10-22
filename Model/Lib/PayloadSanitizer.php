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
     * Sequences that are meaningful to a downstream interpreter and must never survive verbatim.
     *
     * `<?` opens a PHP block in any plaintext file that is later included.
     * `{{` / `}}` open and close a directive in Magento's template engine, which processes order
     * comments and other persisted strings when they are rendered into transactional emails.
     *
     * @var string[]
     */
    private const NEUTRALIZED_SEQUENCES = [
        '<?' => '< ?',
        '{{' => '{ {',
        '}}' => '} }',
    ];

    /**
     * Keys whose values are masked. Email, IP and postal address are deliberately absent:
     * they are the fields support needs to match a Kount order back to a Magento order.
     *
     * A masked key keeps its place in the payload with `***` for a value, so the log still
     * shows which fields were sent. A sensitive key holding an array (`name: {first, last}`)
     * is recursed into, which is why the inner `first` / `last` keys are listed here too.
     *
     * @var string[]
     */
    private const SENSITIVE_KEYS = [
        'phone',
        'phonenumber',
        'telephone',
        'firstname',
        'lastname',
        'name',
        'first',
        'last',
        'cardnumber',
        'bin',
        'last4',
        'token',
        'paymenttoken',
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
        $search = array_keys(self::NEUTRALIZED_SEQUENCES);
        $replace = array_values(self::NEUTRALIZED_SEQUENCES);

        // A single pass is not enough: `{{{{` becomes `{ {{ {`, which still carries a `{{` pair.
        // Each pass inserts separators and never creates a new adjacency, so this converges.
        do {
            $previous = $value;
            $value = str_replace($search, $replace, $value);
        } while ($value !== $previous);

        return $value;
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
