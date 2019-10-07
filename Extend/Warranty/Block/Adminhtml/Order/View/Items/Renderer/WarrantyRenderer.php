<?php

namespace Extend\Warranty\Block\Adminhtml\Order\View\Items\Renderer;

use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;
use Magento\Sales\Model\Order\Item;
use Magento\Backend\Block\Template\Context;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Registry;
use Magento\GiftMessage\Helper\Message;
use Magento\Checkout\Helper\Data;
use Extend\Warranty\Helper\Api\Data as ExtendData;

class WarrantyRenderer extends DefaultRenderer
{
    /**
     * @var ExtendData
     */
    protected $enable;

    public function __construct
    (
        Context $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        Message $messageHelper,
        Data $checkoutHelper,
        ExtendData $extendHelper,
        array $data = []
    )
    {
        parent::__construct
        (
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $messageHelper,
            $checkoutHelper,
            $data
        );

        $this->enable = $extendHelper->isExtendEnabled();
    }

    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        if (!$this->enable) {
            return parent::getColumnHtml($item, $column, $field);
        }
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
            '", "itemId": "' . $item->getId() . '" }}';
    }

    public function getHtmlId()
    {
        return 'return_order_item_' . $this->getItem()->getId();
    }

}