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

use Faker\Generator;
use Kigkonsult\Sie4Sdk\Dto\AccountDto as Dto;

class AccountDto extends LoaderBase
{
    /**
     * @param Generator $faker
     * @param string $kontoNr
     * @return Dto
     * @since 1.8.3 2023-09-20
     */
    public static function load( Generator $faker, string $kontoNr ) : Dto
    {
        static $KTYPES = [ Dto::T, Dto::S, Dto::K, Dto::I ];
        static $UNITS  = [ 'st', 'kg', 'l' ];
        $dto   = new Dto();

        $dto->setKontoNr( $kontoNr );
        $dto->setKontoNamn( self::getRandomString( $faker, 3 ));

        if( 1 !== $faker->numberBetween( 1, 5 )) {
            $dto->setKontoTyp((string)$faker->randomElement($KTYPES));
        }

        if( 1 !== $faker->numberBetween( 1, 5 )) {
            $dto->setEnhet((string)$faker->randomElement($UNITS));
        }

        return $dto;
    }
}
