<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Plugin\GraphQl;

use Pinblooms\CheckoutGuard\Model\AddressValidator;
use Pinblooms\CheckoutGuard\Model\Config;
use Pinblooms\CheckoutGuard\Model\GraphQlInputReader;
use Pinblooms\CheckoutGuard\Model\GuardLogger;
use Pinblooms\CheckoutGuard\Model\RateLimiter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\SetShippingAddressesOnCart;

class ValidateShippingAddressesOnCart
{
    public function __construct(
        private readonly AddressValidator $addressValidator,
        private readonly Config $config,
        private readonly GraphQlInputReader $inputReader,
        private readonly GuardLogger $guardLogger,
        private readonly RateLimiter $rateLimiter
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        SetShippingAddressesOnCart $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): void {
        if (!$this->config->isEnabled()) {
            return;
        }

        $cartId = $this->inputReader->getMaskedCartId($args);

        try {
            $this->rateLimiter->assertAllowed('shipping_address', $cartId);
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        if (!$this->config->isAddressInjectionBlockingEnabled()) {
            return;
        }

        foreach ($this->inputReader->getShippingAddressEntries($args) as $entry) {
            if (isset($entry['customer_address_id'])) {
                continue;
            }

            $address = $entry['address'] ?? $entry;

            if (!is_array($address) || $address === []) {
                continue;
            }

            try {
                $this->addressValidator->assertAllowedAddress($address);
            } catch (LocalizedException $exception) {
                $this->guardLogger->logBlocked(
                    'shipping_address',
                    (string) $exception->getMessage()
                );
                throw new GraphQlInputException(__($exception->getMessage()));
            }
        }
    }
}
