<?php
/**
 * Sie4Sdk   PHP Sie4 SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Sdk
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
declare( strict_types = 1 );
namespace Kigkonsult\Sie4Sdk\Util;

use InvalidArgumentException;

use function gettype;
use function is_int;
use function is_scalar;
use function is_string;
use function sprintf;
use function strlen;
use function substr;
use function trim;
use function var_export;

class Assert
{
    /**
     * Assert value is int or string value
     *
     * @param string $label
     * @param int|string $value
     * @return void
     */
    public static function isIntOrString( string $label, int | string $value ) : void
    {
        static $ERR = '%s expects int or string, got %s';
        if( ! ( is_int( $value ) || is_string( $value ))) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, gettype( $value )),
                3711
            );
        }
    }

    /**
     * Assert value is non positive int
     *
     * @param string $label
     * @param int|string $value
     * @return void
     * @throws InvalidArgumentException
     */
    public static function isNonPositiveInt( string $label, int | string $value ) : void
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
    public static function isIntegerish( string $label, int | string $value ) : void
    {
        static $ERR = '%s integer förväntas, nu %s';
        if( ! is_scalar( $value ) || ( $value != (int)$value )) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, var_export( $value, true )),
                3731
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
    public static function isYYYYMMDate( string $label, int | string $value ) : void
    {
        static $ONE = '01';
        static $ERR = '%s (#%d) YYYYMM-datum förväntas, nu %s';
        $value = trim( (string)$value );
        $yyxx  = (int) substr( $value, 0, 2 );
        if( (6 !== strlen( $value )) ||
            (19 > $yyxx ) ||
            (20 < $yyxx ) ||
            (12 < (int) substr( $value, 4, 2 ))) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, 1, var_export( $value, true )),
                3741
            );
        }
        try {
            DateTimeUtil::getDateTime( $value . $ONE, $label, 3742 );
        } catch( InvalidArgumentException $e ) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $label, 3, var_export( $value, true )),
                3743,
                $e
            );
        }
    }
}
