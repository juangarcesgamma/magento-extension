<?php


namespace Extend\Warranty\Model\Api\Request;

use Braintree\Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Catalog\Model\Product;
use Magento\Directory\Api\CountryInformationAcquirerInterface;

class ContractBuilder
{

    protected $productRepository;
    protected $storeManager;
    protected $countryInformationAcquirer;


    public function __construct
    (
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        CountryInformationAcquirerInterface $countryInformationAcquirer
    )
    {

        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
    }

    /**
     * @param OrderInterface $order
     * @param Product $warranty
     * @return array
     * @throws Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build($order, $warranty)
    {
        $productId = $warranty->getCustomAttribute('assocProduct');

        if (empty($productId)) {
            throw new Exception("Unable to create warranty contract, no associate product");
        }

        $product = $this->productRepository->getById($productId->getValue());

        $billing = $order->getBillingAddress();

        $shipping = $order->getShippingAddress();


        $contract = [
            'transactionId' => $order->getId(),
            'transactionTotal' => $this->formatPrice($order->getGrandTotal()),
            'customer' => [
                'phone' => $billing->getTelephone(),
                'email' => $order->getCustomerEmail(),
                'name' => $order->getCustomerName(),
                'billing' => [
                    "postalCode" => $billing->getPostcode(),
                    "city" => $billing->getCity(),
                    "country" => $this->countryInformationAcquirer->getCountryInfo($billing->getCountryId())->getThreeLetterAbbreviation(),
                    "region" => $billing->getRegion()
                ],
                'shipping' => [
                    "postalCode" => $shipping->getPostcode(),
                    "city" => $shipping->getCity(),
                    "country" => $this->countryInformationAcquirer->getCountryInfo($shipping->getCountryId())->getThreeLetterAbbreviation(),
                    "region" => $shipping->getRegion()
                ]
            ],
            'product' => [
                'referenceId' => $product->getSku(),
                'purchasePrice' => $this->formatPrice($product->getFinalPrice()),
                'title' => $product->getName()
            ],
            'currency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
            'transactionDate' => strtotime($order->getCreatedAt()),
            'plan' => [
                'purchasePrice' => $this->formatPrice($warranty->getFinalPrice()),
                'planId' => $this->formatPlanId($warranty->getSku())
            ]
        ];

        $billingStreet = $billing->getStreet();
        $billingFormat = $this->formatStreet($billingStreet);
        $contract['customer']['billing'] = array_merge($contract['customer']['billing'], $billingFormat);

        $shippingStreet = $shipping->getStreet();
        $shippingFormat = $this->formatStreet($shippingStreet);
        $contract['customer']['shipping'] = array_merge($contract['customer']['shipping'], $shippingFormat);

        if (!$order->getCustomerIsGuest()) {
            $contract['customer']['customerId'] = $order->getCustomerId();
        }

        return $contract;
    }

    private function formatStreet($street)
    {
        $address = [];

        $address['address1'] = array_shift($street);
        if (!empty($street)) {
            $address['address2'] = implode(",", $street);
        }

        return $address;
    }

    private function formatPlanId($sku)
    {
        $formatSku = explode(":", $sku);
        return $formatSku[0];
    }

    private function formatPrice($price)
    {
        if (!empty($price)) {
            $floatPrice = floatval($price);

            $formattedPrice = number_format($floatPrice, 2, '', '');

            return intval($formattedPrice);
        }
        return 0;
    }
}