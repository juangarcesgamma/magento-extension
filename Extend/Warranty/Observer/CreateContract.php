<?php

use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Extend\Warranty\Model\WarrantyContract;

class CreateContract implements ObserverInterface
{
    protected $productRepository;
    protected $warrantyContract;

    public function __construct
    (
        ProductRepositoryInterface $productRepository,
        WarrantyContract $warrantyContract
    )
    {
        $this->productRepository = $productRepository;
        $this->warrantyContract = $warrantyContract;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        /** @var OrderInterface $order */
        $order = $invoice->getOrder();
        $warranties = [];

        $flag = 0;

        /** @var OrderItemInterface $item */
        foreach ($order->getItems() as $item) {

            if ($item->getProductType() == WarrantyType::TYPE_CODE) {
                if (!$flag) {
                    $flag = 1;
                }

                try {
                    $warranties[$item->getProductId()] = $this->productRepository->getById($item->getProductId());
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }
        }

        if ($flag) {
            foreach ($warranties as $warranty) {
                $this->warrantyContract->createContract($order, $warranty);
            }
        }
    }
}