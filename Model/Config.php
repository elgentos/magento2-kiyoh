<?php

namespace Elgentos\Kiyoh\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Config {

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    const PUBLICATION_URL = 'https://www.kiyoh.com/v1/publication/review/external/location/statistics?locationId=';

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getApiKey(): mixed {
        return $this->storeManager->getStore()->getConfig(
        'kiyoh_settings/general/api_key'
        );
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getLocationId(): mixed {
        return $this->storeManager->getStore()->getConfig(
        'kiyoh_settings/general/location_id'
        );
    }

}
