## Elgentos Kiyoh

This extension fetches review scores from Kiyoh and stores them in the m2 database. This is only compatible with KiyohNL. But feel free to open an pr to support kiyoh.com and `klanten vertellen` as well. 

### How to install

:rocket:  Install via composer (recommend)
	composer require elgentos/kiyoh
	php bin/magento setup:upgrade
	php bin/magento setup:static-content:deploy

### Run the cronjob

Run the cronjob with magerun2 `magerun2 sys:cron:run retrieve_reviews_from_kiyoh`

### Features

- Cronjob that fetches review scores every night
- ViewModel to offload logic from a block class
- Send review email + offset when a shipment is made in m2 (config option)
- This extension stores the data in Custom Variables in de m2 database
