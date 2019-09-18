<?php

namespace Extend\Warranty\Plugin\Controller\Onepage;

use Magento\Checkout\Controller\Onepage\Success as SuperSuccess;
use Magento\Framework\Exception\StateException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Extend\Warranty\Model\Product\Type as WarrantyType;
use Extend\Warranty\Model\Api\Sync\Contract\ContractsRequest;
use Magento\Checkout\Model\Session;
use Extend\Warranty\Model\Api\Request\ContractBuilder;
use Magento\Framework\Registry;

class Success
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ContractsRequest
     */
    protected $contractsRequest;

    /**
     * @var ContractBuilder
     */
    protected $contractBuilder;

    /**
     * @var Registry
     */
    protected $registry;

    public function __construct
    (
        Session $session,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        ContractsRequest $contractsRequest,
        ContractBuilder $contractBuilder,
        Registry $registry
    )
    {
        $this->session = $session;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->contractsRequest = $contractsRequest;
        $this->contractBuilder = $contractBuilder;
        $this->registry = $registry;
    }

    function afterExecute(SuperSuccess $subject, $resultPage)
    {
        $orderId = $this->session->getLastOrderId();

        $order = $this->orderRepository->get($orderId);

        $warranties = [];

        $flag = 0;

        foreach ($order->getItems() as $item) {
            if ($item->getProductType() == WarrantyType::TYPE_CODE) {
                if (!$flag) {
                    $flag = 1;
                }
                $warranties[$item->getProductId()] = $this->productRepository->getById($item->getProductId());
            }
        }
        if ($flag) {
            $contracts = $this->contractBuilder->prepareInfo($order, $warranties);
            $this->contractsRequest->prepareClient();
            foreach ($contracts as $orderId => $order) {
                $this->contractsRequest->create($order);
            }
            $this->registry->register('isSecureArea', true);

            foreach ($warranties as $warranty) {
                try {
                    $this->productRepository->delete($warranty);
                } catch (StateException $exception) {
                    continue;
                }
            }
        }

        return $resultPage;
    }
}