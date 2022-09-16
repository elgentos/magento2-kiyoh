<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\Kiyoh\Observer;

use Elgentos\Kiyoh\Model\Config;
use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

abstract class AbstractObserver
{
    private const KIYOH_REQUEST_ENDPOINT_URL = 'https://www.kiyoh.nl/set.php';

    private Curl $curl;

    private Config $config;

    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        Curl $curl,
        Config $config
    ) {
        $this->logger = $logger;
        $this->curl   = $curl;
        $this->config = $config;
    }

    public function isCustomerGroupExcluded(
        Order $order,
        int $storeId = null
    ): bool {
        return in_array(
            $order->getCustomerGroupId(),
            $this->config->getExcludedCustomerGroups($storeId),
            true
        );
    }

    public function getPostVariables(
        Order $order,
        int $storeId = null
    ): array {
        return [
            'user' => $this->config->getKiyohEmail($storeId),
            'connector' => $this->config->getApiKey($storeId),
            'action' => 'sendInvitation',
            'targetMail' => $order->getCustomerEmail(),
            'delay' => $this->config->getDelay($storeId)
        ];
    }

    protected function sendRequest(Order $order)
    {
        $storeId = (int) $order->getStoreId();

        if (
            !$order->getId() ||
            !$this->config->isEnabled() ||
            !$this->config->sendDataToKiyoh() ||
            $this->isCustomerGroupExcluded($order, $storeId)
        ) {
            return;
        }

        try {
            $this->curl->get(
                sprintf(
                    '%s?%s',
                    self::KIYOH_REQUEST_ENDPOINT_URL,
                    http_build_query($this->getPostVariables($order, $storeId))
                )
            );
        } catch (Exception $e) {
            $this->logException($e->getMessage());
        }
    }

    protected function logException(string $message): void
    {
        $this->logger->critical($message);
    }
}
