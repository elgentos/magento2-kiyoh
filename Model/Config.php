<?php

namespace Elgentos\Kiyoh\Model;

use Magento\Store\Model\StoreManagerInterface;

class Config {

    public $locationId;

    public $apiKey;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function getApiKey() {
        return $this->storeManager->getStore()->getConfig(
        'kiyoh_settings/general/api_key'
        );
    }

    public function getLocationId(){
        return $this->storeManager->getStore()->getConfig(
        'kiyoh_settings/general/location_id'
        );
    }

}
