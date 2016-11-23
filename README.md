# Amazon Warehouse Deals Alert

Console utility that scans for new warehouse deals and notifies you about their existence. Even via speech output on Mac OS ;-)

[![License](https://poser.pugx.org/dstuecken/Amazon-WD-Alerts/license)](https://packagist.org/packages/dstuecken/Amazon-WD-Alerts)
[![Latest Stable Version](https://poser.pugx.org/dstuecken/Amazon-WD-Alerts/v/stable)](https://packagist.org/packages/dstuecken/Amazon-WD-Alerts)

## Requirements

* PHP 5.4

## Installation

Install all dependencies via composer:

```shell
composer install
```

Check https://getcomposer.org/download/ if you don't have composer installed.

# Usage

```shell
./check.sh B01BWAHNH4
```

Where B01BWAHNH4 is the article number from Amazon.

There is a config called .config.yml where you can define some options like smtp mail notifications or speech output on Mac OS.

### How does it look like?

#### Console notification
![consolenotification](https://cloud.githubusercontent.com/assets/493399/20566057/1fa6ecf6-b194-11e6-8b5c-f9c8d47b1a52.png)

#### Mac OS notification
![macosnotification](https://cloud.githubusercontent.com/assets/493399/20566058/1fc07338-b194-11e6-90db-40fc23a75942.png)
