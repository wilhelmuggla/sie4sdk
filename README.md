## Sie4Sdk

The PHP Sie4 SDK and Sie5 conversion package

Using the Sie4 /Sie5 formats as prescribed at the [Sie] formats [page]

__*Sie4Sdk*__ supports convert/transform of accounting data from / to
- Sie4 : file / string / Dto / array / json
- Sie/SieEntry : XML / string / Dto


#### Usage

The Sie4EDto (Sie4E, export) usage corresponds to Sie4IDto (Sie4I, import) usage. 

First, load a Sie4IDto instance (if not used as input)

```php
<?php
namespace Kigkonsult\Sie4Sdk;

// Parse a Sie4 file/string into a Sie4IDto instance
$sie4IDto = Sie4::sie4IFileString2Sie4Dto( $sie4I_string );
$sie4IDto = Sie4::sie4IFileString2Sie4Dto( $sie4I_file );

// Transform a (HTTP, $_REQUEST) input array to a Sie4IDto instance
$sie4IDto = Sie4::array2Sie4Dto( $sie4I_array );

// Transform an input json string to a Sie4IDto instance
$sie4IDto = Sie4::json2Sie4Dto( $sie4I_json );

// Convert a SieEntry (Sie5) instance into a Sie4IDto instance
$sie4IDto = Sie4::sieEntry2Sie4IDto( $sieEntry );

// Transform a SieEntry (Sie5) XML into a Sie4IDto instance
$sie4Dto = Sie4::sieEntryXML2Sie4IDto( $sieEntryXML );

// Transform a SieEntry (Sie5) XML file into Sie4IDto instance
$sie4Dto = Sie4::sieEntryfile2Sie4IDto( $sieEntryFile );

```

Second, validate the Sie4IDto instance

```php
<?php
namespace Kigkonsult\Sie4Sdk;

// Assert mandatory sie4Dto properties
Sie4Validator::assertSie4IDto( $sie4Dto );
```

Last, process the output:

```php
<?php
namespace Kigkonsult\Sie4Sdk;

// Write the Sie4IDto instance to a Sie4 string
$sie4IString = Sie4::sie4IDto2String( $sie4Dto );

// Write the Sie4IDto instance to a Sie4 file
Sie4::sie4IDto2File( $sie4Dto, $outputfile );

// Transform the Sie4IDto instance to an array
$sie4IArray  = Sie4::sie4Dto2Array( $sie4Dto );

// Transform the Sie4IDto instance to a json string
$sie4Ijson   = Sie4::sie4Dto2Json( $sie4Dto );

// Convert the Sie4IDto instance to a SieEntry (Sie5) instance
$sieEntry    = Sie4::sie4IDto2SieEntry( $sie4Dto );

// Transform the Sie4IDto instance to a SieEntry (Sie5) XML string
$sieEntryXML = Sie4::sie4IDto2SieEntryXml( $sie4Dto );

```

#### Info

__*Sie4Sdk*__ 
- require PHP7+
- uses kigkonsult\\[SieSdk] for Sie5 Sie/SieEntry and parse/write XML parts.
- the Sie4 input/output string/file uses PHP CP437, IBM PC 8-bitars extended ASCII (Codepage 437),
all other PHP inbounding encoding (UTF-8)
- usefull constants are found in the Sie4Interface
- for the Sie4 - Sie4Dto - array mapping scheme, review mapping.txt.<br>
  For array(/json) format, review top of src/Api/Array2Sie4Dto.php file


To set up Sie4Sdk as a network service (using REST APIs, as a microservice etc), [Comet] is to recommend.


###### Sie4 - Sie/SieEntry comments

Note för GEN
* if _datum_ is missing, date 'now' is used
* if _sign_ is missing, (#PROGRAM) _programnamn_ is used

UNDERDIM are skipped.

Note för VER
* if _verdatum_ is missing, date 'now' is used
* if _regdatum_ is missing, _verdatum_ is used
* if _sign_ is missing, GEN _sign_ is used

Note för TRANS
* only support for _dimensionsnummer_ and _objektnummer_ in the _objektlista_<br>
    i.e. no support for _hierarkiska dimensioner_

Sie4 dates has format _YYYYmmdd_, Sie/SieEntry _YYYY-MM-DDThh:mm:ssZ_

The (Sie4) KSUMMA checksum is experimental.

Note for Sie4 - Sie/SieEntry conversion
* Sie/SieEntry Journal id (and name) is set from #VER serie, if empty, '' is set
* \#IB, #UB, #OIB, #OUB and #RES skipped due to month/period not exists
* \#PSALDO goes into the AccountTypes as CLOSINGBALANCE, only arsnr 0 accepted
* \#PBUDGET goes into the AccountTypes as BUDGET, only arsnr 0 accepted

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
include 'pathToSource/sie5sdk/autoload.php';
```

Run tests
```
cd pathToSource/Sie4Sdk
vendor/bin/phpunit
```
Due to Sie4 and Sie5 disparity, tests will have (acceptable) breaks.
However, the output is still valid.

Test contributions, Sie4i-/SieEntry-files, are welcome!


#### Sponsorship
Donation using [paypal.me/kigkonsult] are appreciated.
For invoice, please [e-mail]</a>.


#### Support

For __*Sie4Sdk*__ support, please use [Github]/issues.

For Sie5 ([XSD]) issues, go to [Sie] homepage.


#### License

This project is licensed under the LGPLv3 License


[Composer]:https://getcomposer.org/
[Comet]:https://github.com/gotzmann/comet
[DsigSdk]:https://github.com/iCalcreator/dsigsdk
[e-mail]:mailto:ical@kigkonsult.se
[Github]:https://github.com/iCalcreator/Sie4Sdk/issues
[SieSdk]:https://github.com/iCalcreator/SieSdk
[page]:https://sie.se/format/
[paypal.me/kigkonsult]:https://paypal.me/kigkonsult
[Sie]:http://www.sie.se
[XSD]:http://www.sie.se/sie5.xsd

[comment]: # (This file is part of Sie4Sdk, The PHP Sie4I SDK and Sie5 conversion package. Copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)
