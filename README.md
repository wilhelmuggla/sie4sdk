## Sie4Sdk

The PHP Sie4 SDK and Sie5 conversion package

Using the Sie4 /Sie5 formats as prescribed at the [Sie] formats [page].

__*Sie4Sdk*__ supports convert/transform of accounting data from / to
- Sie4 : file / string / Dto / array / json / http-service
- Sie/SieEntry : XML / string / Dto

For usage and info, [click here].

#### Installation

[Composer], from the Command Line:

``` php
composer require kigkonsult/Sie4Sdk
```

[Composer], in your `composer.json`:

``` json
{
    "require": {
        "kigkonsult/Sie4Sdk": "dev-master"
    }
}
```
Version 1.8 supports PHP 8, 1.6 7.4, 1.53 7.0.

[Composer], acquire access
``` php
namespace Kigkonsult\Sie4Sdk;
...
include 'vendor/autoload.php';
```


Otherwise , download and acquire..

``` php
namespace Kigkonsult\Sie4Sdk;
...
include 'pathToSource/sie4sdk/autoload.php';
```

Run tests
```
cd pathToSource/Sie4Sdk
vendor/bin/phpunit
```
Sie4Sdk uses [Faker] to generate a major variation of Sie4Sdk test data.<br>
Due to Sie4 and Sie5 disparity, tests will have (acceptable) breaks.
However, the output is still valid.

Test contributions, Sie4-/Sie-/SieEntry-files, are welcome!


#### Sponsorship
Donations using _[buy me a coffee]_ or _[paypal me]_ are appreciated.
For invoice, please e-mail.


#### Support

For __*Sie4Sdk*__ support, please use [Github]/issues.

For Sie4/Sie5 ([XSD]) issues, go to [Sie] homepage.


#### License

This project is licensed under the LGPLv3 License


[buy me a coffee]:https://www.buymeacoffee.com/kigkonsult
[paypal me]:https://paypal.me/kigkonsult
[click here]:usageInfo.md
[Composer]:https://getcomposer.org/
[DsigSdk]:https://github.com/iCalcreator/dsigsdk
[Faker]:https://github.com/fakerphp/faker
[Github]:https://github.com/iCalcreator/Sie4Sdk/issues
[Sie5Sdk]:https://github.com/iCalcreator/SieSdk
[page]:https://sie.se/format/
[Sie]:http://www.sie.se
[XSD]:http://www.sie.se/sie5.xsd

[comment]: # (This file is part of Sie4Sdk, The PHP Sie4I SDK and Sie5 conversion package. Copyright 2021-2024 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)
