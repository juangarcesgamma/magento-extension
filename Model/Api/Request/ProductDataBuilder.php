<?php

namespace Extend\Warranty\Model\Api\Request;

use Extend\Warranty\Helper\Data;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ProductDataBuilder
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Configurable
     */
    protected $configurableType;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $helper;

    public function __construct
    (
        Configurable $configurableType,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        Data $helper
    )
    {
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->categoryRepository = $categoryRepository;
        $this->helper = $helper;
    }

    /**
     * @param $productSubject
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */

    public function build($productSubject): array
    {
        $description = !empty($productSubject->getShortDescription())? (string)$productSubject->getShortDescription() : 'No description';
        $imgUrl = !empty($productSubject->getImage())? (string)$productSubject->getImage() : 'No image url';

        $data = [
            'title' => (string)$productSubject->getName(),
            'description' => $description,
            'price' => $this->helper->formatPrice($productSubject->getFinalPrice()),
            'referenceId' => (string)$productSubject->getSku(),
            'imageUrl' => $imgUrl,
            'category' => $this->getCategories($productSubject),
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
    private function getCategories($productSubject): string
    {
        $categoryIds = $productSubject->getCategoryIds();

        sort($categoryIds);

        $names = [];
        /**
         * @var \Magento\Catalog\Model\Category $category
         */
        foreach ($categoryIds as $key => $categoryId) {
            $category = $this->categoryRepository->get($categoryId);
            if (!$category->hasChildren()) {
                if (in_array($category->getEntityId(), $categoryIds)) {
                    $names[] = $category->getName();
                }
            } else {
                $cat = $this->checkChildren($category, $category->getName(), $categoryIds);
                if ($cat != null) {
                    $names[] = $cat;
                }
            }
        }

        return implode(",", $names);
    }

    /**
     * @param CategoryInterface $category
     * @param string $catName
     * @param array $ids
     * @return string|null
     */
    private function checkChildren(CategoryInterface $category, string $catName, array &$ids): ?string
    {
        $names = [];
        $children = $category->getChildrenCategories();
        foreach ($children as $child) {
            if (in_array($child->getEntityId(), $ids)) {
                $new = $catName . '/' . $child->getName();
                $ids[array_search($child->getEntityId(), $ids)] = '';
                if (!$child->hasChildren()) {
                    $names[] = $new;
                } else {
                    $names[] = $this->checkChildren($child, $new, $ids);
                }
            }
        }
        return !empty($names) ? implode(",", $names) : null;
    }
}