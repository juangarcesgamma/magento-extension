<?php


namespace Extend\Catalog\Gateway\Request;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;

class ContractBuilder
{

    protected $productRepository;
    protected $storeManager;


    public function __construct
    (
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    )
    {

        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
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

            $contracts[$warranty->getSku()] = [
                'transactionId' => $order->getId(),
                'transactionTotal' => $this->formatPrice($order->getGrandTotal()),
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

            $contracts[$warranty->getSku()]['customer'] = $order->getCustomerIsGuest() ?
                [
                    'email' => $order->getCustomerEmail(),
                ] :
                [
                    'customerId' => $order->getCustomerId(),
                    'email' => $order->getCustomerEmail(),
                    'name' => $order->getCustomerName()
                ];
        }
        return $contracts;
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