<?php

namespace Elgentos\Kiyoh\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\VariableFactory;

/**
 * Class Kiyoh
 * @package Elgentos\Kiyoh\ViewModel
 */
class Kiyoh implements ArgumentInterface
{
    private StoreManagerInterface $storeManager;

    private VariableFactory $variable;

    private ScopeConfigInterface $scopeConfig;

    /**
     * Kiyoh constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param VariableFactory $variableFactory
     * @param ScopeConfigInterface $scopeConfig
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
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function getReviewCount(): ?string
    {

        return $this->getVariableValueByCode('kiyoh_numberReviews');
    }

    /**
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function getRating(): ?string
    {

        return $this->getVariableValueByCode('kiyoh_averageRating');
    }

    /**
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function getRatingPercentage(): ?string
    {
        return $this->getVariableValueByCode('kiyoh_recommendation');
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getKiyohCustomerUrl(): ?string
    {
        return $this->storeManager->getStore()->getConfig(
            'kiyoh_settings/general/kiyoh_external_url'
        );
    }

    /**
     * @param $code
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getVariableValueByCode($code): ?string
    {
        $customVariable = $this->variable->create()
            ->setStoreId($this->storeManager->getStore()->getId())
            ->loadByCode($code);

        return $customVariable instanceof VariableFactory
            ? $customVariable->getValue()
            : null;

    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'kiyoh_settings/general/enable',
            ScopeInterface::SCOPE_STORE);
    }
}
