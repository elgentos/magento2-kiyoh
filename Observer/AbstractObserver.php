<?php

namespace Elgentos\Kiyoh\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractObserver
{
    /**
     * @var Curl
     */
    public $curl;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AbstractObserver constructor.
     * @param LoggerInterface $loggerInterface
     * @param Curl $curl
     * @param StoreManagerInterface $storeManager
     */
    private function __construct(
        LoggerInterface $loggerInterface,
        Curl $curl,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $loggerInterface;
        $this->curl = $curl;
        $this->storeManager = $storeManager;
    }

    public function isEnabled($storeId) {
        return $this->storeManager->getStore($storeId)->getConfig(
            'kiyoh_settings/general/enable'
        );
    }

    public function sendDataToKiyoh($storeId) {
        return $this->storeManager->getStore($storeId)->getConfig(
            'kiyoh_settings/general/send_data_to_kiyoh'
        );
    }

    public function isCustomerGroupExcluded($storeId, $order) {
        $excludedCustomerGroups = $this->storeManager->getStore($storeId)->getConfig(
            'kiyoh_settings/review_settings/exclude_customer_groups'
        );

        if ($excludedCustomerGroups) {
            $excludedCustomerGroupsArray = explode(',', $excludedCustomerGroups);
        }

        if (in_array($order->getCustomerGroupId(), $excludedCustomerGroupsArray)) {
            return true;
        }

        return false;
    }

    public function getPostVariables($storeId, $order) {

        $kiyohUserEmail = $this->storeManager->getStore($storeId)->getConfig(
            'kiyoh_settings/general/email'
        );

        $apiKey = $this->storeManager->getStore($storeId)->getConfig(
            'kiyoh_settings/general/api_key'
        );

        $delay = $this->storeManager->getStore($storeId)->getConfig(
            'kiyoh_settings/review_settings/delay'
        );

        return $vars = [
            'user' => $kiyohUserEmail,
            'connector' => $apiKey,
            'action' => 'sendInvitation',
            'targetMail' => $order->getCustomerEmail(),
            'delay' => $delay
        ];
    }

    /**
     * @param $order
     */
    protected function _sendRequest(Order $order)
    {
        $storeId = $order->getStoreId();

        if (!$order->getId()) {
            return;
        }

        if (!$this->isEnabled($storeId)) {
            return;
        }

        if (!$this->sendDataToKiyoh($storeId)) {
            return;
        }

        if ($this->isCustomerGroupExcluded($storeId, $order)) {
            return;
        }

        $postVariables = $this->getPostVariables($storeId, $order);
        $url = 'https://www.kiyoh.nl/set.php?' . http_build_query($postVariables);

        try {
            $this->curl->get($url);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
