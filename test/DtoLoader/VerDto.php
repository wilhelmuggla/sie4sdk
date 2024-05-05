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
use Kigkonsult\Sie4Sdk\Dto\VerDto as Dto;

class VerDto extends LoaderBase
{
    /**
     * @param Generator $faker
     * @param int[] $kontoNrs
     * @param int[] $dimObjectNrs
     * @return Dto
     * @since 1.8.3 2023-09-20
     */
    public static function load( Generator $faker, array $kontoNrs, array $dimObjectNrs ) : Dto
    {
        static $theDaybefore = '-1 day';
        static $VerNrBase    = 'Yz0000';
        static $Ymd          = 'Y-m-d';
        static $arr1234      = [ 1, 2, 3, 4 ];
        static $SERIE        = 1;
        static $VERNR        = null;

        if( empty( $VERNR )) {
            $VERNR = (int) date( $VerNrBase );
        }
        $verText  = self::getRandomString( $faker, 4 );
        $verDatum = ( 1 === $faker->randomElement( self::$Arr12 ))
            ? new DateTime()
            : ( new DateTime())->format( $Ymd );

        if( 1 === $faker->randomElement( self::$Arr12 )) {
            $dto   = new Dto();
            $dto->setSerie( $SERIE );
            $dto->setVernr( ++$VERNR );
            $dto->setVerdatum( $verDatum );
            $dto->setVertext( $verText );
        }
        else {
            $dto   = Dto::factory( ++$VERNR, $verText, $verDatum );
            $dto->setSerie( $SERIE );
        }
        if( 1 === $faker->randomElement( $arr1234 )) {
            ++$SERIE;
        }

        $dateTime = new DateTime();
        if( 1 === $faker->randomElement( self::$Arr12 )) {
            $date1 = $dateTime->modify( $theDaybefore );
            $date2 = ( 1 === $faker->randomElement( self::$Arr12 )) ? $date1 : $date1->format( $Ymd );
            $dto->setRegdatum( $date2 );
        }
        $dto->setSign( $faker->randomLetter() . $faker->randomDigitNotNull());

        $max       = $faker->numberBetween( 2, 7 );
        $kontoNrs2 = [];
        $balans    = 0;
        while( $max > count( $kontoNrs2 )) {
            $kontoNr               = $faker->randomElement( $kontoNrs );
            $kontoNrs2[ $kontoNr ] = $kontoNr;
            $belopp = self::getRandomBelopp( $faker );
            if( 1 === $faker->randomElement( self::$Arr123 )) {
                $dto->addTransKontoNrBelopp( $kontoNr, $belopp );
                $balans  += $belopp;
            }
            else {
                $transDto = TransDto::load( $faker, $kontoNr, $dateTime, $belopp, $dimObjectNrs );
                $balans  += $transDto->getBelopp();
                $dto->addTransDto( $transDto );
            }
        } // end while
        $kontoNr = $faker->randomElement( $kontoNrs );
        while( isset($kontoNrs2[$kontoNr] )) {
            $kontoNr = $faker->randomElement( $kontoNrs );
        }
        $transDto = TransDto::load( $faker, $kontoNr, $dateTime, ( 0 - $balans ), [] );
        $dto->addTransDto( $transDto );

        return $dto;
    }
}
