<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;

class GuestQuoteChecker
{
    public function __construct(
        private readonly MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private readonly CartRepositoryInterface $cartRepository
    ) {
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getQuoteByMaskedCartId(string $maskedCartId): Quote
    {
        if ($maskedCartId === '') {
            throw new NoSuchEntityException(__('Cart not found.'));
        }

        $quoteId = (int) $this->maskedQuoteIdToQuoteId->execute($maskedCartId);

        if ($quoteId <= 0) {
            throw new NoSuchEntityException(__('Cart not found.'));
        }

        /** @var Quote $quote */
        $quote = $this->cartRepository->get($quoteId);

        return $quote;
    }

    /**
     * True only when the quote is not tied to a logged-in customer account.
     */
    public function isGuestQuote(Quote $quote): bool
    {
        return (int) $quote->getCustomerId() <= 0;
    }
}
