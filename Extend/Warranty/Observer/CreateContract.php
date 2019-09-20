<?php

namespace Extend\Warranty\Observer;

use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Extend\Warranty\Model\WarrantyContract;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class CreateContract implements ObserverInterface
{
    protected $productRepository;
    protected $warrantyContract;
    protected $quoteRepository;
    protected $logger;

    public function __construct
    (
        ProductRepositoryInterface $productRepository,
        WarrantyContract $warrantyContract,
        CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger
    )
    {
        $this->productRepository = $productRepository;
        $this->warrantyContract = $warrantyContract;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
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

        try {
            $quote = $this->quoteRepository->get($order->getQuoteId());
        } catch (NoSuchEntityException $exception) {
            $this->logger->error("No quote found");
            return;
        }

        $warranties = [];

        $flag = 0;

        foreach ($quote->getAllItems() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if ($item->getProductType() == WarrantyType::TYPE_CODE) {
                if (!$flag) {
                    $flag = 1;
                }
                $warranties[] = $item;
            }
        }

        if ($flag) {
            $this->warrantyContract->createContract($order, $warranties);
        }
    }
}