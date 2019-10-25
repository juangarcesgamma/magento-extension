<?php

namespace Extend\Warranty\Block\System\Config\Products;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Extend\Warranty\Api\TimeUpdaterInterface;


class Button extends Field
{
    protected $_template = "Extend_Warranty::system/config/products/button.phtml";

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _renderValue(AbstractElement $element)
    {
        $html = '<td class="value">';
        $html .= $this->toHtml();
        $html .= '</td>';
        return $html;
    }


    public function getLastSync()
    {
        return $this->_scopeConfig->getValue(TimeUpdaterInterface::LAST_SYNC_PATH);
    }
}