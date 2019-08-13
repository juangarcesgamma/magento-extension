<?php

namespace Extend\Catalog\Plugin\Controller\Cart;

use Magento\Framework\App\ResourceConnection;
use Magento\Checkout\Controller\Cart\Add as SuperAdd;
use \Magento\Framework\App\Request\Http;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;

class Add
{
    protected $request;
    protected $cart;
    protected $productFactory;
    protected $formKey;
    protected $productRepository;
    protected $storeManager;
    protected $registry;
    protected $connection;

    public function __construct
    (
        Http $request,
        Cart $cart,
        ProductFactory $productFactory,
        FormKey $formKey,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        Registry $registry,
        ResourceConnection $connection
    )
    {
        $this->request = $request;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->formKey = $formKey;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->connection = $connection;

    }

    public function beforeExecute(SuperAdd $subject)
    {
        $productId = $this->request->getPost('product');

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        if ($product->getSku() == '24-MB03') {

            // HARDCORED WARRANTY - HEL-167

            $warrantyData = [
                'id' => "10001-misc-elec-base-replace-1y",
                'title' => "Extend Protection Plan - Electronics",
                'imageUrl' => "https://extend-js-sdk.s3.amazonaws.com/extend_icon.png",
                "term_length" => 12,
                'prices' => [
                    'min' => 199,
                    'max' => 599,
                    'points' => [
                        199,
                        339,
                        389,
                        599
                    ]
                ],
                'products' => ["24-MB03",
                    "24-MB04",
                    "240-LV04",
                    "MH01-XS-Black"
                ]
            ];

            //END HARDCORE

            $warranty = $this->productFactory->create();

            $connection = $this->connection->getConnection();
            $queryResult = $connection->fetchRow("SELECT MAX(`entity_id`) as LastID FROM `{$connection->getTableName('catalog_product_entity')}`");

            if (empty($queryResult)) {
                return null;
            }

            $id = $queryResult['LastID'] + 1;

            $warranty
                ->setPrice($this->deformatPrice($warrantyData['prices']['max']))
                ->setName($warrantyData['title'] . ' - ' . $id)
                ->setTypeId(WarrantyType::TYPE_CODE)
                ->setVisibility(1)
                ->setAttributeSetId(4)
                ->setSku($warrantyData['id'] . '-' . $id)
                ->setId($id)
                ->setWebsiteIds([$this->storeManager->getStore()->getWebsiteId()])
                ->setStockData([
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'min_sale_qty' => 1,
                    'max_sale_qty' => 1,
                    'is_in_stock' => 1,
                    'qty' => 10
                ])
                ->setStatus(1);

            $warranty->save();

            try {
                $customerCart = $this->cart->getCart();

                $params = array(
                    'form_key' => $this->formKey->getFormKey(),
                    'product' => $warranty->getId(),
                    'qty' => 1
                );

                $customerCart->addProduct($warranty, $params);

                $customerCart->save();
            } catch (LocalizedException $e) {

                $this->registry->register('isSecureArea', true);
                $warranty->delete();
                return null;
            }
        }
        return null;
    }

    private function deformatPrice(int $price): float
    {
        $price = (string)$price;

        $price = substr_replace($price, '.', strlen($price) - 2, 0);

        return (float)$price;
    }


}