<?php

declare(strict_types=1);

namespace Elgentos\Kiyoh\Service;

use Elgentos\Kiyoh\Model\Config;
use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Variable\Model\ResourceModel\Variable as VariableResource;
use Magento\Variable\Model\Variable;
use Magento\Variable\Model\VariableFactory;
use Magento\Store\Model\Store;

class RetrieveKiyohReviews {

    private const RATING_CODES = ['numberReviews', 'averageRating', 'recommendation'];
    private const DEFAULT_OUTPUT = [
        'averageRating' => 0,
        'numberReviews' => 0,
        'recommendation' => 0,
    ];

    private StoreManagerInterface $storeManager;

    private Json $serializer;

    private Config $config;

    private Curl $curl;

    private VariableFactory $variableFactory;

    private VariableResource $variableResource;

    public function __construct(
        Curl                  $curl,
        Json                  $serializer,
        VariableFactory       $variableFactory,
        VariableResource      $variableResource,
        Config                $config,
        StoreManagerInterface $storeManager
    )
    {
        $this->serializer = $serializer;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->curl = $curl;
        $this->variableFactory = $variableFactory;
        $this->variableResource = $variableResource;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(): void {
        $errors = [];

        foreach ($this->storeManager->getStores(true) as $store) {

            if ((int) $store->getId() === Store::DEFAULT_STORE_ID) {
                try {
                    $this->saveToDb(self::DEFAULT_OUTPUT, $store);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }

                continue;
            }

            if (!$this->isEnabled($store)) {
                continue;
            };

            $this->curl->addHeader('X-Publication-Api-Token', $this->config->getApiKey((int) $store->getId()));
            $this->curl->get(sprintf(Config::PUBLICATION_URL, $this->config->getLocationId((int) $store->getId())));

            try {
                $output = $this->serializer->unserialize($this->curl->getBody());
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                continue;
            }

            if (isset($output['errorCode'])) {
                $errors[] =  __(
                    'Kiyoh API error: %1: %2',
                    $output['errorCode'],
                    $output['detailedError'][0]['message'] ?? null
                );
                continue;
            }

            try {
                $this->saveToDb($output, $store);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (count($errors)) {
            throw new LocalizedException(__('Errors during retrieval of Kiyoh Reviews: %1', implode("\n", $errors)));
        }

    }

    /**
     * @throws AlreadyExistsException
     */
    protected function saveToDb(array $data, StoreInterface $store): void
    {
        foreach (self::RATING_CODES as $ratingCode) {
            $reviewData = $data[$ratingCode] ?? null;
            $variable = $this->initVariable(sprintf('kiyoh_%s', $ratingCode), (int) $store->getId());
            $variable->setData('name', sprintf('Kiyoh %s', $ratingCode))
                ->setData('html_value', $reviewData)
                ->setData('plain_value', $reviewData);

            $this->variableResource->save($variable);

        }
    }

    public function isEnabled(StoreInterface $store): bool
    {
        return $this->config->isEnabled((int) $store->getId()) &&
            $this->config->getLocationId((int) $store->getId()) &&
            $this->config->getApiKey((int) $store->getId());

    }

    protected function initVariable(string $code, int $storeId = null): Variable
    {
        $variable = $this->variableFactory->create();

        return $variable
            ->setStoreId($storeId)
            ->loadByCode($code)
            ->setCode($code);
    }
}
