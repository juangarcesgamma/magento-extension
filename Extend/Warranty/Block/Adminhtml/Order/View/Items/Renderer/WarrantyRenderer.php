<?php

namespace Extend\Warranty\Block\Adminhtml\Order\View\Items\Renderer;

use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;
use Magento\Sales\Model\Order\Item;

class WarrantyRenderer extends DefaultRenderer
{
    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        $html = '';
        switch ($column) {
            case 'refund':
                if ($item->getStatusId() == Item::STATUS_INVOICED) {
                    if ($this->canDisplayContainer()) {
                        $html .= '<div id="' . $this->getHtmlId() . '">';
                    }
                    $html .= '<button type="button">Request Refund</button>';
                    if ($this->canDisplayContainer()) {
                        $html .= '</div>';
                    }
                } else {
                    $html .= '&nbsp;';
                }
                break;
            default:
                $html = parent::getColumnHtml($item, $column, $field);
        }
        return $html;
    }

    public function getHtmlId()
    {
        return 'return_order_item_' . $this->getItem()->getId();
    }

}