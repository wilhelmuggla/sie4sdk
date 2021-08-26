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
use Kigkonsult\Sie4Sdk\Dto\VerDto as Dto;

class VerDto
{
    /**
     * @param int[] $kontoNrs
     * @return Dto
     */
    public static function load( array $kontoNrs )
    {
        $faker = Faker\Factory::create();

        $dto = new Dto();

        static $serie = 0;
        $dto->setSerie( ++$serie );

        static $VERNR = null;
        if( empty( $VERNR )) {
            $VERNR = (int) date( 'Yz0000');
        }
        $dto->setVernr( ++$VERNR );

        $dateTime = new DateTime();
        if( 1 == $faker->randomElement( [ 1, 2 ] )) {
            $dateTime->modify( '-1 day' );
            $dto->setVerdatum( $dateTime );
        }
        if( 1 == $faker->randomElement( [ 1, 2 ] )) {
            $dateTime = clone $dateTime;
            $dto->setRegdatum( $dateTime->modify( '-1 day' ));
        }

        $dto->setVertext( $faker->words( 4, true ));

        $max         = $faker->numberBetween( 2, 7 );
        $kontoNrs2    = [];
        $balans      = 0;
        while( $max > count( $kontoNrs2 )) {
            $kontoNr = $faker->randomElement( $kontoNrs );
            if ( ! isset( $kontoNrs2[$kontoNr] )) {
                $kontoNrs2[$kontoNr] = $kontoNr;
                $transDto = TransDto::load((string) $kontoNr, $dateTime );
                $balans  += $transDto->getBelopp();
                $dto->addTransDto( $transDto );
            }
        } // end while
        $kontoNr = $faker->randomElement( $kontoNrs );
        while( isset($kontoNrs2[$kontoNr] )) {
            $kontoNr = $faker->randomElement( $kontoNrs );
        }
        $transDto = TransDto::load((string) $kontoNr, $dateTime );
        $transDto->setBelopp( 0 - $balans );
        $dto->addTransDto( $transDto );

        return $dto;
    }
}