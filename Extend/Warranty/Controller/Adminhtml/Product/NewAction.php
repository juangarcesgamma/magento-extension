<?php

namespace Extend\Warranty\Controller\Adminhtml\Product;

class NewAction extends \Magento\Catalog\Controller\Adminhtml\Product\NewAction
{
    public function execute()
    {
        if (!$this->getRequest()->getParam('set')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $arrOfNotAlowedTypesIds = array('warranty');
        $typeId = $this->getRequest()->getParam('type');
        if(in_array($typeId,$arrOfNotAlowedTypesIds)) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        return parent::execute();

    }

}