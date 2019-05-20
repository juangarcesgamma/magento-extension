<?php


namespace Extend\Config\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;


class Button extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Extend_Config::system/config/button.phtml';

    protected $id;
    protected $label;

    public function __construct(Context $context, array $data = [], $id = '', $label = '')
    {
        $this->id = $id;
        $this->label = $label;
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

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => $this->id,
                'label' => __($this->label)
            ]
        );

        return $button->toHtml();
    }
}