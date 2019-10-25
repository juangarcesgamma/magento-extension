<?php

namespace Extend\Warranty\Plugin\Block\Product\View\Type;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as SuperConfigurable;
use Magento\Framework\Serialize\Serializer\Json;

class Configurable
{
    /**
     * @var Json
     */
    protected $jsonSerializer;

    public function __construct(Json $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    public function afterGetJsonConfig(SuperConfigurable $subject, $result)
    {
        $jsonResult = $this->jsonSerializer->unserialize($result);

        $jsonResult['skus'] = [];
        foreach ($subject->getAllowProducts() as $simpleProduct) {
            $jsonResult['skus'][$simpleProduct->getId()] = $simpleProduct->getSku();
        }

        $result = $this->jsonSerializer->serialize($jsonResult);

        return $result;
    }
}
