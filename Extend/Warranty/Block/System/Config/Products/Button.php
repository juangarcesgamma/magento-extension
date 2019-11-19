<?php

namespace Extend\Warranty\Block\System\Config\Products;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Extend\Warranty\Api\TimeUpdaterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Backend\Block\Template\Context;

class Button extends Field
{
    protected $_template = "Extend_Warranty::system/config/products/button.phtml";

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    public function __construct
    (
        Context $context,
        TimezoneInterface $timezone,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->timezone = $timezone;
    }

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
        $date = $this->_scopeConfig->getValue(TimeUpdaterInterface::LAST_SYNC_PATH);

        if (empty($date)) {
            return '';
        }

        $date = new \DateTime($date);

        return $this->timezone->formatDate($date, 1, true);
    }
}