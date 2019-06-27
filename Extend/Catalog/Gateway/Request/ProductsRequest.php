<?php


namespace Extend\Catalog\Gateway\Request;

use Extend\Catalog\Model\Keys;
use Magento\Framework\HTTP\ZendClient;
use Extend\Catalog\Api\Data\UrlBuilderInterface;
use Extend\Catalog\Helper\Data as Config;
use Extend\Catalog\Gateway\Request\ProductDataBuilder;
use Psr\Log\LoggerInterface;


class ProductsRequest
{
    const URI = '/stores/%s/products';

    protected $keys;
    protected $urlBuilder;
    protected $client;
    protected $config;
    protected $productDataBuilder;
    protected $logger;

    public function __construct(
        Keys $keys,
        UrlBuilderInterface $urlBuilder,
        ZendClient $client,
        Config $config,
        ProductDataBuilder $productDataBuilder,
        LoggerInterface $logger
    )
    {
        $this->keys = $keys;
        $this->urlBuilder = $urlBuilder;
        $this->client = $client;
        $this->config = $config;
        $this->productDataBuilder = $productDataBuilder;
        $this->logger = $logger;
    }


    public function create($products){
        $this->createRequest($products);
    }

    private function prepareClient($products){

        $accessKeys = $this->apiKey = $this->config->getValue('auth_mode') ?
            $this->keys->getLiveAccessKeys() :
            $this->keys->getSandboxAccessKeys();

        $uriWithStore = sprintf(self::URI, $accessKeys['storeID']);

        $uri = $this->urlBuilder->setUri($uriWithStore)->build();

        //Batch flag
        $uri .= '?batch=1';

        $data = [];
        foreach ($products as $product){
            $data[] = $this->productDataBuilder->build($product);
        }

        $this->client
            ->setUri($uri)
            ->setMethod(ZendClient::POST)
            ->setHeaders([
                'Accept' =>' application/json',
                'Content-Type' =>' application/json',
                'X-Extend-Access-Token' => $accessKeys['api_key']
            ])
            ->setRawData(json_encode($data),'application/json');
    }

    private function createRequest($products){
        try{
            $this->prepareClient($products);
            $response = $this->client->request();
            $this->processResponse($response);
        }catch (\Zend_Http_Client_Exception $e){
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }


    private function processResponse(\Zend_Http_Response $response){

        if($response->isError()){
            $this->logger->error($response->getBody(). 'Status code: ' . $response->getStatus());
        }elseif ($response->getStatus() === 201){
            $this->logger->info($response->getBody());
        }
    }
}