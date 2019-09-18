<?php


namespace Extend\Warranty\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Intro extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Extend_Warranty::system/config/intro.phtml';

    public function render(AbstractElement $element)
    {
        $element->unsScope();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }
}