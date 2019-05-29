<?php

namespace Extend\Warranty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const NOT_ALLOWED_TYPES = [\Extend\Warranty\Model\Product\Type::TYPE_CODE];
}