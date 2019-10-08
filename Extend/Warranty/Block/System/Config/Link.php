<?php


namespace Extend\Warranty\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Link extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Extend_Warranty::system/config/render.phtml';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $label;

    public function __construct(
        Context $context,
        array $data = [],
        $url = '',
        $label = '',
        $class = ''
    )
    {
        $this->url = $url;
        $this->label = $label;
        $this->class = $class;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getHtml()
    {
        $linkHtml = "<a href='{$this->url}' class='{$this->class}'>{$this->label}</a>";

        return $linkHtml;
    }
}