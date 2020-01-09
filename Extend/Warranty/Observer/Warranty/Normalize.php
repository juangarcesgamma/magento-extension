<?php


namespace Extend\Warranty\Observer\Warranty;

use Extend\Warranty\Model\Normalizer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Extend\Warranty\Helper\Api\Data;

class Normalize implements ObserverInterface
{
    /**
     * @var Normalizer
     */
    protected $normalizer;

    /**
     * @var Data
     */
    protected $helper;

    public function __construct(Normalizer $normalizer, Data $helper)
    {
        $this->normalizer = $normalizer;
        $this->helper = $helper;

    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if(!$this->helper->isBalancedCart()){
            return;
        }

        $cart = $observer->getEvent()->getCart();
        $info = $observer->getEvent()->getInfo();

        $this->normalizer->normalize($cart, $info);
    }
}