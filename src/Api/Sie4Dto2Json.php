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
namespace Kigkonsult\Sie4Sdk\Api;

use Exception;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;

class Sie4Dto2Json
{
    /**
     * Transform any Sie4Dto (4E/4I) to json string
     *
     * Assert input Sie4IDto
     *
     * @param Sie4Dto $sie4IDto
     * @return string
     * @throws InvalidArgumentException
     */
    public static function process( Sie4Dto $sie4IDto ) : string
    {
        static $ERR1 = 'array to json string error, ';
        static $FLAGS = JSON_THROW_ON_ERROR;
        try {
            $string = json_encode( Sie4Dto2Array::process( $sie4IDto ), $FLAGS );
        }
        catch( Exception $e ) {
            throw new InvalidArgumentException( $ERR1 . $e->getMessage(), 7001 );
        }
        if( false === $string ) {
            throw new InvalidArgumentException( $ERR1 . json_last_error_msg(), 7002 );
        }
        return $string;
    }
}
