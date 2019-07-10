<?php

namespace Extend\Catalog\Gateway\Request;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class ProductDataBuilder
{
    protected $categoryRepository;

    public function __construct
    (
        CategoryRepositoryInterface $categoryRepository
    )
    {
        $this->categoryRepository = $categoryRepository;
    }

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
            'category' =>  $this->getCategories($productSubject),
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
     * @param Product $productSubject
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategories($productSubject){
        $categoryIds = $productSubject->getCategoryIds();
        sort($categoryIds);
        $names = [];
        /**
         * @var \Magento\Catalog\Model\Category $category
         */
        foreach ($categoryIds as $key => $categoryID){
            $category = $this->categoryRepository->get($categoryID);
            if(!$category->hasChildren()){
                if(in_array($category->getEntityId(),$categoryIds)){
                    $names[] = $category->getName();
                }
            }else{
                $cat = $this->checkChildrens($category,$category->getName(),$categoryIds);
                if($cat != null){
                    $names[] = $cat;
                }
            }
        }

        return implode(",",$names);
    }

    /**
     * @var \Magento\Catalog\Model\Category $category
     * @return string|null
     */
    private function checkChildrens($category,$string,&$ids){
        $names= [];
        $childrens = $category->getChildrenCategories();
        foreach ($childrens as $children){
            if(in_array($children->getEntityId(),$ids)){
                $new=$string.'/'.$children->getName();
                $ids[array_search($children->getEntityId(),$ids)] = '';
                if(!$children->hasChildren()){
                    $names[] = $new;
                }else{
                    $names[] = $this->checkChildrens($children,$new,$ids);
                }
            }
        }
        return !empty($names) ? implode(",",$names) : null;
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