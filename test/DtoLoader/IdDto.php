<?php
/**
 * Sie4Sdk   PHP Sie4 SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Sdk
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
use Kigkonsult\Sie4Sdk\Dto\IdDto as Dto;

class IdDto
{
    /**
     * @return Dto
     */
    public static function load() : Dto
    {
        $faker = Faker\Factory::create();

        $dto = new Dto();

        $dto->setProsa((string) $faker->words( 4, true ));

        static $FTYPS = [
            'AB'  => 'Aktiebolag',
            'E'   => 'Enskild näringsidkare',
            'HB'  => 'Handelsbolag',
            'KB'  => 'Kommanditbolag',
            'EK'  => 'Ekonomisk förening',
            'KHF' => 'Kooperativ hyresrättsförening',
            'BRF' => 'Bostadsrättsförening',
            'BF'  => 'Bostadsförening',
            'SF'  => 'Sambruksförening',
            'I'   => 'Ideell förening som bedriver näring',
            'S'   => 'Stiftelse som bedriver näring',
            'FL'  => 'Filial till utländskt bolag',
            'BAB' => 'Bankaktiebolag.',
            'MB'  => 'Medlemsbank',
            'SB'  => 'Sparbank',
            'BFL' => 'Utländsk banks filial',
            'FAB' => 'Försäkringsaktiebolag',
            'OFB' => 'Ömsesidigt försäkringsbolag',
            'SE'  => 'Europabolag',
            'SCE' => 'Europakooperativ',
            'TSF' => 'Trossamfund',
            'X'   => 'Annan företagsform'
        ];
        $dto->setFtyp((string) $faker->randomElement(array_keys( $FTYPS )));

        $dto->setFnrId((string) $faker->numberBetween( 10000000, 999999999999 ));

        $dto->setOrgnr((string) $faker->numberBetween( 10000000, 999999999999 ));

        $dto->setMultiple(1);

        $dto->setBkod((string) $faker->numberBetween( 10000, 99999 ));

        $dto->setAdress( AdressDto::load());

        $dto->setFnamn( $faker->company );

        $dto->addRarDto( RarDto::load());

        $dto->setTaxar((int) $faker->time( 'Y' ));

        $dto->setOmfattn( new DateTime());

        static $KPTYPS = ['BAS95', 'BAS96', 'EUBAS97', 'NE2007'];
        $dto->setKptyp((string) $faker->randomElement( $KPTYPS ));

        $dto->setValutakod( $faker->currencyCode );

        return $dto;
    }
}
