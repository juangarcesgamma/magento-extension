<?php


namespace Extend\Warranty\ViewModel;


use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session;

class WarrantiesInCart implements ArgumentInterface
{
    protected $checkoutSession;

    public function __construct
    (
        Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function hasWarranty($sku)
    {
        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
            if ($item->getProductType() === 'warranty') {
                if ($item->getOptionByCode('associated_product')->getValue() === $sku) {
                    return true;
                }

            }
        }
        return false;
    }

}