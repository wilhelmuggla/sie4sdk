## Sie4Sdk

#### Usage

Simple Sie4 import (file-)string __create__ :
```php
<?php
namespace Kigkonsult\Sie4Sdk;

use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;

$sie4String = Sie4IWriter::factory()->process(
    Sie4Dto::factory( 'Acme Corp', '123', '556334-3689' )
        ->addVerDto(
            VerDto::factory( 123, 'Porto' )
                ->addTransKontoNrBelopp( 1910, -2000.00 )
                ->addTransKontoNrBelopp( 2640, 400.00 )
                ->addTransKontoNrBelopp( 6250, 1600.00 )
            )
);
```

__Parse__ Sie-source

The Sie4EDto (Sie4E, export) usage corresponds to Sie4IDto (Sie4I, import) usage, described below. 

First, load a Sie4IDto instance (if not used as input)

```php
<?php
namespace Kigkonsult\Sie4Sdk;

use Kigkonsult\Sie4Sdk\Api\Array2Sie4Dto;
use Kigkonsult\Sie4Sdk\Api\Json2Sie4Dto;
use Kigkonsult\Sie5Sdk\XMLParse\Sie5Parser;

// Load resource
$sie4I_string = ...
// Parse a Sie4 string (or file) into a Sie4IDto instance
$sie4IDto = Sie4Parser::factory()->process( $sie4I_string );
// $sie4IDto = Sie4Parser::factory()->process( $sie4I_file );


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
Array Sie4I input, single #VER with three $TRANS (same data as above)
```php
<?php
namespace Kigkonsult\Sie4Sdk;

use Kigkonsult\Sie4Sdk\Api\Array2Sie4Dto;

$sie4I_array = [
    Sie4IDto::FTGNAMN            => 'Acme Corp',
    Sie4IDto::ORGNRORGNR         => '556334-3689',
    Sie4IDto::VERDATUM           => [ date( 'Ymd' ) ],
    Sie4IDto::VERNR              => [ 123 ],
    Sie4IDto::VERTEXT            => [ 'Porto' ],
    Sie4IDto::TRANSKONTONR       => [ [  1910, 2640, 6250 ] ],
    Sie4IDto::TRANSBELOPP        => [ [ -2000,  400, 1600 ] ]
];

$sie4IDto = Array2Sie4Dto::process( $sie4I_array );
.. .
```

Url-encoded Sie4I query input (ex from `$_SERVER['QUERY_STRING']`), single #VER with three $TRANS (same data as above)
```php
<?php
namespace Kigkonsult\Sie4Sdk;

use Kigkonsult\Sie4Sdk\Api\Array2Sie4Dto;

$sie4I_query = 'FNAMN=Acme+Corp&ORGNRORGNR=556334-3689&VERDATUM%5B0%5D=20230920&VERNR%5B0%5D=123&VERTEXT%5B0%5D=Porto&TRANSKONTONR%5B0%5D%5B0%5D=1910&TRANSKONTONR%5B0%5D%5B1%5D=2640&TRANSKONTONR%5B0%5D%5B2%5D=6250&TRANSBELOPP%5B0%5D%5B0%5D=-2000&TRANSBELOPP%5B0%5D%5B1%5D=400&TRANSBELOPP%5B0%5D%5B2%5D=1600';

parse_url( $sie4I_query, $array );
$sie4IDto = Array2Sie4Dto::process( $array );
.. .
```

Json Sie4I input, single #VER with three $TRANS (same data as above)
```php
<?php
namespace Kigkonsult\Sie4Sdk;

use Kigkonsult\Sie4Sdk\Api\Json2Sie4Dto;

$sie4I_json = '{"ORGNRORGNR":"556334-3689","FNAMN":"Acme Corp","VERNR":[123],"VERDATUM":["20230920"],"VERTEXT":["Porto"],"TRANSKONTONR":[["1910","2640","6250"]],"TRANSBELOPP":[["-2000.00","400.00","1600.00"]]}';

$sie4IDto = Json2Sie4Dto::process( $sie4I_json );
.. .
```

Second, __validate__ the Sie4IDto instance

```php
<?php
namespace Kigkonsult\Sie4Sdk;

// Assert mandatory sie4Dto properties
Sie4Validator::assertSie4IDto( $sie4Dto );
```


__Format__ Sie-output


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

// Transform the Sie4IDto array to a URL-encoded query string
// $queryString = http_build_query( $sie4IArray )

// Transform the Sie4IDto instance to a json string
$sie4Ijson   = Sie4Dto2Json::process( $sie4Dto );

// Convert the Sie4IDto instance to a SieEntry (Sie5) instance
$sieEntry    = Sie5EntryLoader::factory( $sie4Dto )->getSieEntry();

// Transform the Sie4IDto instance to a SieEntry (Sie5) XML string
$sieEntry    = Sie5EntryLoader::factory( $sie4Dto )->getSieEntry();
$sieEntryXML = Sie5Writer::factory()->write( $sieEntry );
```

#### Network service
To set up Sie4Sdk as a network service
(using REST APIs, as a microservice etc),
explore the src/Api/Server directory and [sie4Server.php] script
where [Comet] middleware is used. 
The service accepts Sie string array / json input and return Sie (file) string / json.



#### Info

__Sie__
- more [Sie] info and formats [page] here.

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
- _VerDto_ and _TransDto_ 'inherit' _fnr_ / _orgnr_ property values from _Sie4Dto_
- _TransDto_ 'inherit' _serie_ / _vernr_ property values from _VerDto_, opt _transdat_ from regdatum 
- usefull constants are found in the Sie4Interface
- review mapping.txt for
  - Dto class and property structure
  - the _Sie4Dto_ - array mapping scheme<br>
  or review top of src/Api/Array2Sie4Dto.php file


###### Sie4 - Sie/SieEntry comments

Note för GEN
* if _datum_ is missing, date 'now' is used
* if _sign_ is missing, (#PROGRAM) _programnamn_ is used

Note för VER
* if _verdatum_ is missing, date 'now' is used
* if _regdatum_ is missing, _verdatum_ is used
* if _sign_ is missing, GEN _sign_ is used

Note för TRANS
* _transdat_ skipped if missing or equal with _verdatum_

Sie4 dates has format _YYYYmmdd_, Sie/SieEntry _YYYY-MM-DDThh:mm:ssZ_

The (Sie4) KSUMMA checksum is experimental.

Note for Sie4 - Sie/SieEntry conversion
* \#UNDERDIM is skipped
* Sie/SieEntry Journal id (and name) is set from #VER serie, if empty '' is set
* \#IB, #UB, #OIB, #OUB and #RES skipped due to month/period not exists
* \#PSALDO goes into the AccountTypes as CLOSINGBALANCE, only arsnr 0 accepted
* \#PBUDGET goes into the AccountTypes as BUDGET, only arsnr 0 accepted

[Comet]:https://github.com/gotzmann/comet
[page]:https://sie.se/format/
[Sie]:http://www.sie.se
[sie4Server.php]:src/Api/Server/sie4Server.php
[Sie5Sdk]:https://github.com/iCalcreator/SieSdk

[comment]: # (This file is part of Sie4Sdk, The PHP Sie4I SDK and Sie5 conversion package. Copyright 2021-2024 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)
