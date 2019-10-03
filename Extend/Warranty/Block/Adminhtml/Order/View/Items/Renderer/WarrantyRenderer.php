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
                    $options = $item->getProductOptions();
                    if (isset($options['refund']) && $options['refund'] === false) {
                        if ($this->canDisplayContainer()) {
                            $html .= '<div id="' . $this->getHtmlId() . '">';
                        }
                        $html .= '<button type="button"' . " data-mage-init='{$this->getDataInit($item)}' >Request Refund</button>";
                        if ($this->canDisplayContainer()) {
                            $html .= '</div>';
                        }
                    } else if (isset($options['refund']) && $options['refund'] === true) {
                        if ($this->canDisplayContainer()) {
                            $html .= '<div id="' . $this->getHtmlId() . '">';
                        }
                        $html .= '<button type="button" disabled>Refunded</button>';
                        if ($this->canDisplayContainer()) {
                            $html .= '</div>';
                        }
                    } else {
                        $html .= '&nbsp;';
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

    private function getDataInit($item)
    {
        return '{"refundWarranty": {"url": "' . $this->getUrl('extend/contract/refund') .
                '", "contractId": "' . $item->getContractId() .
                '", "itemId": "' . $item->getId() .  '" }}';
    }

    public function getHtmlId()
    {
        return 'return_order_item_' . $this->getItem()->getId();
    }

}