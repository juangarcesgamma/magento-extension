<?php


namespace Extend\Warranty\Model\Api\Sync\Product;

use Extend\Warranty\Model\Api\Request\ProductDataBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

use Extend\Warranty\Api\ConnectorInterface;


class ProductsRequest
{
    const ENDPOINT_URI = 'products';
    /**
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * @var ProductDataBuilder
     */
    protected $productDataBuilder;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    protected $productRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct
    (
        ConnectorInterface $connector,
        ProductDataBuilder $productDataBuilder,
        Json $jsonSerializer,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    )
    {
        $this->connector = $connector;
        $this->productDataBuilder = $productDataBuilder;
        $this->jsonSerializer = $jsonSerializer;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * @param $products
     * @throws \Exception
     */
    public function create($products): void
    {
        $data = [];

        foreach ($products as $product) {
            $data[] = $this->productDataBuilder->build($product);
        }

        $response = $this->connector->call(
            self::ENDPOINT_URI . '?batch=1&upsert=1',
            \Zend_Http_Client::POST,
            $data
        );

        if ($response->getStatus() === 201) {
            $this->logger->info('Create product request successful');
            return;
        }

        $res = $this->jsonSerializer->unserialize($response->getBody());
        $this->logger->error($res['message']);
        throw new \Exception($res['message']);
    }


    /**
     * @param $products
     * @throws \Exception
     */
    public function update($products): void
    {
        foreach ($products as $product) {
            $data = $this->productDataBuilder->build($product);
            $response = $this->connector->call(
                self::ENDPOINT_URI . "/{$product->getSku()}",
                \Zend_Http_Client::PUT,
                $data
            );

            if ($response->isError()) {
                $res = $this->jsonSerializer->unserialize($response->getBody());
                $this->logger->error($res['message']);

                throw new \Exception($res['message']);
            } else {
                $this->logger->info('Update product request successful');
                $product->setCustomAttribute('is_product_synced', true);

                $this->productRepository->save($product);
            }
        }

    }

}