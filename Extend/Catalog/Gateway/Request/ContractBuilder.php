<?php


namespace Extend\Catalog\Gateway\Request;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;
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
     * @param Order $order
     * @param $warranties
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareInfo($order, $warranties)
    {
        $contracts = [];

        /** @var Product $warranty */
        foreach ($warranties as $warranty) {
            $productId = $warranty->getCustomAttribute('assocProduct');

            if (empty($productId)) {
                continue;
            }

            try {
                $product = $this->productRepository->getById($productId->getValue());
            } catch (NoSuchEntityException $exception) {
                continue;
            }

            $billing = $order->getBillingAddress();

            $shipping = $order->getShippingAddress();


            $contracts[$warranty->getSku()] = [
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
            $contracts[$warranty->getSku()]['customer']['billing'] = array_merge($contracts[$warranty->getSku()]['customer']['billing'],$billingFormat);

            $shippingStreet = $shipping->getStreet();
            $shippingFormat = $this->formatStreet($shippingStreet);
            $contracts[$warranty->getSku()]['customer']['shipping'] = array_merge($contracts[$warranty->getSku()]['customer']['shipping'],$shippingFormat);

            if(!$order->getCustomerIsGuest()){
                $contracts[$warranty->getSku()]['customer']['customerId'] = $order->getCustomerId();
            }

        }
        return $contracts;
    }

    private function formatStreet($street)
    {
        $address = [];

        $address['address1'] = array_shift($street);
        if(!empty($street)){
            $address['address2'] = implode(",",$street);
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