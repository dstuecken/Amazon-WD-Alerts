# Amazon Warehouse Deals Alert

Console utility that scans for new warehouse deals and notifies you about their existence. Even via speech output on Mac OS ;-)

[![License](https://poser.pugx.org/dstuecken/Amazon-WD-Alerts/license)](https://packagist.org/packages/dstuecken/Amazon-WD-Alerts)

## Requirements

* PHP 5.4

## Installation from source 

Clone

```shell
git clone https://github.com/dstuecken/Amazon-WD-Alerts.git
cd Amazon-WD-Alerts
```

Install all dependencies via composer:

```shell
composer install
```

Check https://getcomposer.org/download/ if you don't have composer installed.

## Download binary

Or just use this binary with all dependencies included:

https://github.com/dstuecken/Amazon-WD-Alerts/releases/download/1.0.2/amazon-wd-alerts.zip

# Usage

Mac OS & Linux

```shell
./check.sh B01BWAHNH4
```

Windows

```shell
cd Amazon-WD-Alerts
c:/path/to/php.exe check.php B01BWAHNH4
```

Where B01BWAHNH4 is the article number from Amazon.

There is a config called .config.yml where you can define some options like smtp mail notifications or speech output on Mac OS.

### How does it look like?

#### Console notification
![consolenotification](https://cloud.githubusercontent.com/assets/493399/20566057/1fa6ecf6-b194-11e6-8b5c-f9c8d47b1a52.png)

#### Mac OS notification
![macosnotification](https://cloud.githubusercontent.com/assets/493399/20566058/1fc07338-b194-11e6-90db-40fc23a75942.png)


### FAQ

#### How do I enable notification under Windows?

Edit the "shellDeal:" Option in .config.yml to something like:

```shell
shellDeal: msg %username% "Your deal %TITLE% is available for %PRICE%! Go get it!"
```
