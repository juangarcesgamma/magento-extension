<?php

namespace Extend\Catalog\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Extend\Catalog\Model\ProductsCollection;
use Magento\Framework\Controller\ResultFactory;
use Extend\Catalog\Gateway\Request\ProductsRequest;

class Sync extends Action
{
    protected $_publicActions = ['extend/products/sync'];
    const MAX_PRODUCTS_BATCH = 250;

    protected $productsCollection;
    protected $productsRequest;
    protected $resultFactory;

    public function __construct(
        Action\Context $context,
        ProductsCollection $productsCollection,
        ProductsRequest $productsRequest,
        ResultFactory $resultFactory
    )
    {
        $this->resultFactory = $resultFactory;
        $this->productsCollection = $productsCollection;
        $this->productsRequest = $productsRequest;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $website = $this->_request->getParam('website');

        $scopeId = 0;

        $data = [];

        if(!empty($website)) {
            $scopeId = $website;
        }

        //CHECK WHICH PRODUCTS ARE ALREADY IN THE API
        $storeProducts = $this->productsCollection->getProducts();

        try{
            $apiIds = $this->productsRequest->get();
            $products = $this->processProducts($apiIds, $storeProducts);
        }catch(Exception $e){
            $msg = __($e->getMessage());
            $code = 500;
            $result = $this->prepareResult($result, $code, ['msg' => $msg]);
            return $result;
        }

        $productsToDelete = $products['delete'];

        foreach ($productsToDelete as $identifier){
            $this->productsRequest->delete($identifier);
        }

        $productsToCreate = $products['create'];

        $numOfBatches = ceil(sizeof($productsToCreate)/self::MAX_PRODUCTS_BATCH);

        try{
            for ($i = 0 ; $i < $numOfBatches ; $i++){
                if($i === ($numOfBatches-1)){
                    $productsInBatch = array_slice($productsToCreate,$i*self::MAX_PRODUCTS_BATCH);
                }else{
                    $productsInBatch = array_slice($productsToCreate,$i*self::MAX_PRODUCTS_BATCH,self::MAX_PRODUCTS_BATCH);
                }
                $this->productsRequest->create($productsInBatch);
            }

            $msg = __('Products Successfully Synchronized');
            $code = 200;
            $result = $this->prepareResult($result, $code, ['msg' => $msg]);
            return $result;
        }catch(Exception $e){
            $msg = __($e->getMessage());
            $code = 500;
            $result = $this->prepareResult($result, $code, ['msg' => $msg]);
            return $result;
        }
    }

    protected function processProducts($apiIDS, $storeProducts){
        $productsToUpdate = [];
        foreach ($storeProducts as $key => $product){
            $sku = $product->getSku();
            if(isset($apiIDS[$sku])){
                $productsToUpdate[] = $product;
                unset($storeProducts[$key]);
                unset($apiIDS[$sku]);
            }
        }
        return [
            'create' => $storeProducts,
            'update' => $productsToUpdate,
            'delete' => $apiIDS
        ];
    }

    protected function prepareResult(JsonResult $result, int $code, array $data = [])
    {
        $result->setHttpResponseCode($code);

        $result->setData($data);
    }
}