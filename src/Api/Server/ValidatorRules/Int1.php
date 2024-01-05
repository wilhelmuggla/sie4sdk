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

use function filter_var;
use function is_array;
use function sprintf;

/**
 * class Int1
 *
 * Check value is an array with int elements
 */
class Int1 extends Rule
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
        static $err2   = "[%s] must be an int";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx => $value2 ) {
            if( false === filter_var( $value2, FILTER_VALIDATE_INT )) {
                $this->message = sprintf( $err2, $vx );
                return false;
            }
        } // end foreach
        return true;
    }
}