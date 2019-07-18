<?php

namespace Extend\Catalog\ViewModel;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Extend\Warranty\Model\Product\Type as WarrantyType;

class Warranty implements ArgumentInterface
{
    protected $productFactory;

    public function __construct
    (
        ProductInterfaceFactory $productFactory
    )
    {
        $this->productFactory = $productFactory;
    }

    public function getWarranties(string $sku): array
    {
        $warrantiesData = [];

        //DUMMY PRODUCTS WAITING FOR EXTEND TO PROVIDE API CALL
        $warrantiesData[] = [
            'id' => "10001-auto-part-base-replace-1y",
            'title' => "Extend Protection Plan - Auto Part",
            'imageUrl' => "https://extend-js-sdk.s3.amazonaws.com/extend_icon.png",
            "term_length" => 12,
            'prices' => [
                'min' => 549,
                'max' => 549,
                'points' => [
                    549
                ]
            ],
            'products' => ["24-MB05"]
        ];

        $warrantiesData[] = [
            'id' => "10001-auto-part-base-replace-2y",
            'title' => "Extend Protection Plan - Auto Part",
            'imageUrl' => "https://extend-js-sdk.s3.amazonaws.com/extend_icon.png",
            "term_length" => 24,
            'prices' => [
                'min' => 1099,
                'max' => 1099,
                'points' => [
                    1099
                ]
            ],
            'products' => ["24-MB05"]
        ];

        $warrantiesData[] = [
            'id' => "10001-auto-part-base-replace-3y",
            'title' => "Extend Protection Plan - Auto Part",
            'imageUrl' => "https://extend-js-sdk.s3.amazonaws.com/extend_icon.png",
            "term_length" => 48,
            'prices' => [
                'min' => 2599,
                'max' => 2599,
                'points' => [
                    2599
                ]
            ],
            'products' => ["24-MB05"]
        ];
        //END OF DUMMY PRODUCTS

        $warranties = [];

        foreach ($warrantiesData as $warrantyData) {
            $warranty = $this->productFactory->create();

            $warranty
                ->setPrice($this->deformatPrice($warrantyData['prices']['max']))
                ->setName($warrantyData['title'])
                ->setTypeId(WarrantyType::TYPE_CODE)
                ->setSku($warrantyData['id'])
                ->setCustomAttribute('warranty_length',$warrantyData['term_length']/12);

            $warranties[] = $warranty;
        }
        return $warranties;
    }

    private function deformatPrice(int $price): float
    {
        $price = (string)$price;

        $price = substr_replace($price, '.', strlen($price) - 2, 0);

        return (float)$price;
    }
}