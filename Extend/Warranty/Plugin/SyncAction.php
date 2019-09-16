<?php

namespace Extend\Warranty\Plugin;

use Extend\Warranty\Controller\Adminhtml\Products\Sync;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SyncAction
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    protected $scopeConfig;

    public function __construct(
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
    }
    public function aroundExecute(Sync $subject, callable $proceed)
    {
        if($this->scopeConfig->isSetFlag('warranty/enableExtend/enable')){
            return $proceed();
        }
        return;

    }
}