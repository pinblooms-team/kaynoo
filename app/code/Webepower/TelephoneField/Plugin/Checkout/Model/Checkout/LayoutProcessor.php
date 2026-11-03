<?php

namespace Webepower\TelephoneField\Plugin\Checkout\Model\Checkout;

use Magento\Customer\Model\Session;

class LayoutProcessor
{
    private $customer;

    public function __construct(Session $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {

        $phoneNumber = $this->customer->getCustomer()->getPhoneNumber();

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['value'] = $phoneNumber;
        return $jsLayout;
    }
}
