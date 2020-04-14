<?php


namespace Extend\Warranty\ViewModel;


use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session;
use Extend\Warranty\Helper\Api\Data;

class WarrantiesInCart implements ArgumentInterface
{
    protected $checkoutSession;

    protected $helper;

    public function __construct
    (
        Session $checkoutSession,
        Data $helper
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
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

    public function isDisplayOffersEnabled()
    {
        return $this->helper->isDisplayOffersEnabled();
    }

}