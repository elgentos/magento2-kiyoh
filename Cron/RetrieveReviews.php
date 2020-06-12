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

    private $filePath;


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

        $this->filePath = $this->getFeedUrl();
    }


    public function getFeedUrl(){
        return $this->storeManager->getStore()->getConfig(
            'kiyoh_settings/general/feed_url'
        );
    }

    public function processAggregateScores() {

        $this->curl->get($this->filePath);
        $output = $this->curl->getBody();

        $doc = simplexml_load_string($output);

        unset($doc->{'reviews'});

        try {
            if (isset($doc->errorCode)) {
                throw new \Magento\Framework\Exception\CronException(__('Invalid xml format, code data missing.'));
            }

            $this->_saveToDb($doc);

        } catch (\Exception $e) {
            $this->log->critical($e->getMessage());
        }
    }

    protected function _saveToDb($data) {
        // save these attributes in custom var DB numberReviews averageRating
        $ratingCodes = ['numberReviews', 'averageRating'];
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
