<?php


namespace Extend\Catalog\Model;

use Extend\Catalog\Api\ConnectionInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Extend\Catalog\Helper\Data;

class Connection implements ConnectionInterface
{

    protected $baseUrl;

    protected $sandBoxBaseUrl;

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
        string $sandBoxBaseUrl = 'https://api-stage.helloextend.com/stores/',
        string $baseUrl = 'https://developers.helloextend.com/api.helloextend.com/stores/'
    )
    {
        $this->baseUrl = $baseUrl;
        $this->sandBoxBaseUrl = $sandBoxBaseUrl;
        $this->jsonSerializer = $jsonSerializer;
        $this->httpClient = $httpClient;
        $this->extendHelper = $extendHelper;
    }

    public function createProducts($products): array
    {
        $apiKey = $this->extendHelper->getExtendApiKey();
        $extendStoreID = $this->extendHelper->getExtendStoreID();
        $mode = $this->extendHelper->getExtendMode();

        if($mode){
            $fullPath = $this->baseUrl . $extendStoreID . self::PRODUCTS;
        }else{
            //SANDBOX MODE
            $fullPath = $this->sandBoxBaseUrl . $extendStoreID . self::PRODUCTS;
        }


        //Set 1 if the api is going to receive an group of products
        $fullPath .= '?batch=1';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullPath);
        $headers = ['Accept: application/json', 'Content-Type: application/json', 'X-Extend-Access-Token: '.$apiKey];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $productsToSend = [];

        foreach ($products as $product){
            $data = [
                'title' => $product->getName(),
                'price' => $this->formatPrice($product->getPrice()),
                'referenceId' => $product->getSku()
            ];

            $productsToSend[] = $data;
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($productsToSend));

        $result = curl_exec($ch);

        curl_close($ch);

        return json_decode($result, true);
    }


    private function formatPrice($price){

        $floatPrice = floatval($price);
        $formatedPrice = number_format($floatPrice, 2,'','');

        return intval($formatedPrice);

    }
}

