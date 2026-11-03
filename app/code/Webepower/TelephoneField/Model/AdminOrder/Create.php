<?php

namespace Webepower\TelephoneField\Model\AdminOrder;

class Create extends \Magento\Sales\Model\AdminOrder\Create
{
    /**
     * Prepare customer data for order creation.
     *
     * Create customer if not created using data from customer form.
     * Create customer billing/shipping address if necessary using data from customer address forms.
     * Set customer data to quote.
     *
     * @return \Magento\Sales\Model\AdminOrder\Create
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */

    public function _prepareCustomer()
    {
        if ($this->getQuote()->getCustomerIsGuest()) {
            return $this;
        }
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->getSession()->getStore();
        $customer = $this->getQuote()->getCustomer();

        if ($customer->getId() && !$this->_customerIsInStore($store)) {
            /** Create a new customer record if it is not available in the specified store */
            /** Unset customer ID to ensure that new customer will be created */
            $customer->setId(null)
                ->setStoreId($store->getId())
                ->setWebsiteId($store->getWebsiteId())
                ->setCreatedAt(null);
            $customer = $this->_validateCustomerData($customer);
        } elseif (!$customer->getId()) {
            /** Create new customer */

            $customerBillingAddressDataObject = $this->getBillingAddress()->exportCustomerAddress();
            $customer->setSuffix($customerBillingAddressDataObject->getSuffix())
                ->setFirstname($customerBillingAddressDataObject->getFirstname())
                ->setLastname($customerBillingAddressDataObject->getLastname())
                ->setMiddlename($customerBillingAddressDataObject->getMiddlename())
                ->setPrefix($customerBillingAddressDataObject->getPrefix())
                ->setStoreId($store->getId())
                ->setWebsiteId($store->getWebsiteId())
                ->setCustomAttribute('phone_number', $customerBillingAddressDataObject->getTelephone())
                ->setEmail($this->_getNewCustomerEmail());
            $customer = $this->_validateCustomerData($customer);
        }
        $this->getQuote()->setCustomer($customer);

        if ($this->getBillingAddress()->getSaveInAddressBook()) {
            $this->_prepareCustomerAddress($this->getQuote()->getCustomer(), $this->getBillingAddress());
            $address = $this->getBillingAddress()->setCustomerId($this->getQuote()->getCustomer()->getId());
            $this->setBillingAddress($address);
        }
        if (!$this->getQuote()->isVirtual() && $this->getShippingAddress()->getSaveInAddressBook()) {
            $this->_prepareCustomerAddress($this->getQuote()->getCustomer(), $this->getShippingAddress());
            $address = $this->getShippingAddress()->setCustomerId($this->getQuote()->getCustomer()->getId());
            $this->setShippingAddress($address);
        }
        $this->getBillingAddress()->setCustomerId($customer->getId());
        $this->getQuote()->updateCustomerData($this->getQuote()->getCustomer());

        $customer = $this->getQuote()->getCustomer();
        $origAddresses = $customer->getAddresses(); // save original addresses
        $customer->setAddresses([]);
        $customerData = $this->customerMapper->toFlatArray($customer);
        $customer->setAddresses($origAddresses); // restore original addresses
        foreach ($this->_createCustomerForm($customer)->getUserAttributes() as $attribute) {
            if (isset($customerData[$attribute->getAttributeCode()])) {
                $quoteCode = sprintf('customer_%s', $attribute->getAttributeCode());
                $this->getQuote()->setData($quoteCode, $customerData[$attribute->getAttributeCode()]);
            }
        }

        return $this;
    }
}
