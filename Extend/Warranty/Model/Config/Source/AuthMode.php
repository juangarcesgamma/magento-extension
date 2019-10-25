<?php

namespace Extend\Warranty\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class AuthMode implements ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Live')],
            ['value' => 0, 'label' => __('Sandbox')]
        ];
    }
}