<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\Kiyoh\ViewModel;

use Elgentos\Kiyoh\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\Variable;
use Magento\Variable\Model\VariableFactory;

class Kiyoh implements ArgumentInterface
{
    private StoreManagerInterface $storeManager;

    private VariableFactory $variable;

    private Config $config;

    public function __construct(
        StoreManagerInterface $storeManager,
        VariableFactory $variableFactory,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->variable     = $variableFactory;
        $this->config       = $config;
    }

    public function getReviewCount(): int
    {
        return (int) $this->getVariableValueByCode('kiyoh_numberReviews');
    }

    public function getRating(): float
    {
        return (float) $this->getVariableValueByCode('kiyoh_averageRating');
    }

    public function getRecommendationPercentage(): float
    {
        return (float) $this->getVariableValueByCode('kiyoh_recommendation');
    }

    public function getRatingPercentage(int $precision = 0): string
    {
        return number_format($this->getRating() * 10, $precision);
    }


    public function getKiyohCustomerUrl(): string
    {
        return $this->config->getKiyohUrl();
    }

    public function getVariableValueByCode(string $code): ?string
    {

        try {
            $variable = $this->variable->create()
                ->setStoreId((int) $this->storeManager->getStore()->getId())
                ->loadByCode((string) $code);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $variable instanceof Variable
            ? $variable->getValue()
            : null;
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }
}
