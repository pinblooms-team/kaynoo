<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\Exception\LocalizedException;

class AddressValidator
{
    private const TEMPLATE_INJECTION_PATTERN =
        '/\{\{|\}\}|%\}|%\{|\$\{|<script|javascript:|\{\{depend|\{\{var|\{\{block|\{\{\/|Magento\\\\Email/i';

    private const GARBAGE_VALUE_PATTERN = '/^x+$/i';

    /** US fictional / reserved test numbers commonly used by bots. */
    private const FAKE_PHONE_PATTERN = '/(?:\+?1[\s.\-]?)?\(?555\)?[\s.\-]?01\d{2}\b/';

    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * @param array<string, mixed> $address
     * @throws LocalizedException
     */
    public function assertAllowedAddress(array $address, ?int $storeId = null): void
    {
        if (!$this->config->isAddressInjectionBlockingEnabled($storeId)) {
            return;
        }

        foreach ($this->flattenAddressValues($address) as $value) {
            $this->assertAllowedScalar($value);
        }

        $this->assertAllowedNames($address);
        $this->assertAllowedTelephone($address);
    }

    /**
     * @param array<string, mixed> $address
     * @throws LocalizedException
     */
    private function assertAllowedNames(array $address): void
    {
        $first = trim((string) ($address['firstname'] ?? ''));
        $last = trim((string) ($address['lastname'] ?? ''));

        if ($first === '' && $last === '') {
            return;
        }

        // Classic bot pattern: "A B"
        if (preg_match('/^[A-Za-z]$/', $first) && preg_match('/^[A-Za-z]$/', $last)) {
            throw new LocalizedException(
                __('Please enter a valid first and last name.')
            );
        }
    }

    /**
     * @param array<string, mixed> $address
     * @throws LocalizedException
     */
    private function assertAllowedTelephone(array $address): void
    {
        $phone = trim((string) ($address['telephone'] ?? ''));
        if ($phone === '') {
            return;
        }

        if (preg_match(self::FAKE_PHONE_PATTERN, $phone)) {
            throw new LocalizedException(
                __('Please enter a valid phone number.')
            );
        }
    }

    /**
     * @throws LocalizedException
     */
    private function assertAllowedScalar(string $value): void
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return;
        }

        if (preg_match(self::TEMPLATE_INJECTION_PATTERN, $trimmed)) {
            throw new LocalizedException(
                __('Please enter a valid address without special template characters.')
            );
        }

        if (preg_match(self::GARBAGE_VALUE_PATTERN, $trimmed)) {
            throw new LocalizedException(
                __('Please enter a valid address.')
            );
        }

        if (str_contains($trimmed, '{{var postcode}}') || str_contains($trimmed, '{{depend')) {
            throw new LocalizedException(
                __('Please enter a valid address.')
            );
        }
    }

    /**
     * @param array<string, mixed> $address
     * @return string[]
     */
    private function flattenAddressValues(array $address): array
    {
        $values = [];

        foreach ($address as $value) {
            if (is_array($value)) {
                foreach ($this->flattenAddressValues($value) as $nestedValue) {
                    $values[] = $nestedValue;
                }
                continue;
            }

            if (is_string($value) || is_numeric($value)) {
                $values[] = (string) $value;
            }
        }

        return $values;
    }
}
