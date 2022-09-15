<?php

namespace Elgentos\Kiyoh\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\Variable;
use Magento\Variable\Model\VariableFactory;

/**
 * Class Kiyoh
 * @package Elgentos\Kiyoh\ViewModel
 */
class Kiyoh implements ArgumentInterface
{

    /**
     * @var ScopeInterface
     */
    private $storeManager;

    /**
     * @var VariableFactory
     */
    private $variable;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Kiyoh constructor.
     *
     * @param StoreManagerInterface       $storeManager
     * @param VariableFactory             $variableFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        VariableFactory $variableFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->variable = $variableFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool|Variable|string
     */
    public function getReviewCount(){

        if(!$this->getVariableValueByCode('kiyoh_numberReviews')){
            return false;
        }

        return $this->getVariableValueByCode('kiyoh_numberReviews');
    }

    /**
     * @return bool|Variable|string
     * @throws NoSuchEntityException
     */
    public function getRating(){

        if(!$this->getVariableValueByCode('kiyoh_averageRating')){
            return false;
        }

        return $this->getVariableValueByCode('kiyoh_averageRating');
    }

    /**
     * @return bool|Variable|string
     * @throws NoSuchEntityException
     */
    public function getRatingPercentage(){
        if(!$this->getVariableValueByCode('kiyoh_recommendation')){
            return false;
        }

        return $this->getVariableValueByCode('kiyoh_recommendation');
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getKiyohCustomerUrl(){
        return $this->storeManager->getStore()->getConfig(
            'kiyoh_settings/general/kiyoh_external_url'
        );
    }

    /**
     * @return int
     */
    public function getMaxRating(){
        return 10;
    }


    /**
     * @param $code
     * @return string
     * @throws NoSuchEntityException
     */
    public function getVariableValueByCode($code) {

        $customVariable = $this->variable->create()->setStoreId(
            $this->storeManager->getStore()->getId()
        )->loadByCode(
            $code
        );

        if ($customVariable) {
            return $customVariable->getValue();
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function isEnabled() {
        return $this->scopeConfig->getValue('kiyoh_settings/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
