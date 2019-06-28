<?php


namespace Extend\Warranty\Model;


use Extend\Warranty\Api\ConnectionInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Connection implements ConnectionInterface
{

    protected $baseUrlSandbox = 'https://api-stage.helloextend.com/stores';

    protected $baseUrlLive = 'https://api.helloextend.com/stores';
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

    public function testConnection($storeId, $apiKey, $isLive): string
    {
        if($isLive === "1" && !is_null($isLive)){
            $baseUrl = $this->baseUrlLive;
        } else if($isLive === "0" && !is_null($isLive)){
            $baseUrl = $this->baseUrlSandbox;
        }
        $requestPath = "{$baseUrl}/{$storeId}/products";

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