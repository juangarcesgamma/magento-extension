<?php

namespace Extend\Catalog\Gateway\Request;

use Magento\Catalog\Model\Product;

class ProductDataBuilder
{
    /**
     * @param Product $productSubject
     * @return array
     */

    public function build($productSubject){
        $data = [
            'title' => (string)$productSubject->getName(),
            'description' => (string)$productSubject->getShortDescription(),
            'price' => $this->formatPrice($productSubject->getPrice()),
            'referenceId' => (string)$productSubject->getSku(),
            'brand' => '',
            'category' =>  '',
            'imageUrl' => (string)$productSubject->getImage(),
            'identifiers' => [
                'sku' => (string)$productSubject->getSku()
            ]
        ];

        return $data;
    }

    /**
     * @param array
     * @return array
     */

    public function getIds($products){
        $ids = [];
        foreach ($products as $product){
            $referenceID = $product['referenceId'];
            $ids[$referenceID] = $referenceID;
        }
        return $ids;
    }

    /**
     * @param float $price
     * @return int
     */

    private function formatPrice(float $price){

        $floatPrice = floatval($price);

        $formattedPrice = number_format($floatPrice, 2,'','');

        return intval($formattedPrice);

    }

}