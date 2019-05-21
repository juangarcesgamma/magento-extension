<?php


namespace Extend\Config\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;

class Intro extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Extend_Config::intro.phtml';

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope();
        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->toHtml();
    }
}