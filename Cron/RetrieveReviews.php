<?php

namespace Elgentos\Kiyoh\Cron;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\Declaration\Converter\Dom;
use Magento\Variable\Model\Variable;
use Magento\Variable\Model\VariableFactory;
use \Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\CronException;
use Elgentos\Kiyoh\Model\Config;

class RetrieveReviews {

    private Curl $curl;
    private LoggerInterface $log;
    private Dom $converter;
    private VariableFactory $variable;
    private Json $jsonSerializer;
    private Config $config;

    public function __construct(
        Curl $curl,
        Json $jsonSerializer,
        LoggerInterface $logger,
        VariableFactory $variable,
        Dom $converter,
        Config $config
    ) {
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->converter = $converter;
        $this->log = $logger;
        $this->variable = $variable;
        $this->config = $config;
    }

    /**
     * @throws CronException|NoSuchEntityException
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

        $output = $this->curl->getBody();
        $jsonOutput = $this->jsonSerializer->unserialize($output);

        try {
            if (isset($jsonOutput->errorCode)) {

                throw new CronException( __(
                    'Kiyoh API error: %1: %2',
                    $jsonOutput->errorCode,
                    $jsonOutput->detailedError[0]->message ?? null
                ));
            }
            $this->saveToDb($jsonOutput);

        } catch (\Exception $e) {
            $this->log->critical($e->getMessage());
        }
    }

    /**
     * @param $data
     * @return void
     */
    protected function saveToDb($data) : void {
        $ratingCodes = ['numberReviews', 'averageRating', 'recommendation'];
        foreach ($ratingCodes as $ratingCode) {
            $reviewData = $data[$ratingCode];

            try {
                $variable = $this->initVariable('kiyoh_' . $ratingCode);
                $variable->setData('name', 'Kiyoh ' . $ratingCode);
                $variable->setData('html_value', $reviewData);
                $variable->setData('plain_value' , $reviewData);
                $variable->save();

            } catch (\Exception $e) {
                $this->log->critical($e->getMessage());
            }
        }
    }


    protected function initVariable($code, int $storeId = 0): Variable
    {
        return $this->variable->create()->setStoreId($storeId)->loadByCode($code)->setCode($code);
    }

}
