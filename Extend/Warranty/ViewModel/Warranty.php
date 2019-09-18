<?php

namespace Extend\Warranty\ViewModel;

use Extend\Warranty\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Extend\Warranty\Model\Product\Type as WarrantyType;

class Warranty implements ArgumentInterface
{

    const ENABLE_EXTEND_PATH = 'warranty/enableExtend/enable';

    /**
     * @var ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Data
     */
    protected $helper;

    public function __construct
    (
        ProductInterfaceFactory $productFactory,
        ScopeConfigInterface $scopeConfig,
        Data $helper
    )
    {
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
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


        if ($this->isExtendEnabled()) {
            $warranties = [];

            foreach ($warrantiesData as $warrantyData) {
                $warranty = $this->productFactory->create();

                $warranty
                    ->setPrice($this->helper->removeFormatPrice($warrantyData['prices']['max']))
                    ->setName($warrantyData['title'])
                    ->setTypeId(WarrantyType::TYPE_CODE)
                    ->setSku($warrantyData['id'])
                    ->setCustomAttribute('warranty_length',$warrantyData['term_length']/12);

                $warranties[] = $warranty;
            }
            return $warranties;
        }
        return [];
    }

    public function isExtendEnabled()
    {
        return $this->scopeConfig->isSetFlag($this::ENABLE_EXTEND_PATH);
    }
}