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
use Kigkonsult\Sie4Sdk\Dto\PeriodDto as Dto;

class PeriodDto extends LoaderBase
{
    /**
     * @param Generator $faker
     * @param string $kontoNr
     * @param int $dimensionNr
     * @param string $objektNr
     * @return Dto
     * @since 1.8.3 2023-09-20
     */
    public static function load( Generator $faker, string $kontoNr, int $dimensionNr, string $objektNr ) : Dto
    {
        $dto   = new Dto();

        $dto->setArsnr( 0 );

        static $period = null;
        if( empty( $period )) {
            $period = ( new DateTime())->modify( '-1 month' )->format( 'Ym' );
        }
        $dto->setPeriod( $period );

        $dto->setKontoNr( $kontoNr );
        $dto->setDimensionNr( $dimensionNr );
        $dto->setObjektNr( $objektNr );

        $dto->setSaldo( $faker->randomFloat( 2, 1, 999999 ));
        $dto->setKvantitet( $faker->randomDigitNot( 0 ));

        return $dto;
    }
}
