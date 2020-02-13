<?php


namespace Extend\Warranty\ViewModel;


use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Cart;

class WarrantiesInCart implements ArgumentInterface
{
    protected $cart;

    public function __construct
    (
        Cart $cart
    )
    {
        $this->cart = $cart;
    }

    public function hasWarranty($sku)
    {
        foreach ($this->cart->getItems() as $item) {
            if ($item->getProductType() === 'warranty') {
                if ($item->getOptionByCode('associated_product')->getValue() === $sku) {
                    return true;
                }

            }
        }
        return false;
    }

}