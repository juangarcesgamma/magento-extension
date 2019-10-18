<?php

namespace Extend\Warranty\Controller\Cart;

use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Checkout\Controller\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

class Add extends Cart implements HttpPostActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    protected function initWarranty()
    {
        $this->searchCriteriaBuilder
            ->setPageSize(1)->addFilter('type_id', WarrantyType::TYPE_CODE);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResults = $this->productRepository->getList($searchCriteria);

        $results = $searchResults->getItems();
        return reset($results);
    }

    protected function initProduct($info)
    {
        if (isset($info['product'])) {
            try {
                return $this->productRepository->get($info['product']);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    public function execute()
    {
        $warrantyData = $this->getRequest()->getPost('warranty');

        $product = $this->initProduct($warrantyData);

        try {
            $warranty = $this->initWarranty();
            if (!$warranty) {
                return $this->goBack();
            }

            $this->cart->addProduct($warranty, $warrantyData);

            $product = $this->cart->getQuote()->getItemByProduct($product);
            if ($product) {
                $product->addOption([
                    'product' => $product->getProduct(),
                    'code' => 'hasWarranty',
                    'value' => serialize(true)
                ]);
                $product->saveItemOptions();
            }

            $this->cart->save();

            $message = __(
                'You added %1 to your shopping cart.',
                $warranty->getName()
            );
            $this->messageManager->addSuccessMessage($message);
            return $this->goBack(null, $warranty);

        } catch (LocalizedException | \Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this product protection to your shopping cart right now.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }
    }

    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}