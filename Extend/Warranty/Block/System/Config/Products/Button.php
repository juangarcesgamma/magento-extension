<?php

namespace Extend\Warranty\Block\System\Config\Products;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Extend\Warranty\Controller\Adminhtml\Products\Sync;

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


    public function getLastSync(){
        return $this->_scopeConfig->getValue(Sync::LAST_SYNC_PATH);
    }
}