<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

/**
 * Safe GraphQL args parsing — never throws on missing/null args.
 */
class GraphQlInputReader
{
    public function getInput(?array $args): array
    {
        if (!is_array($args)) {
            return [];
        }

        $input = $args['input'] ?? null;

        return is_array($input) ? $input : [];
    }

    public function getGuestEmail(?array $args): ?string
    {
        $email = $this->getInput($args)['email'] ?? null;

        return is_string($email) ? $email : null;
    }

    public function getMaskedCartId(?array $args): ?string
    {
        $cartId = $this->getInput($args)['cart_id'] ?? null;

        if (!is_string($cartId)) {
            return null;
        }

        $cartId = trim($cartId);

        return $cartId !== '' ? $cartId : null;
    }

    public function getPaymentMethodCode(?array $args): ?string
    {
        $paymentMethod = $this->getInput($args)['payment_method'] ?? null;

        if (!is_array($paymentMethod)) {
            return null;
        }

        $code = $paymentMethod['code'] ?? null;

        return is_string($code) && $code !== '' ? $code : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getBillingAddress(?array $args): array
    {
        $billingAddress = $this->getInput($args)['billing_address'] ?? null;

        if (!is_array($billingAddress)) {
            return [];
        }

        $address = $billingAddress['address'] ?? $billingAddress;

        return is_array($address) ? $address : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getShippingAddressEntries(?array $args): array
    {
        $shippingAddresses = $this->getInput($args)['shipping_addresses'] ?? null;

        if (!is_array($shippingAddresses)) {
            return [];
        }

        $entries = [];

        foreach ($shippingAddresses as $entry) {
            if (is_array($entry)) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }
}
