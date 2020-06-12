<?php

namespace Elgentos\Kiyoh\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class SalesOrderShipmentSaveAfter extends AbstractObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * SalesOrderShipmentSaveAfter constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        try {
            $this->_sendRequest($order);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
