<?php

namespace Extend\Warranty\Cron;

use Extend\Warranty\Model\Product\Type as WarrantyType;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Extend\Warranty\Model\WarrantyContract;
use Magento\Sales\Api\OrderRepositoryInterface;

class Contracts
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ResourceConnection
     */
    protected $connection;

    /**
     * @var WarrantyContract
     */
    protected $warrantyContract;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    public function __construct
    (
        ResourceConnection $connection,
        WarrantyContract $warrantyContract,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    )
    {
        $this->warrantyContract = $warrantyContract;
        $this->connection = $connection;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->logger->info('Contracts Cron');

        $connection = $this->connection->getConnection();
        $sql = "select order_id from sales_order_item where product_type = 'warranty' and contract_id is null;";

        try {
            $orders = $connection->fetchAll($sql);

            //Performance - Should we limit $orders size?
            $ordersToSend = array_slice($orders, 0, 10);

            if (!empty($orders)) {
                foreach($orders as $_order) {

                    $order = $this->orderRepository->get($_order["order_id"]);

                    foreach ($order->getAllItems() as $key => $item) {
                        /** @var \Magento\Sales\Model\Order\Item $item */
                        if ($item->getProductType() == WarrantyType::TYPE_CODE) {
                            if (!$flag) {
                                $flag = 1;
                            }
                            $warranties[$key] = $item;
                        }
                    }

                    if ($flag) {
                        $this->warrantyContract->createContract($order, $warranties);
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->logger->critical('Contracts cron sync error: ', $e->getMessage());
        }
    }


}