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

use DateTime;
use Faker\Generator;
use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Dto\TransDto as Dto;
use Kigkonsult\Sie4Sdk\Util\StringUtil;

class TransDto extends LoaderBase
{
    /**
     * @param Generator $faker
     * @param string $kontoNr
     * @param DateTime $dateTime
     * @param float $belopp
     * @param int[] $dimObjectNrs
     * @return Dto
     * @since 1.8.3 2023-09-20
     */
    public static function load(
        Generator $faker,
        string $kontoNr,
        DateTime $dateTime,
        float $belopp,
        array $dimObjectNrs
    ) : Dto
    {
        static $Arr012  = [ 0, 1, 2 ];
        static $maxOpts = [ 0, 0, 1, 2, 3 ];
        static $theDaybefore = '-1 day';
        static $Ymd     = 'Y-m-d';

        $transText = ( 1 === $faker->randomElement( self::$Arr123 ))
            ? self::getRandomString( $faker, self::$Arr123 )
            : str_pad(
                StringUtil::$SP0,
                (int) ( $faker->randomElement( $Arr012 )),
                StringUtil::$SP1
            );

        $dto = new Dto();
        $dto->setKontoNr( $kontoNr );
        $dto->setBelopp( $belopp );
        $dto->setTranstext( $transText );

        if( ! empty( $dimObjectNrs )) {
            $max = $faker->randomElement( $maxOpts );
            for( $x = 0; $x < $max; $x++ ) {
                $dimNr = $faker->randomElement( array_keys( $dimObjectNrs ) );
                $dto->addObjektlista(
                    DimObjektDto::factoryDimObject(
                        (int) $dimNr,
                        (string) $faker->randomElement( $dimObjectNrs[ $dimNr ] )
                    )
                );
            } // end for
        }

        if( 1 === $faker->randomElement( self::$Arr12 )) {
            $date1 = ( clone $dateTime )->modify( $theDaybefore );
            $date2 = ( 1 === $faker->randomElement( self::$Arr12 )) ? $date1 : $date1->format( $Ymd );
            $dto->setTransdat( $date2 );
        }


        if( ! empty( $dimObjectNrs ) && ( 1 === $faker->randomElement( self::$Arr123 ))) {
            $dto->setKvantitet( $faker->randomDigitNot( 0 ));
        }

        return $dto;
    }
}
