<?php


namespace Extend\Warranty\Observer\Warranty;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Exception\NoSuchEntityException;

class RemoveWarrantyFlag implements ObserverInterface
{
    /**
     * @var Cart
     */
    protected $cart;

    public function __construct
    (
        Cart $cart
    )
    {
        $this->cart = $cart;
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
                $quote = $this->cart->getQuote();
                $items = $quote->getAllItems();
                $itemAssociated = null;
                foreach ($items as $item) {
                    if ($item->getSku() == $productAssociated) {
                        $itemAssociated = $item;
                    }
                }
                if ($itemAssociated) {
                    $itemAssociated = $itemAssociated->getParentItem() ?? $itemAssociated;
                    $itemAssociated->removeOption('hasWarranty');
                    $itemAssociated->saveItemOptions();
                }

            } catch (NoSuchEntityException $e) {
                return;
            }
        }
    }
}