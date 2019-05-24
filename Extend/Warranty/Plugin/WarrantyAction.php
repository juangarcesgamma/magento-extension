<?php

namespace Extend\Warranty\Plugin;

use \Magento\Catalog\Controller\Adminhtml\Product\NewAction;
use \Magento\Backend\Model\View\Result\ForwardFactory;

class WarrantyAction
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    public function __construct(
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
    }
    public function aroundExecute(NewAction $subject, callable $proceed){
        $arrOfNotAlowedTypesIds = [\Extend\Warranty\Model\Product\Type::TYPE_CODE];
        $typeId = $subject->getRequest()->getParam('type');
        if(in_array($typeId,$arrOfNotAlowedTypesIds)) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        return $proceed();
    }
}