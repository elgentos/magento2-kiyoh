<?php

namespace Elgentos\Kiyoh\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config {

    public const PUBLICATION_URL = 'https://www.kiyoh.com/v1/publication/review/external/location/statistics?locationId=%d';
    public const MAX_RATING = '10';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getApiKey(): mixed {
        return $this->scopeConfig->getValue(
            'kiyoh_settings/general/api_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getLocationId(): mixed {
        return $this->scopeConfig->getValue(
            'kiyoh_settings/general/location_id',
            ScopeInterface::SCOPE_STORE
        );
    }

}
