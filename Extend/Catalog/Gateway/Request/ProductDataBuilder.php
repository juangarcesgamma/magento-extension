<?php

namespace Extend\Catalog\Gateway\Request;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ProductDataBuilder
{
    const NOT_VISIBLE = 1;
    protected $categoryRepository;
    protected $configurableType;
    protected $productRepository;

    public function __construct
    (
        Configurable $configurableType,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param Product $productSubject
     * @return array
     */

    public function build($productSubject)
    {

        $data = [
            'title' => (string)$productSubject->getName(),
            'description' => (string)$productSubject->getShortDescription(),
            'price' => $this->formatPrice($productSubject->getFinalPrice()),
            'referenceId' => (string)$productSubject->getSku(),
            'category' => $this->getCategories($productSubject),
            'imageUrl' => (string)$productSubject->getImage(),
            'identifiers' => [
                'sku' => (string)$productSubject->getSku(),
                'type' => (string)$productSubject->getTypeId()
            ]
        ];

        $parentId = $this->configurableType->getParentIdsByChild($productSubject->getEntityId());
        $parentId = reset($parentId);
        if (!empty($parentId)) {
            $data['identifiers']['parentSku'] = $this->productRepository->getById($parentId)->getSku();

            $data['identifiers']['type'] = 'configurableChild';
        }
        return $data;
    }

    /**
     * @param Product $productSubject
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategories($productSubject)
    {
        $categoryIds = $productSubject->getCategoryIds();
        sort($categoryIds);
        $names = [];
        /**
         * @var \Magento\Catalog\Model\Category $category
         */
        foreach ($categoryIds as $key => $categoryID) {
            $category = $this->categoryRepository->get($categoryID);
            if (!$category->hasChildren()) {
                if (in_array($category->getEntityId(), $categoryIds)) {
                    $names[] = $category->getName();
                }
            } else {
                $cat = $this->checkChildrens($category, $category->getName(), $categoryIds);
                if ($cat != null) {
                    $names[] = $cat;
                }
            }
        }

        return implode(",", $names);
    }

    /**
     * @return string|null
     * @var \Magento\Catalog\Model\Category $category
     */
    private function checkChildrens($category, $string, &$ids)
    {
        $names = [];
        $childrens = $category->getChildrenCategories();
        foreach ($childrens as $children) {
            if (in_array($children->getEntityId(), $ids)) {
                $new = $string . '/' . $children->getName();
                $ids[array_search($children->getEntityId(), $ids)] = '';
                if (!$children->hasChildren()) {
                    $names[] = $new;
                } else {
                    $names[] = $this->checkChildrens($children, $new, $ids);
                }
            }
        }
        return !empty($names) ? implode(",", $names) : null;
    }

    /**
     * @param $price
     * @return int
     */
    private function formatPrice($price)
    {
        if(!empty($price)){
            $floatPrice = floatval($price);

            $formattedPrice = number_format($floatPrice, 2, '', '');

            return intval($formattedPrice);
        }
        return 0;
    }

}