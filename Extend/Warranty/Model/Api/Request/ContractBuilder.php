<?php


namespace Extend\Warranty\Model\Api\Request;

use Extend\Warranty\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;
use Magento\Directory\Api\CountryInformationAcquirerInterface;

class ContractBuilder
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInformationAcquirer;

    /**
     * @var Data
     */
    protected $helper;


    public function __construct
    (
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        Data $helper
    )
    {

        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->helper = $helper;
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
                'transactionTotal' => $this->helper->formatPrice($order->getGrandTotal()),
                'customer' => [
                    'phone' => $billing->getTelephone(),
                    'email' => $order->getCustomerEmail(),
                    'name' => $order->getCustomerName(),
                    'billing' => [
                        "postalCode" => $billing->getPostcode(),
                        "city" => $billing->getCity(),
                        "country" => $this->countryInformationAcquirer
                            ->getCountryInfo(
                                $billing->getCountryId()
                            )->getThreeLetterAbbreviation(),
                        "region" => $billing->getRegion()
                    ],
                    'shipping' => [
                        "postalCode" => $shipping->getPostcode(),
                        "city" => $shipping->getCity(),
                        "country" => $this->countryInformationAcquirer
                            ->getCountryInfo(
                                $shipping->getCountryId()
                            )->getThreeLetterAbbreviation(),
                        "region" => $shipping->getRegion()
                    ]
                ],
                'product' => [
                    'referenceId' => $product->getSku(),
                    'purchasePrice' => $this->helper->formatPrice($product->getFinalPrice()),
                    'title' => $product->getName()
                ],
                'currency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                'transactionDate' => strtotime($order->getCreatedAt()),
                'plan' => [
                    'purchasePrice' => $this->helper->formatPrice($warranty->getFinalPrice()),
                    'planId' => $this->formatPlanId($warranty->getSku())
                ]
            ];

            $billingStreet = $billing->getStreet();
            $billingFormat = $this->formatStreet($billingStreet);

            $contracts[$warranty->getSku()]['customer']['billing'] = array_merge(
                $contracts[$warranty->getSku()]['customer']['billing'],
                $billingFormat
            );

            $shippingStreet = $shipping->getStreet();
            $shippingFormat = $this->formatStreet($shippingStreet);

            $contracts[$warranty->getSku()]['customer']['shipping'] = array_merge(
                $contracts[$warranty->getSku()]['customer']['shipping'],
                $shippingFormat
            );

            if (!$order->getCustomerIsGuest()) {
                $contracts[$warranty->getSku()]['customer']['customerId'] = $order->getCustomerId();
            }

        }
        return $contracts;
    }

    private function formatStreet($street): array
    {
        $address = [];

        $address['address1'] = array_shift($street);
        if (!empty($street)) {
            $address['address2'] = implode(",",$street);
        }

        return $address;
    }

    private function formatPlanId($sku): string
    {
        $formatSku = explode(":", $sku);
        return current($formatSku);
    }
}