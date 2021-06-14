<?php

namespace Luxinten\ReferalKlarna\Model\Api\Request;

use Klarna\Kp\Model\Api\Request\AddressFactory;
use Klarna\Kp\Model\Api\Request\AttachmentFactory;
use Klarna\Kp\Model\Api\Request\CustomerFactory;
use Klarna\Kp\Model\Api\Request\MerchantUrlsFactory;
use Klarna\Kp\Model\Api\Request\OptionsFactory;
use Klarna\Kp\Model\Api\Request\OrderlineFactory;
use Klarna\Kp\Api\Data\OrderlineInterface;
use Klarna\Kp\Model\Api\RequestFactory;

class Builder extends \Klarna\Kp\Model\Api\Request\Builder
{
    protected $checkoutSession;

    private $order_amount = 0;
    private $merchant_reference1;
    private $merchant_reference2;
    private $purchase_country;
    private $purchase_currency;
    private $locale;
    private $order_tax_amount = 0;
    private $customer;
    private $attachment;
    private $billing_address;
    private $shipping_address;
    private $merchant_urls;
//    private $orderlines = [];
    private $options;
    private $requestFactory;

    public function __construct(RequestFactory $requestFactory, AddressFactory $addressFactory, AttachmentFactory $attachmentFactory, CustomerFactory $customerFactory, MerchantUrlsFactory $urlFactory, OptionsFactory $optionsFactory, OrderlineFactory $orderlineFactory, \Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($requestFactory, $addressFactory, $attachmentFactory, $customerFactory, $urlFactory, $optionsFactory, $orderlineFactory);
    }

    public function getRequest()
    {
        return $this->requestFactory->create([
            'data' => [
                'purchase_country'    => $this->purchase_country,
                'purchase_currency'   => $this->purchase_currency,
                'locale'              => $this->locale,
                'customer'            => $this->customer,
                'options'             => $this->options,
                'order_amount'        => $this->order_amount,
                'order_tax_amount'    => $this->order_tax_amount,
                'urls'                => $this->merchant_urls,
                'attachment'          => $this->attachment,
                'billing_address'     => $this->billing_address,
                'shipping_address'    => $this->shipping_address,
//                'order_lines'         => $this->orderlines,
                'merchant_reference1' => $this->merchant_reference1,
                'merchant_reference2' => $this->merchant_reference2

            ]
        ]);
    }


    public function validate($requiredAttributes, $type)
    {
        $missingAttributes = [];
        foreach ($requiredAttributes as $requiredAttribute) {
            if (null === $this->$requiredAttribute) {
                $missingAttributes[] = $requiredAttribute;
            }
            if (is_array($this->$requiredAttribute) && count($this->$requiredAttribute) === 0) {
                $missingAttributes[] = $requiredAttribute;
            }
        }
        if (!empty($missingAttributes)) {
            throw new KlarnaApiException(
                __(
                    'Missing required attribute(s) on %1: "%2".',
                    $type,
                    implode(', ', $missingAttributes)
                )
            );
        }
//        $total = 0;

//        foreach ($this->orderlines as $orderLine) {
//            $total += (int)$orderLine->getTotal();
//        }
        $quote = $this->checkoutSession->getQuote();
        $total = $quote->getGrandTotal();
        $total = intval(str_replace('.', '', $total));

        if ($total  !== $this->order_amount) {
            throw new KlarnaApiException(
                __('Order line totals do not total order_amount - %1 != %2', $total, $this->order_amount)
            );
        }

        return $this;
    }
}
