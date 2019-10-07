<?php

namespace Extend\Warranty\Helper\Api;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    CONST BASEPATH = 'warranty/authentication/';

    CONST ENABLE_PATH = 'warranty/enableExtend/enable';

    protected $scopeConfig;

    public function __construct
    (
        Context $context,
        ScopeConfigInterface $config
    )
    {
        $this->scopeConfig = $config;
        parent::__construct($context);
    }

    public function getValue(string $field)
    {
        $path = self::BASEPATH . $field;
        return $this->scopeConfig->getValue($path);
    }

    public function isExtendEnabled()
    {
        return $this->scopeConfig->isSetFlag($this::ENABLE_PATH);
    }
}