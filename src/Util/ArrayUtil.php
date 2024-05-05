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

use function array_pad;
use function count;

class ArrayUtil
{
    /**
     * Assure array has keyed (array) elements
     *
     * @param mixed[] $array
     * @param string|string[] $key
     * @return void
     */
    public static function assureIsArray( array & $array, array|string $key ) : void
    {
        foreach((array) $key as $k ) {
            if( ! isset( $array[$k] )) {
                $array[$k] = [];
            }
        }
    }

    /**
     * Assure array has a number of elements
     *
     * @param string[]  $array
     * @param int       $length
     * @return void
     */
    public static function assureArrayLength( array & $array, int $length ) : void
    {
        if( $length > count( $array )) {
            $array = array_pad( $array, $length, null );
        }
    }

    /**
     * Add trailing eol to each array element
     *
     * @param string[] $array
     * @return string[]
     */
    public static function eolEndElements( array $array ) : array
    {
        $output = [];
        foreach( $array as $key => $value ) {
            $output[$key] = $value . PHP_EOL;
        }
        return $output;
    }
}
