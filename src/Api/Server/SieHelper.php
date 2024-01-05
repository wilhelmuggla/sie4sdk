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

use Exception;
use Kigkonsult\Sie4Sdk\Api\Array2Sie4Dto;
use Kigkonsult\Sie4Sdk\Api\Sie4Dto2Json;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Sie4IWriter;
use Kigkonsult\Sie4Sdk\Sie4Validator;
use Monolog\Logger;

use function sprintf;
use function var_export;

/**
 * Class SieHelper
 *
 * Manages Sie4Sdk invokes
 *
 * @since 1.8.4 20230927
 */
class SieHelper
{
    /**
     * @var string
     */
    public static string $INVALIN = '#%s invalid input : %s';
    public static string $METHOD  = '_method';
    public static string $MSGFMT2 = '#%s %s';
    public static string $PATH    = '_path';

    /**
     * Opt Monolog\Logger
     *
     * @var Logger|null
     */
    public static ? Logger $logger = null;

    /**
     * Return false on error or Sie4Dto, manages the Array2Sie4Dto::process() invoke
     *
     * @param string[] $array
     * @param string[] $serverParams
     * @param int $errNo
     * @param string|null $msg
     * @return bool|Sie4Dto
     */
    public static function array2Sie4Dto( array $array, array $serverParams, int $errNo, ?string &$msg ): bool|Sie4Dto
    {
        try {
            return Array2Sie4Dto::process( $array );
        }
        catch( Exception $e ) {
            $msg = sprintf( self::$MSGFMT2, $errNo, $e->getMessage());
            self::$logger?->error( self::getErrMsg( $serverParams, $msg ));
            return false;
        }
    }

    /**
     * Return false on error or Sie4 string
     *
     * Manages the Array2Sie4Dto::process(), AssertSie4IDto() and Sie4IWriter::factory()->process() invokes
     *
     * @param string[] $serverParams
     * @param string[]|string[][] $array
     * @param int $errNo
     * @param string|null $msg
     * @return bool|string
     */
    public static function array2Sie4Dto2String(
        array $serverParams,
        array $array,
        int $errNo,
        ? string &$msg
    ): bool|string
    {
        if(( false === ( $sie4Dto = self::array2Sie4Dto( $array, $serverParams, $errNo + 5, $msg ))) || // X5
            ( false === self::assertSie4IDto( $sie4Dto, $serverParams, $errNo + 6, $msg ))) {           // x6
            return false;
        }
        try {
            $output = Sie4IWriter::factory()->process( $sie4Dto );
        }
        catch( Exception $e ) {
            $msg = sprintf( self::$MSGFMT2, $errNo + 7, $e->getMessage()); // X7
            self::$logger?->error( self::getErrMsg( $serverParams, $msg ));
            return false;
        }
        return $output;
    }

    /**
     * Assert input is a non-empty array
     *
     * @param mixed $input
     * @param string[] $serverParams
     * @param int $errNo
     * @param string|null $msg
     * @return bool
     */
    public static function assertNonEmptyArray( mixed $input, array $serverParams, int $errNo, ?string &$msg ): bool
    {
        if ( empty( $input ) || !is_array( $input )) {
            $msg = sprintf( self::$INVALIN, $errNo, var_export( $input, true ));
            self::$logger?->error( self::getErrMsg( $serverParams, $msg ));
            return false;
        }
        return true;
    }

    /**
     * Manages the Sie4Validator::assertSie4IDto() invoke
     *
     * @param Sie4Dto $sie4Dto
     * @param string[] $serverParams
     * @param int $errNo
     * @param string|null $msg
     * @return bool
     */
    public static function assertSie4IDto( Sie4Dto $sie4Dto, array $serverParams, int $errNo, ?string &$msg ): bool
    {
        static $FMT = '#%s(%s) %s';
        try {
            Sie4Validator::assertSie4IDto( $sie4Dto );
        }
        catch( Exception $e ) {
            $msg = sprintf( $FMT, $errNo, $e->getCode(), $e->getMessage());
            self::$logger?->error( self::getErrMsg( $serverParams, $msg ));
            return false;
        }
        return true;
    }

    /**
     * Return rendered error msg string with time, opt remote invoker, msg etc
     *
     * @param string[] $serverParams
     * @param string $msg
     * @return string
     */
    public static function getErrMsg( array $serverParams, string $msg ): string
    {
        static $SP1 = ' ';
        static $C   = 'c';
        static $REQUEST_TIME_FLOAT = 'REQUEST_TIME_FLOAT';
        static $REMOTE_ADDR        = 'REMOTE_ADDR';
        $output = ! empty( $serverParams[$REQUEST_TIME_FLOAT] ) ? $_SERVER[$REQUEST_TIME_FLOAT] : date( $C );
        if( ! empty( $serverParams[$REMOTE_ADDR] )) {
            $output .= $SP1 . $serverParams[$REMOTE_ADDR];
        }
        $output .= $SP1 . $serverParams[self::$METHOD];
        $output .= $SP1 . $serverParams[self::$PATH];
        return $output . $SP1 . $msg;
    }

    /**
     * Return false or error or Sie4Dto as json string
     *
     * @param Sie4Dto   $sie4Dto
     * @param string[]  $serverParams
     * @param int       $errNo
     * @param string|null $msg
     * @return bool|string
     */
    public static function sie4Dto2Json(
        Sie4Dto $sie4Dto,
        array $serverParams,
        int $errNo,
        ? string & $msg
    ): bool|string
    {
        try {
            return Sie4Dto2Json::process( $sie4Dto );
        }
        catch( Exception $e ) {
            $msg = sprintf( self::$MSGFMT2, $errNo, $e->getMessage());
            self::$logger?->error( self::getErrMsg( $serverParams, $msg ));
            return false;
        }
    }
}
