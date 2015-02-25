# YellowCube from Post AG - Switzerland

## Description



## License

This extension is licensed under OSL v.3.0
Some classes and javascript contain a MIT license.

## Support & Documentation

## System requirements

- Magento CE >= 1.6.x to 1.9.x
- PHP >= 5.3.2
- PHP Soap, DOM Library
- Cron enabled and configured for Magento (set your cron at server level to a period of 5 min to launch internal task related to the rircardo extension
*/5 * * * * php path/to/my/magento/cron.php)

## Features

-
- 

## Installation

### Via MagentoConnect

- You can install the current stable version via [MagentoConnect Website](http://www.magentocommerce.com/magento-connect/)

### Manually



```

git clone https://github.com/liip/yellowcube-magento.git

git submodule init

git submodule fetch

```



Then copy the files and folders in the corresponding Magento folders

Do not forget the folder "lib"


### Via modman

- Install [modman](https://github.com/colinmollenhour/modman)
- Use the command from your Magento installation folder: `modman clone https://github.com/liip/yellowcube-magento.git`

#### Via Composer

- Install [composer](http://getcomposer.org/download/)
- Create a composer.json into the root folder of your project with the following sample:

```
 {
    "require" : {
        "liip/yellowcube-magento": "1.*"
    },
    "repositories" : [
        {
            "type": "vcs",
            "url": "git@github.com:liip/yellowcube-magento.git"
        },
        {
            "type": "vcs",
            "url": "git@github.com:liip/yellowcube-php.git"
        },
        {
            "type": "vcs",
            "url": "https://github.com/liip-forks/wse-php.git"
        }
    ],
     "scripts": {
         "post-package-install": [
             "YellowCube\\Composer\\Magento::postPackageAction"
         ],
         "post-package-update": [
             "YellowCube\\Composer\\Magento::postPackageAction"
         ],
         "pre-package-uninstall": [
             "YellowCube\\Composer\\Magento::cleanPackageAction"
         ]
     },
     "extra":{
       "magento-root-dir": "./"
     },
     "minimum-stability": "dev"
 }
 ```
- Then from your composer.json folder: `php composer.phar install` or `composer install`


## Known Issues

None

