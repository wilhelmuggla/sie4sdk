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
namespace Kigkonsult\Sie4Sdk\DtoLoader;

use DateTime;
use Faker;
use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Dto\TransDto as Dto;
use Kigkonsult\Sie4Sdk\Util\StringUtil;

class TransDto
{
    /**
     * @param string $kontoNr
     * @param DateTime $dateTime
     * @return Dto
     */
    public static function load( string $kontoNr, DateTime $dateTime )
    {
        $faker = Faker\Factory::create();

        $dto = new Dto();

        $dto->setKontoNr( $kontoNr );

        static $DIMs    = [ 1, 2, 6, 7, 8, 9, 10 ];
        static $OBJECTs = [ '1', '2', '3', '4', '5' ];
        static $maxOpts = [ 0, 1, 2, 3 ];
        $max            = $faker->randomElement( $maxOpts );
        for( $x = 0; $x < $max; $x++ ) {
            $dto->addObjektlista(
                DimObjektDto::factoryDimObject(
                    $faker->randomElement( $DIMs ),
                    $faker->randomElement( $OBJECTs )
                )
            );
        } // end for

        $dto->setBelopp( $faker->randomFloat( 2, 1, 999999 ));

        static $theDaybefore = '-1 day';
        if( 1 == $faker->randomElement( [ 1, 2 ] )) {
            $transDat = clone $dateTime;
            $dto->setTransdat( $transDat->modify( $theDaybefore ));
        }

        static $Arr123 = [ 1, 2, 3 ];
        static $Arr012 = [ 0, 1, 2 ];
        switch( $faker->randomElement( $Arr123 )) {
            case 1 :
                $dto->setTranstext(
                    (string) $faker->words( $faker->randomElement( $Arr123 ), true )
                );
                break;
            case 2 :
                $dto->setTranstext(
                    str_pad(
                        StringUtil::$SP0,
                        ( $faker->randomElement( $Arr012 )),
                        StringUtil::$SP1
                    )
                );
                break;
            default :
                break;
        }

        if( 1 == $faker->randomElement( $Arr123 )) {
            $dto->setKvantitet( $faker->randomDigitNot( 0 ));
        }

        return $dto;
    }
}
