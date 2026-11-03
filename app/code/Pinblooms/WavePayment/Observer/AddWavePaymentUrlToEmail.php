<?php

namespace Pinblooms\WavePayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddWavePaymentUrlToEmail implements ObserverInterface
{
    /**
     * Add Wave Payment URL to email template variables
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getEvent()->getTransport();
        $order = $transport->getData('order');


        if ($order) {
            $grandTotal = $order->getGrandTotal();
            $amountWithFee = round($grandTotal * 1.01); // Calculate amount with 1% fee
            $wavePaymentUrl = 'https://pay.wave.com/m/M_r_UjrChcJ9wN/c/sn/?amount=' . $amountWithFee;

            // Add wave_payment_url to email variables
            $transport->setData('wave_payment_url', $wavePaymentUrl);
        }
    }
}
