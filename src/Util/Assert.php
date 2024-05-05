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
namespace Kigkonsult\Sie4Sdk\Util;

use InvalidArgumentException;
use RuntimeException;

use function is_float;
use function is_int;
use function is_numeric;
use function is_string;
use function sprintf;
use function strlen;
use function substr;
use function trim;
use function var_export;

class Assert
{
    /**
     * Assert value is float, int or string (int/float)
     *
     * @param string $label
     * @param int|float|string $value
     * @return void
     * @throws InvalidArgumentException
     */
    public static function isfloatish( string $label, int|float|string $value ) : void
    {
        static $ERR = '%s float förväntas, nu %s';
        if( is_int( $value ) ||
            is_float( $value ) ||
            ( is_string( $value ) && is_numeric( $value ))) {
            return;
        }
        throw new InvalidArgumentException(
            sprintf( $ERR, $label, var_export( $value, true )),
            3711
        );
    }

    /**
     * Assert value is non positive int
     *
     * @param string $label
     * @param int|string $value
     * @return void
     * @throws InvalidArgumentException
     */
    public static function isNonPositiveInt( string $label, int|string $value ) : void
    {
        static $ERR = '%s integer <= 0 förväntas, nu %s';
        self::isIntegerish( $label, $value );
        if( 0 < (int)$value ) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, (int) $value ),
                3721
            );
        }
    }

    /**
     * Assert value is int or string integer
     *
     * @param string $label
     * @param int|string $value
     * @return void
     * @throws InvalidArgumentException
     */
    public static function isIntegerish( string $label, int|string $value ) : void
    {
        static $ERR = '%s integer förväntas, nu %s';
        if( $value != (int)$value ) { // Note !=
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, var_export( $value, true )),
                3731
            );
        }
    }

    /**
     * Assert value is a YYYY-date
     *
     * @param string $label
     * @param int|string $value
     * @return void
     * @throws InvalidArgumentException
     */
    public static function isYYYYDate( string $label, int|string $value ) : void
    {
        static $MMDD = '0101';
        static $ERR  = '%s (#%d) YYYY-datum förväntas, nu %s';
        $value  = trim((string) $value );
        $valueI = (int) $value;
        if(( 4 !== strlen( $value )) ||
            ( 1900 > $valueI ) ||
            ( 2099 < $valueI )) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, 1, var_export( $value, true )),
                3741
            );
        }
        try {
            DateTimeUtil::getDateTime( $value . $MMDD, $label, 3742 );
        }
        catch( RuntimeException $e ) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, 3, var_export( $value, true )),
                3743,
                $e
            );
        }
    }

    /**
     * Assert value is a YYYYMM-date
     *
     * @param string $label
     * @param int|string $value
     * @return void
     * @throws InvalidArgumentException
     */
    public static function isYYYYMMDate( string $label, int|string $value ) : void
    {
        static $ONE = '01';
        static $ERR = '%s (#%d) YYYYMM-datum förväntas, nu %s';
        $value = trim( (string)$value );
        $yyxx  = (int) substr( $value, 0, 2 );
        $mm    = (int) substr( $value, -2 );
        if( (6 !== strlen( $value )) ||
            (19 > $yyxx ) ||
            (20 < $yyxx ) ||
            (  0 === $mm ) ||
            ( 12 < $mm )) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, 1, var_export( $value, true )),
                3751
            );
        }
        try {
            DateTimeUtil::getDateTime( $value . $ONE, $label, 3752 );
        }
        catch( RuntimeException $e ) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, 3, var_export( $value, true )),
                3753,
                $e
            );
        }
    }
}
