<?php

namespace Webepower\TelephoneField\Plugin\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;

class OrderCustomerExtractor
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Around Plugin
     *
     * @param \Magento\Sales\Model\Order\OrderCustomerExtractor $subject
     * @param callable $proceed
     * @param integer $orderId
     * @return void
     */
    public function aroundExtract(
        \Magento\Sales\Model\Order\OrderCustomerExtractor $subject,
        callable $proceed,
        int $orderId
    ) {
        $result = $proceed($orderId);

        $order = $this->orderRepository->get($orderId);

        $phoneNumber = '';
        foreach ($order->getAddresses() as $orderAddress) {
            // billing address
            if ($orderAddress->getAddressType() == OrderAddress::TYPE_BILLING) {
                $phoneNumber = $orderAddress->getTelephone();
            }
        }
        $result->setCustomAttribute('phone_number', $phoneNumber);
        return $result;
    }
}
