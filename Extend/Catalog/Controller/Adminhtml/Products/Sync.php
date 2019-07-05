<?php

namespace Extend\Catalog\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Extend\Catalog\Model\ProductsCollection;
use Magento\Framework\Controller\ResultFactory;
use Extend\Catalog\Gateway\Request\ProductsRequest;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;


class Sync extends Action
{
    const LAST_SYNC_PATH = 'warranty/products/lastSync';
    protected $_publicActions = ['extend/products/sync'];
    const MAX_PRODUCTS_BATCH = 250;

    protected $productsCollection;
    protected $productsRequest;
    protected $resultFactory;
    protected $logger;

    protected $configWriter;
    protected $timezone;

    public function __construct(
        Action\Context $context,
        ProductsCollection $productsCollection,
        ProductsRequest $productsRequest,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        Writer $configWriter,
        TimezoneInterface $timezone
    )
    {
        $this->resultFactory = $resultFactory;
        $this->productsCollection = $productsCollection;
        $this->productsRequest = $productsRequest;
        $this->logger = $logger;
        $this->configWriter = $configWriter;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $storeProducts = $this->productsCollection->getProducts();

        try{

            $productsToCreate = $this->processProducts($storeProducts);

            $numOfBatches = ceil(sizeof($productsToCreate)/self::MAX_PRODUCTS_BATCH);

            for ($i = 0 ; $i < $numOfBatches ; $i++){
                if($i === ($numOfBatches-1)){
                    $productsInBatch = array_slice($productsToCreate,$i*self::MAX_PRODUCTS_BATCH);
                }else{
                    $productsInBatch = array_slice($productsToCreate,$i*self::MAX_PRODUCTS_BATCH,self::MAX_PRODUCTS_BATCH);
                }
                $this->productsRequest->create($productsInBatch);
            }
            $code = 200;
            $result = $this->prepareResult($result, $code);
            $this->logger->info('Products Successfully Synchronized');
            $date = $this->timezone->formatDate(null,1, true);
            $this->configWriter->save(self::LAST_SYNC_PATH,$date);
            return $result;
        }catch(Exception $e){
            $msg = __($e->getMessage());
            $code = 500;
            $result = $this->prepareResult($result, $code, ['msg' => $msg]);
            return $result;
        }
    }

    protected function processProducts($storeProducts){
        foreach ($storeProducts as $key => $product){
            $identifier = $product->getSku();
            $alreadyCreated = $this->productsRequest->get($identifier);
            if($alreadyCreated){
                unset($storeProducts[$key]);
            }
        }
        return $storeProducts;
    }

    protected function prepareResult(JsonResult $result, int $code, array $data = [])
    {
        $result->setHttpResponseCode($code);

        $result->setData($data);
    }
}