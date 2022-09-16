<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\Kiyoh\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const MAX_RATING = '10',
        PUBLICATION_URL     = 'https://www.kiyoh.com/v1/publication/review/external/location/statistics?locationId=%d';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'kiyoh_settings/general/enable',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function sendDataToKiyoh(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'kiyoh_settings/general/send_data_to_kiyoh',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getKiyohEmail(int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            'kiyoh_settings/general/email',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getDelay(int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            'kiyoh_settings/review_settings/delay',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getExcludedCustomerGroups(int $storeId = null): array
    {
        return explode(
            ',',
            $this->scopeConfig->getValue(
                'kiyoh_settings/review_settings/exclude_customer_groups',
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) ?: ''
        );
    }

    public function getApiKey(int $storeId = null): string
    {
        return $this->scopeConfig->getValue(
            'kiyoh_settings/general/api_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getLocationId(int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            'kiyoh_settings/general/location_id',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getKiyohUrl(int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            'kiyoh_settings/general/kiyoh_external_url',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
