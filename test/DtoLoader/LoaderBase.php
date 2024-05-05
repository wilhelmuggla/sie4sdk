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
namespace Kigkonsult\Sie4Sdk\DtoLoader;

use Faker\Factory;
use \Faker\Generator;
use Kigkonsult\Sie4Sdk\Dto\DtoInterface;

/**
 * abstract class LoaderBase
 *
 * @since 1.8.3 2023-09-20
 */
abstract class LoaderBase implements DtoInterface
{
    protected static array $Arr12  = [ 1, 2 ];
    protected static array $Arr123 = [ 1, 2, 3 ];

    /**
     * Return a random belopp
     * @param Generator $faker
     * @return float
     */
    protected static function getRandomBelopp( Generator $faker ) : float
    {
        $belopp = $faker->randomFloat( 2, 1, 999999 );
        if( 1 === $faker->randomElement( self::$Arr123 )) {
            $belopp *= -1;
        }
        return $belopp;
    }

    /**
     * Return a random belopp
     * @param Generator $faker
     * @return float
     */
    protected static function getRandomString( Generator $faker, null|int|array $len = null ) : string
    {
        $len = match( true ) {
            ( null == $len ) => $faker->randomElement( self::$Arr123 ),
            is_array( $len ) => $faker->randomElement( $len ),
            default => $len
        };
        return (string) $faker->words( $len, true );
    }
}
