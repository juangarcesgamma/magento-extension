<?php

namespace Extend\Warranty\Plugin\Api;

use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;


class OrderRepository
{

    const CONTRACT_ID = 'contract_id';
    const PRODUCT_OPTIONS = 'product_options';

    /**
     * Order Extension Attributes Factory
     *
     * @var OrderItemExtensionFactory
     */
    protected $extensionFactory;
    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(
        OrderItemExtensionFactory $extensionFactory
    )
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Add "contract_id & product_options" extension attributes to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $orderItem)
    {
        //contract_id
        $contractId = $orderItem->getData(self::CONTRACT_ID);
        //product_options
        $productOptions = $orderItem->getProductOptions();      
        $extensionAttributes = $orderItem->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        $extensionAttributes->setContractId($contractId);
        if(array_key_exists("warranty_term", $productOptions) && array_key_exists("associated_product", $productOptions) && array_key_exists("warranty_id", $productOptions))
        {
            $extensionAttributes->setTerm($productOptions["warranty_term"]);
            $extensionAttributes->setAssociatedProduct($productOptions["associated_product"]);
            $extensionAttributes->setWarrantyId($productOptions["warranty_id"]);
        }
        $extensionAttributes->setProductOptions(json_encode($productOptions));
        $orderItem->setExtensionAttributes($extensionAttributes);

        return $orderItem;
    }

    /**
     * Add "contract_id & product_options" extension attributes to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderItemRepositoryInterface $subject, OrderItemSearchResultInterface $searchResult)
    {
        $ordersItems = $searchResult->getItems();

        foreach ($ordersItems as &$orderItem) {
            //contract_id
            $contractId = $orderItem->getData(self::CONTRACT_ID);
            //product_options
            $productOptions = $orderItem->getProductOptions();
            $extensionAttributes = $orderItem->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setContractId($contractId);
            $test = [];
            if(array_key_exists("warranty_term", $productOptions) && array_key_exists("associated_product", $productOptions) && array_key_exists("warranty_id", $productOptions))
            {
                $extensionAttributes->setTerm($productOptions["warranty_term"]);
                $extensionAttributes->setAssociatedProduct($productOptions["associated_product"]);
                $extensionAttributes->setWarrantyId($productOptions["warranty_id"]);
                $test = array(
                    "warranty_term" => $productOptions["warranty_term"],
                    "associated_product" => $productOptions["associated_product"],
                    "warranty_id" => $productOptions["warranty_id"]
                );
                $extensionAttributes->setTest($test);
            }
            $extensionAttributes->setProductOptions(json_encode($productOptions));
            $orderItem->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}