<?php

namespace Extend\Warranty\Plugin\Controller\Cart;

use Magento\Checkout\Controller\Cart\Add as SuperAdd;
use \Magento\Framework\App\Request\Http;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Message\ManagerInterface;

class Add
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    public function __construct
    (
        Http $request,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ManagerInterface $messageManager
    )
    {
        $this->request = $request;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->messageManager = $messageManager;
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
                'products' => [
                    "24-MB03",
                    "24-MB04",
                    "240-LV04",
                    "MH01-XS-Black"
                ]
            ];
            //END HARDCORE

            $warrantyData['products'] = $product->getId();
            $warrantyData['custom_price'] = $warrantyData['prices']['max'];

            $this->searchCriteriaBuilder
                ->setPageSize(1)->addFilter('type_id', WarrantyType::TYPE_CODE);

            $searchCriteria = $this->searchCriteriaBuilder->create();

            $searchResults = $this->productRepository->getList($searchCriteria);

            $results = $searchResults->getItems();
            $warranty = reset($results);

            $cart = $this->cart->getCart();

            try {
                $cart->addProduct($warranty->getId(), $warrantyData);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage('Error while adding warranty product');
            }

        }
        return null;
    }
}