<?php

namespace Extend\Warranty\Block\System\Config\Products;

use Magento\Config\Block\System\Config\Form\Field;

class Button extends Field
{
    protected $_template = "Extend_Warranty::system/config/products/button.phtml";

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td class="value">';
        $html .= $this->toHtml();
        $html .= '</td>';
        return $html;
    }
}