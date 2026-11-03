<?php

namespace Webepower\TelephoneField\Block;

class CustomerData extends \Magento\Framework\View\Element\Template
{
    private $customer;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customer,
        array $data = []
    ) {
        $this->customer = $customer;
        parent::__construct($context, $data);
    }

    public function phoneNumber()
    {
        $phoneNumber = $this->customer->getCustomer()->getPhoneNumber();
        return $phoneNumber;
    }
}
