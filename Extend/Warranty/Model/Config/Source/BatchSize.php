<?php

namespace Extend\Warranty\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class BatchSize implements ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => 25, 'label' => __('25')],
            ['value' => 50, 'label' => __('50')],
            ['value' => 100, 'label' => __('100')],
            ['value' => 200, 'label' => __('200')]
        ];
    }
}