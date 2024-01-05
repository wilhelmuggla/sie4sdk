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
namespace Kigkonsult\Sie4Sdk\Api\Server\ValidatorRules;

use Rakit\Validation\Rule;

use function is_array;
use function is_numeric;
use function sprintf;

/**
 * class Numeric2
 *
 * Check value is a two-dim array with numeric elements
 */
class Numeric2 extends Rule
{
    /**
     * The rule check
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool
    {
        static $err1   = " must be an array";
        static $err2   = "[%s] must be an array";
        static $err3   = "[%s][%s] must be numeric";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx1 => $value2 ) {
            if( ! is_array( $value2 )) {
                $this->message = sprintf( $err2, $vx1 );
                return false;
            }
            foreach( $value2 as $vx2 =>$value3 ) {
                if( ! is_numeric( $value3 )) {
                    $this->message = sprintf( $err3, $vx1, $vx2 );
                    return false;
                }
            } // end foreach
        } // end foreach
        return true;
    }
}