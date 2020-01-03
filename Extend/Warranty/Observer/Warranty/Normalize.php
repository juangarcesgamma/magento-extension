<?php


namespace Extend\Warranty\Observer\Warranty;

use Extend\Warranty\Model\Normalizer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Normalize implements ObserverInterface
{
    /**
     * @var Normalizer
     */
    protected $normalizer;

    public function __construct(Normalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $cart = $observer->getEvent()->getCart();
        $info = $observer->getEvent()->getInfo();

        $this->normalizer->normalize($cart,$info);
    }
}