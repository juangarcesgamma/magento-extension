<?php


namespace Extend\Catalog\Model;

use Extend\Catalog\Api\ConnectionInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Extend\Catalog\Helper\Data;

class Connection implements ConnectionInterface
{

    protected $baseUrl;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var CurlFactory
     */
    protected $httpClient;


    protected $extendHelper;


    CONST PRODUCTS = '/products';

    public function __construct(
        Json $jsonSerializer,
        CurlFactory $httpClient,
        Data $extendHelper,
        string $baseUrl = 'https://developers.helloextend.com/api.helloextend.com/stores/'
    )
    {
        $this->baseUrl = $baseUrl;
        $this->jsonSerializer = $jsonSerializer;
        $this->httpClient = $httpClient;
        $this->extendHelper = $extendHelper;
    }

    public function createProduct($product): void
    {
        $apiKey = $this->extendHelper->getExtendApiKey();
        $extendStoreID = $this->extendHelper->getExtendStoreID();

        $fullPath = $this->baseUrl . $extendStoreID . self::PRODUCTS;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullPath);
        $headers = ['Accept: application/json', 'Content-Type: application/json', 'X-Extend-Access-Token: '.$apiKey];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = [
          'title' => $product->getName(),
          'price' => $product->getPrice(),
          'referenceId' => $product->getSku()
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }
}

