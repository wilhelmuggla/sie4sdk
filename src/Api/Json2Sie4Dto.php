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
namespace Kigkonsult\Sie4Sdk\Api;

use Exception;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;

use function json_decode;

/**
 * Class Json2Sie4Dto
 *
 * Transform input json string to any Sie4IDto (4E/4I)
 */
class Json2Sie4Dto
{
    /**
     * @param string $json
     * @return Sie4Dto
     * @throws InvalidArgumentException
     */
    public static function process( string $json ) : Sie4Dto
    {
        static $ERR1  = 'json string to array error, ';
        static $FLAGS = JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR;
        try {
            $sie4IArray = json_decode( $json, true, 512, $FLAGS );
        }
        catch( Exception $e ) {
            throw new InvalidArgumentException( $ERR1 . $e->getMessage(), 4001 );
        }
        if( ! is_array( $sie4IArray )) {
            throw new InvalidArgumentException( $ERR1 . json_last_error_msg(), 4002 );
        }
        return Array2Sie4Dto::process( $sie4IArray );
    }
}
