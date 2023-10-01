<?php
/**
 * Sie4Sdk   PHP Sie4 SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Sdk
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021-2023 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software Sie4Sdk.
 *            The above package, copyright, link and this licence notice shall be
 *            included in all copies or substantial portions of the Sie4Sdk.
 *
 *            Sie4Sdk is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            Sie4Sdk is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with Sie4Sdk. If not, see <https://www.gnu.org/licenses/>.
 */
/**
 * Simpler Sie4 Comet server,
 * offer conversion from array/json input to Sie4 string output.
 * The input requires at least one Sie '#VER', ex. the result from an 'online' purchase
 *
 * Requires composer update : "gotzmann/comet": "^2.4" and, opt for logger, "monolog/monolog": "^2.5"
 *
 * accepts
 * - [host][:port][basePart]/get          Method GET, accepts http query string Sie4 array, output sie4 string
 *
 *  testing using a browser (or 'Postman') with url
 * 'http://127.0.0.1:8081/api/v1/get?FNAMN=Acme+Corp&ORGNRORGNR=556334-3689&VERDATUM[0]=20230920&VERNR[0]=123&VERTEXT[0]=Porto&TRANSKONTONR[0][0]=1910&TRANSKONTONR[0][1]=2640&TRANSKONTONR[0][2]=6250&TRANSBELOPP[0][0]=-2000&TRANSBELOPP[0][1]=400&TRANSBELOPP[0][2]=1600'
 *  returns a Sie4 string
 *
 * - [host][:port][basePart]/getJson      Method GET, accepts http query string Sie4 array, returns Sie json string
 *
 *  testing using a browser (or 'Postman') with url
 * 'http://127.0.0.1:8081/api/v1/getJson?FNAMN=Acme+Corp&ORGNRORGNR=556334-3689&VERDATUM[0]=20230920&VERNR[0]=123&VERTEXT[0]=Porto&TRANSKONTONR[0][0]=1910&TRANSKONTONR[0][1]=2640&TRANSKONTONR[0][2]=6250&TRANSBELOPP[0][0]=-2000&TRANSBELOPP[0][1]=400&TRANSBELOPP[0][2]=1600'
 *  returns a Sie4 json string
 *
 * - [host][:port][basePart]/getFromJson  Method POST, accepts Sie json string, returns Sie string
 *
 *  testing using, ('Postman' or) in a command window (and installed 'sudo yum install httpie')
 *  'http 127.0.0.1:8081/api/v1/getFromJson @Sie.json'
 *  and with the 'Sie.json' file content
 *  '{"ORGNRORGNR":"556334-3689","FNAMN":"Acme Corp","VERNR":[123],"VERDATUM":["20230920"],"VERTEXT":["Porto"],"TRANSKONTONR":[["1910","2640","6250"]],"TRANSBELOPP":[["-2000.00","400.00","1600.00"]]}'
 *  returns a Sie4 string
 *
 * - [host][:port][basePart]/test         Method GET, check the app is up, returns string, name + version + timestamp
 *
 *  testing using a browser (or 'Postman') with url
 * 'http://127.0.0.1:8081/api/v1/test'
 *  returns string with product name and version and the current timestamp
 *
 * Start server in a command window (in the 'local SieSdk' dir)
 * > sudo php src/Api/Server/server.php start
 *
 * @since 1.8.4 20230927
 */
declare( strict_types = 1 );
namespace Kigkonsult\Sie4Sdk\Api\Server;

use Comet\Comet;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * config
 */
$config = [
    'host' => '127.0.0.1',
//  'host' => '192.168.1.217',
    'port' => 8081,
];

// $logger = null; // opt
// or...
$format     = "\n%datetime% %level_name% %message%";
$dateFormat = "Y-m-d H:i:s.u";
$formatter  = new LineFormatter( $format, $dateFormat );
$logDirFile = __DIR__ . '/../../../log/app.log';
$logLevel   = Logger::DEBUG;
$stream     = new StreamHandler( $logDirFile, $logLevel );
$stream->setFormatter( $formatter );
$logger     = new Logger('comet' );
$logger->pushHandler( $stream );
if( $logger ) {
    $config['debug']  = true;
    $config['logger'] = $logger;
}

/**
 * set up
 */
$comet         = new Comet( $config );
$comet->setBasePath( "/api/v1" );
$sieController = new SieController( $logger );

// Method GET, accepts http query string Sie4 array, output sie4 string
$comet->get( '/get', $sieController->getCallable( '/get' ));

// Method GET, accepts http query string Sie4 array, returns Sie json string
$comet->get( '/getJson', $sieController->getCallable( '/getJson' ));

// Method POST, accepts Sie json string, returns Sie string
$comet->post( '/getFromJson', $sieController->getCallable( '/getFromJson' ));

// Method GET, check the app is up, returns string, name + version + timestamp
$comet->get( '/test', $sieController->getCallable( '/test' ));

/**
 * run
 */
$comet->run();
