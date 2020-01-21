<?php

namespace Extend\Warranty\Observer\Warranty;

use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Message\ManagerInterface;

class AddToCart implements ObserverInterface
{
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
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ManagerInterface $messageManager
    )
    {
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $cart = $this->cart->getCart();
        $qty = $request->getPost('qty');
        $warrantyData = $request->getPost('warranty');

        if (!empty($warrantyData)) {

            $this->searchCriteriaBuilder
                ->setPageSize(1)->addFilter('type_id', WarrantyType::TYPE_CODE);

            $searchCriteria = $this->searchCriteriaBuilder->create();

            $searchResults = $this->productRepository->getList($searchCriteria);

            $results = $searchResults->getItems();
            $warranty = reset($results);

            $warrantyData['qty'] = $qty;

            try {
                $cart->addProduct($warranty->getId(), $warrantyData);

                $cart->getQuote()->removeAllAddresses();
                $cart->getQuote()->setTotalsCollectedFlag(false);
                $cart->save();

            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage('Error while adding warranty product');
            }

        }
    }
}