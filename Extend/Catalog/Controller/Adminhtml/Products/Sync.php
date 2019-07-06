<?php

namespace Extend\Catalog\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Extend\Catalog\Model\SyncProcess;
use Psr\Log\LoggerInterface;
use Extend\Catalog\Model\ProductsCollection;

class Sync extends Action
{
    protected $_publicActions = ['extend/products/sync'];

    protected $resultFactory;
    protected $logger;
    protected $syncProcess;
    protected $productsCollection;

    public function __construct(
        Action\Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        SyncProcess $syncProcess,
        ProductsCollection $productsCollection
    )
    {
        $this->productsCollection = $productsCollection;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->syncProcess = $syncProcess;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $storeProducts = $this->productsCollection->getProducts();

        try{
            $this->syncProcess->sync($storeProducts);
            $code = 200;
            $result = $this->prepareResult($result, $code);
            $this->logger->info('Products Successfully Synchronized');
            return $result;
        }catch(Exception $e){
            $msg = __($e->getMessage());
            $code = 500;
            $result = $this->prepareResult($result, $code, ['msg' => $msg]);
            return $result;
        }
    }

    protected function prepareResult(JsonResult $result, int $code, array $data = [])
    {
        $result->setHttpResponseCode($code);
        $result->setData($data);
    }
}