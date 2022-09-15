<?php

namespace Elgentos\Kiyoh\Cron;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\Declaration\Converter\Dom;
use Magento\Framework\View\Element\Template\Context;
use Magento\Variable\Model\VariableFactory;
use \Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;

class RetrieveReviews {

    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Dom
     */
    private $converter;

    /**
     * @var LoggerInterface
     */
    private $log;
    /**
     * @var VariableFactory
     */
    private $variable;

    private $apiKey;

    private $locationId;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Curl $curl,
        Dom $converter,
        LoggerInterface $logger,
        VariableFactory $variable
    ) {
        $this->curl = $curl;
        $this->storeManager = $storeManager;
        $this->converter = $converter;
        $this->log = $logger;
        $this->variable = $variable;

        $this->apiKey = $this->getApiKey();
        $this->locationId = $this->getLocationId();
    }


    public function getApiKey(){
        return $this->storeManager->getStore()->getConfig(
            'kiyoh_settings/general/api_key'
        );
    }

    public function getLocationId(){
        return $this->storeManager->getStore()->getConfig(
            'kiyoh_settings/general/location_id'
        );
    }


    public function processAggregateScores() {

        if (!$this->getLocationId()) {
            throw new \Magento\Framework\Exception\CronException(__('Location ID missing, please set your location ID.'));
        }

        if (!$this->getApiKey()) {
            throw new \Magento\Framework\Exception\CronException(__('API key missing, please set your API key.'));
        }

        $this->curl->addHeader('X-Publication-Api-Token', $this->getApiKey());
        $this->curl->get('https://www.klantenvertellen.nl/v1/publication/review/external/location/statistics?locationId=' . $this->getLocationId());

        $ouput = $this->curl->getBody();
        $jsonOutput = json_decode($ouput);

        try {
            if (isset($jsonOutput->errorCode)) {
                throw new \Magento\Framework\Exception\CronException(__('Kiyoh API error: ' . $jsonOutput->errorCode . ': ' . $jsonOutput->detailedError[0]->message));
            }
            $this->_saveToDb($jsonOutput);

        } catch (\Exception $e) {
            $this->log->critical($e->getMessage());
        }
    }

    protected function _saveToDb($data) {

        // save these attributes in custom var DB numberReviews averageRating
        $ratingCodes = ['numberReviews', 'averageRating', 'recommendation'];
        foreach ($ratingCodes as $ratingCode) {
            $reviewData = $data->{$ratingCode};

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
