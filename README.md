## Elgentos Kiyoh for Magento 2

This extension fetches site review scores from Kiyoh and stores them in the m2 database. This is only compatible with KiyohNL. But feel free to open an pr to support kiyoh.com and `klanten vertellen` as well. 

Note: this extension is used for Kiyoh *site reviews*, not for Kiyoh *product reviews*. For the product reviews extension, see [dutchwebdesign/magento2-kiyoh](https://github.com/dutchwebdesign/magento2-kiyoh).

### How to install

:rocket:  Install via composer (recommend)
```
composer require elgentos/magento2-kiyoh
bin/magento setup:upgrade
bin/magento setup:static-content:deploy
```

### Configuration

- Enable the extension
- Setup an interval number in days
- Enter the Kiyoh known email adress
- Enter the Kiyoh API key (only to send data to kiyoh)
- Enter the feed URL (https://www.kiyoh.com/v1/review/feed.xml?hash=YOURHASHHERE)
- Enter the url from the public page in kiyoh

### Run the cronjob

Run the cronjob with magerun2 `magerun2 sys:cron:run retrieve_reviews_from_kiyoh`

### Features

- Cronjob that fetches review scores every night
- ViewModel to offload logic from a block class
- Send review email + offset when a shipment is made in m2 (config option)
- This extension stores the data in Custom Variables in de m2 database

### How does it work

- Gets the xml feed and puts the data in custom vars
- Has a viewModel that transform this data in the correct form on the frontend
