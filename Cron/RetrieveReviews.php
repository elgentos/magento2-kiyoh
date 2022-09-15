<?php

namespace Elgentos\Kiyoh\Cron;

use Magento\Framework\App\Cron;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\Declaration\Converter\Dom;
use Magento\Framework\View\Element\Template\Context;
use Magento\Variable\Model\VariableFactory;
use \Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\CronException;
use Elgentos\Kiyoh\Model\Config;

class RetrieveReviews {

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var Dom
     */
    private $converter;

    /**
     * @var VariableFactory
     */
    private $variable;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Context $context,
        Curl $curl,
        Json $jsonSerializer,
        LoggerInterface $logger,
        VariableFactory $variable,
        CronException $cronException,
        Dom $converter,
        Config $config
    ) {
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->converter = $converter;
        $this->log = $logger;
        $this->variable = $variable;
        $this->cronException = $cronException;
        $this->config = $config;
    }

    public function processAggregateScores() {

        if (!$this->config->getLocationId()) {
            throw new CronException(__('Location ID missing, please set your location ID.'));
        }

        if (!$this->config->getApiKey()) {
            throw new CronException(__('API key missing, please set your API key.'));
        }

        $this->curl->addHeader('X-Publication-Api-Token', $this->getApiKey());
        $this->curl->get(config::PUBLICATION_URL . $this->getLocationId());


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


    /**
     * @param $code
     * @param int $storeId
     * @return Variable
     */
    protected function initVariable($code, $storeId = 0)
    {
        return $this->variable->create()->setStoreId($storeId)->loadByCode($code)->setCode($code);
    }

}
