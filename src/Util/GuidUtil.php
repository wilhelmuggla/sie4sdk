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

use Exception;
use InvalidArgumentException;

use function function_exists;
use function preg_match;
use function random_int;
use function sprintf;
use function strlen;

class GuidUtil
{

    /**
     * @link https://stackoverflow.com/questions/19989481/how-to-determine-if-a-string-is-a-valid-v4-uuid
     * @param string $guid
     * @param int    $errCode
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertGuid( string $guid, int $errCode ) : void
    {
        static $FMT3 = 'Ogiltig guid : ';
        static $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        if( 36 !== strlen( $guid )) {
            throw new InvalidArgumentException( $FMT3 . $guid, $errCode );
        }
        if( 1 !== preg_match( $UUIDv4, $guid )) {
            throw new InvalidArgumentException( $FMT3 . $guid, ($errCode + 1));
        }
    }

    /**
     * Return guid (without surrounding brackets)
     *
     * @link https://stackoverflow.com/questions/21671179/how-to-generate-a-new-guid#26163679
     * @return string
     * @throws Exception
     */
    public static function getGuid(): string
    {
        static $FUNCTION = 'com_create_guid';
        static $FMTGUID = '%04X%04X-%04X-%04X-%04X-%04X%04X%04X';
        return ( true === function_exists( $FUNCTION ))
            ? StringUtil::trimBrackets( $FUNCTION())
            : sprintf(
                $FMTGUID,
                random_int( 0, 65535 ),
                random_int( 0, 65535 ),
                random_int( 0, 65535 ),
                random_int( 16384, 20479 ),
                random_int( 32768, 49151 ),
                random_int( 0, 65535 ),
                random_int( 0, 65535 ),
                random_int( 0, 65535 )
            );
    }
}
