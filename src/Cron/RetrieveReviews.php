<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\Kiyoh\Cron;

use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Variable\Model\ResourceModel\Variable as VariableResource;
use Magento\Variable\Model\Variable;
use Magento\Variable\Model\VariableFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\CronException;
use Elgentos\Kiyoh\Model\Config;

class RetrieveReviews
{
    private const RATING_CODES = ['numberReviews', 'averageRating', 'recommendation'];

    private Curl $curl;

    private LoggerInterface $log;

    private VariableFactory $variableFactory;

    private Json $serializer;

    private Config $config;

    private VariableResource $variableResource;

    public function __construct(
        Curl $curl,
        Json $serializer,
        LoggerInterface $logger,
        VariableFactory $variableFactory,
        VariableResource $variableResource,
        Config $config
    ) {
        $this->curl             = $curl;
        $this->serializer       = $serializer;
        $this->log              = $logger;
        $this->variableFactory  = $variableFactory;
        $this->config           = $config;
        $this->variableResource = $variableResource;
    }

    /**
     * @throws CronException
     */
    public function processAggregateScores(): void
    {
        if (!$this->config->getLocationId()) {
            throw new CronException(__('Location ID missing, please set your location ID.'));
        }

        if (!$this->config->getApiKey()) {
            throw new CronException(__('API key missing, please set your API key.'));
        }

        $this->curl->addHeader('X-Publication-Api-Token', $this->config->getApiKey());
        $this->curl->get(sprintf(Config::PUBLICATION_URL, $this->config->getLocationId()));

        $output = $this->serializer->unserialize($this->curl->getBody());

        if (isset($output['errorCode'])) {
            throw new CronException(
                __(
                    'Kiyoh API error: %1: %2',
                    $output['errorCode'],
                    $output['detailedError'][0]['message'] ?? null
                )
            );
        }

        try {
            $this->saveToDb($output);
        } catch (Exception $e) {
            $this->log->critical($e->getMessage());
        }
    }

    protected function saveToDb(array $data): void
    {
        foreach (self::RATING_CODES as $ratingCode) {
            $reviewData = $data[$ratingCode] ?? null;
            $variable   = $this->initVariable(sprintf('kiyoh_%s', $ratingCode));
            $variable->setData('name', sprintf('Kiyoh %s', $ratingCode))
                ->setData('html_value', $reviewData)
                ->setData('plain_value', $reviewData);

            try {
                $this->variableResource->save($variable);
            } catch (Exception $e) {
                $this->log->critical($e->getMessage());
            }
        }
    }

    protected function initVariable(string $code, int $storeId = null): Variable
    {
        /** @var Variable $variable */
        $variable = $this->variableFactory->create();

        return $variable
            ->setStoreId($storeId)
            ->loadByCode($code)
            ->setCode($code);
    }
}
