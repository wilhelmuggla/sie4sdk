<?php
/**
 * Sie4Sdk   PHP Sie4 SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Sdk
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult, <ical@kigkonsult.se>
 * @copyright 2021-2024 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
declare( strict_types = 1 );
namespace Kigkonsult\Sie4Sdk\Api\Server;

use Comet\Request;
use Comet\Response;
use Comet\Utils;
use Exception;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Monolog\Logger;
use Psr\Http\Message\StreamInterface;

use function date_create;
use function json_decode;
use function sprintf;

/**
 * class SieController
 *
 * Manages services, Sie4 services found in SieHelper
 *
 * @since 1.8.4 20230927
 */
class SieController implements Sie4Interface
{
    private static string $INVOKED = ' invoked ';

    /**
     * Opt Monolog\Logger
     *
     * @var Logger|null
     */
    private static ? Logger $logger = null;

    /**
     * Class constructor
     *
     * @param Logger|null $logger
     */
    public function __construct( ? Logger $logger = null )
    {
        if( $logger ) {
            self::$logger      = $logger;
            SieHelper::$logger = $logger;
        }
    }

    /**
     * Return callable for controller service route
     *
     * '$this' required for PHP post-8.0 for non-static callables
     *
     * @param string $route
     * @return callable
     */
    public function getCallable( string $route ) : callable
    {
        return match ( $route ) {
            '/get'         => [ $this, 'get' ],
            '/getJson'     => [ $this, 'getJson' ],
            '/getFromJson' => [ $this, 'getFromJson' ],
            default        => [ $this, 'test' ],
        };
    }

    /**
     * @param Request $request
     * @return string[]
     */
    private static function getServerParams( Request $request ) : array
    {
        $output = $request->getServerParams();
        $output[SieHelper::$METHOD] = $request->getMethod();
        $output[SieHelper::$PATH]   = $request->getUri()->getPath();
        return $output;
    }

    /**
     * Method GET, accepts http query string Sie4 array, output sie4 string
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function get( Request $request, Response $response ): Response
    {
        self::$logger?->debug( __METHOD__ . self::$INVOKED );
        $serverParams = self::getServerParams( $request );
        $input        = $request->getQueryParams();
        if( false === SieHelper::assertNonEmptyArray( $input, $serverParams, 11, $msg )) {
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 500 );
        }
        if( false === Sie4Validator::validateArray( $input, $msg )) {
            self::$logger?->error( SieHelper::getErrMsg( $serverParams, $msg ));
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 400 );
        }
        if( false === ( $output = SieHelper::array2Sie4Dto2String( $serverParams, $input, 10, $msg ))) {  // 15-7
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 400 );
        }
        return $response->withBody( Utils::streamFor( $output ))
            ->withStatus( 200 );
    }

    /**
     * Method GET, accepts http query string Sie4 array, returns Sie json string
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getJson( Request $request, Response $response ) : Response
    {
        $CONTENT_TYPE     = 'Content-Type';
        $APPLICATION_JSON = 'application/json';
        $serverParams     = self::getServerParams( $request );
        self::$logger?->debug(__METHOD__ . self::$INVOKED);
        $input = $request->getQueryParams();
        if( false === SieHelper::assertNonEmptyArray( $input, $serverParams, 21, $msg )) {
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 500 );
        }
        if( false === Sie4Validator::validateArray( $input, $msg )) {
            self::$logger?->error( SieHelper::getErrMsg( $serverParams, $msg ));
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 400 );
        }
        if(( false === ( $sie4Dto = SieHelper::array2Sie4Dto( $input, $serverParams, 23, $msg ))) ||
            ( false === SieHelper::assertSie4IDto( $sie4Dto, $serverParams, 24, $msg )) ||
            ( false === ( $json = SieHelper::sie4Dto2Json( $sie4Dto, $serverParams, 24, $msg )))) {
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 400 );
        }
        return $response->withBody( Utils::streamFor( $json ))
            ->withHeader( $CONTENT_TYPE, $APPLICATION_JSON )
            ->withStatus( 200 );
    }

    /**
     * Method POST, accepts Sie json string, returns Sie string
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getFromJson( Request $request, Response $response ) : Response
    {
        self::$logger?->debug( __METHOD__ . self::$INVOKED );
        $serverParams = self::getServerParams( $request );
        if( false === ( $input = self::getPostStringBody( $serverParams, $request->getBody(), $msg ))) { // err 31
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 500 );
        }
        if( false === ( $array = self::jsonDecode( $serverParams, $input, $msg ))) { // err 32
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 400 );
        }
        if( false === Sie4Validator::validateArray( $array, $msg )) {
            self::$logger?->error( SieHelper::getErrMsg( $serverParams, $msg ));
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 400 );
        }
        if( false === ( $output = SieHelper::array2Sie4Dto2String( $serverParams, $array, 30, $msg ))) { // 35-7
            return $response->withBody( Utils::streamFor( $msg ))
                ->withStatus( 400 );
        }
        return $response->withBody( Utils::streamFor( $output ))
            ->withStatus( 200 );
    }

    /**
     * @param null|StreamInterface $body
     * @param mixed[] $serverParams
     * @param string|null $msg
     * @return false|string
     */
    private static function getPostStringBody( array $serverParams, ? StreamInterface $body, ? string & $msg ) : false|string
    {
        static $ERR = '#%s empty body';
        if( null === $body ) {
            $msg    = sprintf( $ERR, 31 );
            self::$logger?->error( SieHelper::getErrMsg( $serverParams, $msg ));
            return false;
        }
        $body->rewind();
        return $body->getContents();
    }

    /**
     * @param mixed[] $serverParams
     * @param string $input
     * @param string|null $msg
     * @return false|string[]
     */
    private static function jsonDecode( array $serverParams, string $input, ? string & $msg ) : false|array
    {
        static $OPTS = JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR;
        try {
            $array   = json_decode( $input, true, 512, $OPTS );
        }
        catch( Exception $e ) {
            $msg     = sprintf( SieHelper::$MSGFMT2, 32, $e->getMessage());
            self::$logger?->error( SieHelper::getErrMsg( $serverParams, $msg ));
            return false;
        }
        return $array;
    }
    /**
     * Method GET, check the app is up, returns string, name + version + timestamp
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function test( Request $request, Response $response ) : Response
    {
        static $FMT = '%s %s %s';
        static $Uu  = 'Uu';
        static $REQUEST_TIME_FLOAT = 'REQUEST_TIME_FLOAT';
        self::$logger?->debug( __METHOD__ . self::$INVOKED );
        $serverParams = $request->getServerParams();
        $timestamp    = $serverParams[$REQUEST_TIME_FLOAT] ?? date_create()->format( $Uu );
        return $response->withBody(
            Utils::streamFor(
                sprintf( $FMT, self::PRODUCTNAME, self::PRODUCTVERSION, $timestamp )
            )
        )
            ->withStatus( 200 );
    }
}
