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

    private function prepareClient()
    {

        $accessKeys = $this->apiKey = $this->config->getValue('auth_mode') ?
            $this->keys->getLiveAccessKeys() :
            $this->keys->getSandboxAccessKeys();

        $uriWithStore = sprintf(self::URI, $accessKeys['storeID']);

        $uri = $this->urlBuilder->setUri($uriWithStore)->build();

        $this->client
            ->setUri($uri)
            ->setHeaders([
                'Accept' => ' application/json',
                'Content-Type' => ' application/json',
                'X-Extend-Access-Token' => $accessKeys['api_key']
            ]);

    }


    //Create
    public function create($products)
    {
        $this->createRequest($products);
    }

    //Update
    public function update($products)
    {
        foreach ($products as $product) {
            $this->updateRequest($product);
        }
    }

    private function createRequest($products)
    {
        try {
            $this->prepareClient();

            $uri = $this->client->getUri(true);

            //Batch flag
            $uri .= '?batch=1';
            $data = [];
            foreach ($products as $product) {
                $data[] = $this->productDataBuilder->build($product);
            }
            $this->client
                ->setUri($uri)
                ->setMethod(ZendClient::POST)
                ->setRawData(json_encode($data), 'application/json');

            $response = $this->client->request();

            if($this->processCreateResponse($response)){
                foreach ($products as $product) {
                    $product->setCustomAttribute('is_product_synced', true);
                    $product->save();
                }
            }
        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    private function updateRequest($product)
    {
        try {
            $buildedProduct = $this->productDataBuilder->build($product);
            $this->prepareClient();
            $uri = $this->client->getUri(true);
            $uri .= '/' . $product->getSku();
            $this->client->setMethod(ZendClient::PUT)
                ->setRawData(json_encode($buildedProduct), 'application/json');;
            $this->client->setUri($uri);
            $response = $this->client->request();

            if($this->processPutResponse($response)){
                $product->setCustomAttribute('is_product_synced', true);
                $product->save();
            }
        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    private function processCreateResponse(\Zend_Http_Response $response)
    {
        if ($response->isError()) {
            $res = json_decode($response->getBody(), true);
            $this->logger->error($res['message']);
            throw new \Exception($res['message']);

        } elseif ($response->getStatus() === 201) {
            $this->logger->info('Create product request successful');
            return true;
        }
        return false;
    }
    private function processPutResponse(\Zend_Http_Response $response)
    {
        if ($response->isError()) {
            $res = json_decode($response->getBody(), true);
            $this->logger->error($res['message']);
            throw new \Exception($res['message']);
        } else {
            $this->logger->info('Update product request successful');
            return true;
        }
    }

}