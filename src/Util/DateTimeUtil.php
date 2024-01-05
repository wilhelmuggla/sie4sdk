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

use DateTime;
use Exception;
use InvalidArgumentException;
use RuntimeException;

use function sprintf;
use function str_replace;
use function substr;

class DateTimeUtil
{
    /**
     * @var string
     */
    private static string $DASH = '-';

    /**
     * @var string
     */
    private static string $Y = 'Y';

    /**
     * @var string
     */
    private static string $M = 'm';

    /**
     * Return DateTime, loaded from input string
     *
     * @param string $dateTimeString
     * @param string $label
     * @param int    $errCode
     * @return DateTime
     * @throws RuntimeException
     */
    public static function getDateTime( string $dateTimeString, string $label, int $errCode ) : DateTime
    {
        static $FMT0 = '%s : %s, %s';
        try {
            $dateTime = new DateTime( $dateTimeString );
        }
        catch( Exception $e ) {
            throw new RuntimeException(
                sprintf( $FMT0, $label, $dateTimeString, $e->getMessage()),
                $errCode,
                $e
            );
        }
        return $dateTime;
    }

    /**
     * @param float $timestamp
     * @param int   $errCode
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertTimestamp( float $timestamp, int $errCode ) : void
    {
        static $TIMESTAMP = 'timestamp';
        static $AT        = '@';
        try {
            self::getDateTime( $AT . (int)$timestamp, $TIMESTAMP, $errCode );
        }
        catch( RuntimeException $e ) {
            throw new InvalidArgumentException( $e->getMessage(), ( $errCode + 1 ), $e );
        }
    }

    /**
     * Return "xsd:gYearMonth" from DateTime, format Y-m
     *
     * @param DateTime $dateTime
     * @return string
     */
    public static function gYearMonthFromDateTime( DateTime $dateTime ) : string
    {
        return $dateTime->format( self::$Y ) . self::$DASH . $dateTime->format( self::$M );
    }

    /**
     * Return "xsd:gYearMonth" from YYYYmm string, format Y-m
     *
     * @param string $yyyymm
     * @return string
     */
    public static function gYearMonthFromString( string $yyyymm ): string
    {
        return substr( $yyyymm, 0, 4 ) .
            self::$DASH .
            substr( $yyyymm, -2, 2 );
    }

    /**
     * Return YYYYmm string from "xsd:gYearMonth" string
     *
     * @param string $gYearMonth
     * @return string
     */
    public static function YYYYmmFromgYearMonth( string $gYearMonth ): string
    {
        return str_replace( self::$DASH, StringUtil::$SP0, $gYearMonth );
    }

    /**
     * Return start/end DateTime from "xsd:gYearMonth" string (YYYY-mm)
     *
     * DateTime start has day 1
     * DateTime end has last day in month
     *
     * @param string $gYearMonth
     * @param bool   $setEnd
     * @return DateTime
     * @throws RuntimeException

     */
    public static function gYearMonthToDateTime( string $gYearMonth, bool $setEnd ): DateTime
    {
        static $FIRST = '01';
        static $T     = 't';
        $year     = substr( $gYearMonth, 0, 4 );
        $month    = substr( $gYearMonth, -2, 2 );
        try {
            $dateTime = new DateTime( $year . $month . $FIRST );
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 19501, $e );
        }
        if( $setEnd ) {
            try {
            $dateTime->setDate(
                (int) $dateTime->format( self::$Y ),
                (int) $dateTime->format( self::$M ),
                (int) $dateTime->format( $T )
            );
            }
            catch( Exception $e ) {
                throw new RuntimeException( $e->getMessage(), 19502, $e );
            }
        }
        return $dateTime;
    }
}
