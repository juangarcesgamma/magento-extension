<?php


namespace Extend\Warranty\Observer\Warranty;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Checkout\Helper\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class RemoveWarrantyFlag implements ObserverInterface
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct
    (
        Cart $cart,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->cart = $cart;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var $item \Magento\Quote\Model\Quote\Item */
        $item = $observer->getEvent()->getQuoteItem();

        if ($item->getProductType() === WarrantyType::TYPE_CODE) {
            $productAssociated = !empty($item->getOptionByCode('associated_product')) ?
                $item->getOptionByCode('associated_product')->getValue() : '';

            if (empty($productAssociated)) {
                return;
            }

            try {
                $product = $this->productRepository->get($productAssociated);
                $quote = $this->cart->getQuote();
                $itemAssociated = $quote->getItemByProduct($product);
                if ($itemAssociated) {
                    $itemAssociated->removeOption('hasWarranty');
                    $itemAssociated->saveItemOptions();
                }

            } catch (NoSuchEntityException $e) {
                return;
            }
        }
    }
}