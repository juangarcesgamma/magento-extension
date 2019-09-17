<?php


namespace Extend\Warranty\Model\Api\Sync\Product;

use Extend\Warranty\Model\Api\Request\ProductDataBuilder;
use Psr\Log\LoggerInterface;

use Extend\Warranty\Api\ConnectorInterface;


class ProductsRequest
{
    protected $connector;
    protected $productDataBuilder;
    protected $logger;

    public function __construct
    (
        ConnectorInterface $connector,
        ProductDataBuilder $productDataBuilder,
        LoggerInterface $logger
    )
    {
        $this->connector = $connector;
        $this->productDataBuilder = $productDataBuilder;
        $this->logger = $logger;
    }

    //Create
    /**
     * @param $products
     * @throws \Exception
     */
    public function create($products)
    {
        $data = [];
        foreach ($products as $product) {
            $data[] = $this->productDataBuilder->build($product);
        }

        $endpoint = '/products?batch=1';
        $response  = $this->connector->call($endpoint, 'POST', $data);

        if ($response->isError()) {
            $res = json_decode($response->getBody(), true);
            $this->logger->error($res['message']);
            throw new \Exception($res['message']);

        } elseif ($response->getStatus() === 201) {
            $this->logger->info('Create product request successful');
        }
    }

    //Update

    /**
     * @param $products
     * @throws \Exception
     */
    public function update($products)
    {
        $endpoint = '/products/';
        foreach ($products as $product) {
            $data = $this->productDataBuilder->build($product);
            $response  = $this->connector->call($endpoint . $product->getSku(), 'PUT', $data);

            if ($response->isError()) {
                $res = json_decode($response->getBody(), true);
                $this->logger->error($res['message']);
                throw new \Exception($res['message']);
            } else {
                $this->logger->info('Update product request successful');
                $product->setCustomAttribute('is_product_synced', true);
                $product->save();
            }
        }

    }

}