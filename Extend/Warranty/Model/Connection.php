<?php


namespace Extend\Warranty\Model;


use Extend\Warranty\Api\ConnectionInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Connection implements ConnectionInterface
{

    protected $baseUrl = 'https://api-stage.helloextend.com/stores';

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var CurlFactory
     */
    protected $httpClient;

    public function __construct(
        Json $jsonSerializer,
        CurlFactory $httpClient
    )
    {
        $this->jsonSerializer = $jsonSerializer;
        $this->httpClient = $httpClient;
    }

    public function testConnection($storeId, $apiKey): string
    {
        $requestPath = "{$this->baseUrl}/{$storeId}/products";

        $client = $this->httpClient->create();
        $headers = [
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "X-Extend-Access-Token" => $apiKey
            ];
        $client->setHeaders($headers);

        $client->get($requestPath);

        return strval($client->getStatus());

    }
}