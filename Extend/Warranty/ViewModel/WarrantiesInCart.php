<?php


namespace Extend\Warranty\ViewModel;


use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Cart;
use Extend\Warranty\Helper\Api\Data;

class WarrantiesInCart implements ArgumentInterface
{
    protected $cart;

    protected $helper;

    public function __construct
    (
        Cart $cart,
        Data $helper
    )
    {
        $this->cart = $cart;
        $this->helper = $helper;
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

    public function isDisplayOffersEnabled()
    {
        return $this->helper->isDisplayOffersEnabled();
    }

}