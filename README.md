## Elgentos Kiyoh

This extension fetches review scores from kiyoh and stores them in the m2 database. This is only compatible with KiyohNl. But feel free to open an pr to support kiyoh.com and `klanten vertellen` as well. 

### How to install

:rocket: Install via composer (recommend)
	composer require elgentos/kiyoh
	php bin/magento setup:upgrade
	php bin/magento setup:static-content:deploy

### Features

- Cronjob that fetches review scores every night
- ViewModel to offload logic from a block class
- Send review email + offset when a shipment is made in m2 (config option)
