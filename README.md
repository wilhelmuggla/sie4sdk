## Sie4Sdk

The PHP Sie4 SDK and Sie5 conversion package

Using the Sie4 /Sie5 formats as prescribed at the [Sie] formats [page]

__*Sie4Sdk*__ supports convert/transform of accounting data from / to
- Sie4 : file / string / Dto / array / json
- Sie/SieEntry : XML / string / Dto


#### Usage

The Sie4EDto (Sie4E, export) usage corresponds to Sie4IDto (Sie4I, import) usage, described below. 

First, load a Sie4IDto instance (if not used as input)

```php
<?php
namespace Kigkonsult\Sie4Sdk;

use Kigkonsult\Sie4Sdk\Api\Array2Sie4Dto;
use Kigkonsult\Sie4Sdk\Api\Json2Sie4Dto;
use Kigkonsult\Sie5Sdk\XMLParse\Sie5Parser;

// Parse a Sie4 file/string into a Sie4IDto instance
$sie4IDto = Sie4Parser::factory()->process( $sie4I_string );
$sie4IDto = Sie4Parser::factory()->process( $sie4I_file );

// Transform a (HTTP, $_REQUEST) input array to a Sie4IDto instance
$sie4IDto = Array2Sie4Dto::process( $sie4I_array );

// Transform an input json string to a Sie4IDto instance
$sie4IDto = Json2Sie4Dto::process( $sie4I_json );

// Convert a SieEntry (Sie5) instance into a Sie4IDto instance
$sie4IDto = Sie4ILoader::factory( $sieEntry )->getSie4IDto();

// Transform a SieEntry (Sie5) XML into a Sie4IDto instance
$sieEntry = Sie5Parser::factory()->parseXmlFromString( $sieEntryXML );
$sie4Dto  = Sie4ILoader::factory( $sieEntry )->getSie4IDto();

// Transform a SieEntry (Sie5) XML file into Sie4IDto instance
$sieEntry = Sie5Parser::factory()->parseXmlFromFile( $sieEntryFile );
$sie4IDto = Sie4ILoader::factory( $sieEntry )->getSie4IDto();

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

use Kigkonsult\Sie4Sdk\Api\Sie4Dto2Array;
use Kigkonsult\Sie4Sdk\Api\Sie4Dto2Json;
use Kigkonsult\Sie5Sdk\XMLWrite\Sie5Writer;

// Write the Sie4IDto instance to a Sie4 string
$sie4IString = Sie4IWriter::factory()->process( $sie4Dto );

// Write the Sie4IDto instance to a Sie4 file
Sie4IWriter::factory()->process( $sie4Dto, $outputfile );

// Transform the Sie4IDto instance to an array
$sie4IArray  = Sie4Dto2Array::process( $sie4Dto );

// Transform the Sie4IDto instance to a json string
$sie4Ijson   = Sie4Dto2Json::process( $sie4Dto );

// Convert the Sie4IDto instance to a SieEntry (Sie5) instance
$sieEntry    = Sie5EntryLoader::factory( $sie4Dto )->getSieEntry();

// Transform the Sie4IDto instance to a SieEntry (Sie5) XML string
$sieEntry    = Sie5EntryLoader::factory( $sie4Dto )->getSieEntry();
$sieEntryXML = Sie5Writer::factory()->write( $sieEntry );

```

#### Info

__*Sie4Sdk*__ 
- require PHP7+
- uses kigkonsult\\[Sie5Sdk] for Sie5 Sie/SieEntry conversion and parse/write XML parts.
- the _Sie4_ input/output string/file is in PHP CP437 encding, IBM PC 8-bitars extended ASCII (Codepage 437),
all other PHP inbounding encoding (UTF-8)
- Each class properties corresponds to Sie4 label subfields, all with get-/is-set/set-methods,<br> 
  array properties also with add-/count-methods
- _Sie4Dto_, _VerDto_ and _TransDto_ are equipped with<br>
  unique time/guid properties - _timestamp_ (float) / _correlationId_ (string)<br>
  auto populated at instance create
- _VerDto_ and _TransDto_ 'inherit' _fnr_/_orgnr_ property values from _Sie4Dto_
- _TransDto_ 'inherit' _serie_/_vernr_ property values from _VerDto_, opt _transdat_ from regdatum 
- usefull constants are found in the Sie4Interface
- review mapping.txt for
  - Dto class and property structure
  - the _Sie4Dto_ - array mapping scheme<br>
  or review top of src/Api/Array2Sie4Dto.php file


To set up Sie4Sdk as a network service (using REST APIs, as a microservice etc), [Comet] is to recommend.


###### Sie4 - Sie/SieEntry comments

Note för GEN
* if _datum_ is missing, date 'now' is used
* if _sign_ is missing, (#PROGRAM) _programnamn_ is used

Note för VER
* if _verdatum_ is missing, date 'now' is used
* if _regdatum_ is missing, _verdatum_ is used
* if _sign_ is missing, GEN _sign_ is used

Note för TRANS
* skipped if _transdat_ is missing or equal with _verdatum_

Sie4 dates has format _YYYYmmdd_, Sie/SieEntry _YYYY-MM-DDThh:mm:ssZ_

The (Sie4) KSUMMA checksum is experimental.

Note for Sie4 - Sie/SieEntry conversion
* \#UNDERDIM is skipped
* Sie/SieEntry Journal id (and name) is set from #VER serie, if empty '' is set
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
include 'pathToSource/sie5sdk/autoload.php';
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
Donation using [paypal.me/kigkonsult] are appreciated.
For invoice, please [e-mail]</a>.


#### Support

For __*Sie4Sdk*__ support, please use [Github]/issues.

For Sie4/Sie5 ([XSD]) issues, go to [Sie] homepage.


#### License

This project is licensed under the LGPLv3 License


[Composer]:https://getcomposer.org/
[Comet]:https://github.com/gotzmann/comet
[DsigSdk]:https://github.com/iCalcreator/dsigsdk
[e-mail]:mailto:ical@kigkonsult.se
[Faker]:https://github.com/fzaninotto/Faker
[Github]:https://github.com/iCalcreator/Sie4Sdk/issues
[Sie5Sdk]:https://github.com/iCalcreator/SieSdk
[page]:https://sie.se/format/
[paypal.me/kigkonsult]:https://paypal.me/kigkonsult
[Sie]:http://www.sie.se
[XSD]:http://www.sie.se/sie5.xsd

[comment]: # (This file is part of Sie4Sdk, The PHP Sie4I SDK and Sie5 conversion package. Copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)
