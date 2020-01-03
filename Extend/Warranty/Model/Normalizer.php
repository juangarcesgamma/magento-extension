<?php


namespace Extend\Warranty\Model;

use Magento\Checkout\Model\Cart;

class Normalizer
{
    public function normalize(Cart $cart, $data)
    {
        $warranties = [];
        $products = [];
        foreach ($data->getData() as $itemId => $itemInfo) {
            $item = $cart->getQuote()->getItemById($itemId);

            if ($item->getProductType() === 'warranty') {
                $prod = $item->getOptionByCode('associated_product')->getValue();
                $warranties[$prod][] = $item;
            } else {
                $products[] = $item;
            }
        }

        foreach ($products as $item) {
            $sku = $item->getSku();

            if (isset($warranties[$sku])) {
                $qty = $item->getQty();
                $warrantiesQty = array_reduce($warranties[$sku], function ($carry, $item) {
                    return $carry += $item->getQty();
                }, 0);

                if ($qty > $warrantiesQty) {
                    $this->updateMax($warranties[$sku], $qty - $warrantiesQty);
                } else if ($qty < $warrantiesQty) {
                    //Update-remove min warranties
                    $this->updateMin($warranties[$sku], $warrantiesQty - $qty, $cart);
                }
            }
        }
    }

    private function updateMax($warranties, $qty)
    {
        $max = null;
        foreach ($warranties as $item) {
            if (!$max || $item->getPrice() > $max->getPrice()) {
                $max = $item;
            }
        }
        $max->setQty($max->getQty() + $qty);
    }

    private function updateMin($warranties, $qty, Cart $cart)
    {
        while ($qty > 0) {
            $min = null;
            $pos = null;
            foreach ($warranties as $index => $item) {
                if (!$min || $item->getPrice() < $min->getPrice()) {
                    $min = $item;
                    $pos = $index;
                }
            }

            $tempQty = $min->getQty() - $qty;

            if ($tempQty > 0) {
                $min->setQty($tempQty);
                $qty = 0;
            } else if ((int)$tempQty === 0) {
                $cart->removeItem($min->getItemId());
                $qty = 0;
            } else {
                $cart->removeItem($min->getItemId());
                unset($warranties[$pos]);
                $qty = abs($tempQty);
            }
        }
    }
}