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
use Faker\Factory;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto as Dto;

class Sie4Dto extends LoaderBase
{
    /**
     * @return Dto
     * @since 1.8.3 2023-09-20
     */
    public static function load() : Dto
    {
        $faker = Factory::create();

        $idDto = IdDto::load( $faker );
        if( 1 === $faker->randomElement( self::$Arr12 )) {
            $dto = new Dto( $idDto );
        }
        else {
            $dto = Dto::factory( $idDto->getFnamn(), $idDto->getFnrId(), $idDto->getOrgnr());
            $dto->getIdDto()
                ->setProsa( $idDto->getProsa())
                ->setFtyp( $idDto->getFtyp())
                ->setMultiple( $idDto->getMultiple())
                ->setBkod( $idDto->getBkod())
                ->setAdress( $idDto->getAdress())
                ->setRarDtos( $idDto->getRarDtos())
                ->setTaxar( $idDto->getTaxar())
                ->setOmfattn( $idDto->getOmfattn())
                ->setKptyp( $idDto->getKptyp())
                ->setValutakod( $idDto->getValutakod());
        } // end else

        if( 1 === $faker->randomElement( self::$Arr123 )) {
            $dto->setKsumma( 1 );
        }

        /**
         * AccountDto[]  #KONTO/#KTYP/#ENHET, saved for later use
         */
        $max         = $faker->numberBetween( 15, 20 );
        $kontoNrs    = [];
        while( $max > count( $kontoNrs )) {
            $kontoNr = (string) $faker->numberBetween( 1000, 9999 );
            if( ! isset( $kontoNrs[$kontoNr] )) {
                $kontoNrs[$kontoNr] = $kontoNr;
            }
        } // end while
        sort( $kontoNrs );
        foreach( $kontoNrs as $kontoNr ) {
            $dto->addAccountDto( AccountDto::load( $faker, $kontoNr ));
        }

        /**
         * SruDto[]   #SRU
         */
        foreach( $kontoNrs as $kontoNr ) {
            $dto->addSruDto( SruDto::load( $faker, $kontoNr ));
        }

        $dimObjectNrs = [];
        /**
         * DimDto[]  #DIM
         */
        static $DIMs = [
            1  => 'Kostnadsställe / resultatenhet',
            2  => 'Kostnadsbärare (skall vara underdimension till 1)',
            6  => 'Projekt',
            7  => 'Anställd',
            8  => 'Kund',
            9  => 'Leverantör',
            10 => 'Faktura'
        ];
        foreach( $DIMs as $dimensionNr => $dimensionNamn ) {
            $dto->addDim( $dimensionNr, $dimensionNamn );
            $dimObjectNrs[$dimensionNr] = [];
        } // end foreach

        /**
         * UnderDimDto[]  #UNDERDIM
         */
        $dimensionNamn = 'underDimension ';
        $underDimNr    = 20;
        foreach( array_keys( $DIMs ) as $superDimNr ) {
            for( $x = 0; $x < 3; $x++ ) {
                $dto->addUnderDim( ++$underDimNr, $dimensionNamn . $underDimNr, $superDimNr );
                $dimObjectNrs[$underDimNr] = [];
            } // end foreach
        } // end foreach

        /**
         * DimObjektDto[]   #OBJECT
         */
        $objektNr     = 0;
        $objektNamn   = 'objekt ';
        foreach( array_keys( $dimObjectNrs) as $dimensionNr ) {
            for( $x = 0; $x < 3; $x++ ) {
                $dto->addDimObjekt( $dimensionNr, (string) ++$objektNr, $objektNamn . $objektNr );
                $dimObjectNrs[$dimensionNr][] = $objektNr;
            } // end foreach
        } // end foreach

        /**
         * BalansDto[]  Ingående balans  #IB
         */
        foreach( $kontoNrs as $kontoNr ) {
            $dto->addIbDto( BalansDto::load( $faker, $kontoNr ));
        }

        /**
         * BalansDto[]  Utgående balans #UB
         */
        foreach( $kontoNrs as $kontoNr ) {
            $dto->addUbDto( BalansDto::load( $faker, $kontoNr ));
        }

        /**
         * BalansObjektDto[]  Ingående balans för objekt  #OIB
         */
        foreach( $kontoNrs as $kontoNr ) {
            if( 1 !== $faker->randomElement( self::$Arr123 )) {
                continue;
            }
            foreach( array_keys( $dimObjectNrs ) as $dimensionNr ) {
                foreach( $dimObjectNrs[$dimensionNr] as $objektNr ) {
                    $dto->addOibDto( BalansObjektDto::load( $faker, $kontoNr, (int) $dimensionNr, (string) $objektNr ));
                } // end foreach
            } // end foreach
        } // end foreach

        /**
         * BalansObjektDto[]  Utgående balans för objekt   #OUB
         */
        foreach( $kontoNrs as $kontoNr ) {
            if( 1 !== $faker->randomElement( self::$Arr123 )) {
                continue;
            }
            foreach( array_keys( $dimObjectNrs ) as $dimensionNr ) {
                foreach( $dimObjectNrs[$dimensionNr] as $objektNr ) {
                    $dto->addOubDto( BalansObjektDto::load( $faker, $kontoNr, (int) $dimensionNr, (string) $objektNr ));
                } // end foreach
            } // end foreach
        } // end foreach

        /**
         * BalansDto[]   Saldo för resultatkonto  #RES
         */
        foreach( $kontoNrs as $kontoNr ) {
            $dto->addSaldoDto( BalansDto::load( $faker, $kontoNr ));
        }

        /**
         * PeriodDto[]  PeriodSaldopost  #PSALDO
         */
        foreach( $kontoNrs as $kontoNr ) {
            foreach( array_keys( $dimObjectNrs ) as $dimensionNr ) {
                if( 1 !== $faker->randomElement( self::$Arr123 )) {
                    continue;
                }
                foreach( $dimObjectNrs[$dimensionNr] as $objektNr ) {
                    $dto->addPsaldoDto( PeriodDto::load( $faker, $kontoNr, (int) $dimensionNr, (string) $objektNr ));
                } // end foreach
            } // end foreach
        } // end foreach

        /**
         *  PeriodDto[]  PeriodBudgetpost  #PBUDGET
         */
        foreach( $kontoNrs as $kontoNr ) {
            foreach( array_keys( $dimObjectNrs ) as $dimensionNr ) {
                if( 1 !== $faker->randomElement( self::$Arr123 )) {
                    continue;
                }
                foreach( $dimObjectNrs[$dimensionNr] as $objektNr ) {
                    $dto->addPbudgetDto( PeriodDto::load( $faker, $kontoNr, (int) $dimensionNr, (string) $objektNr ));
                } // end foreach
            } // end foreach
        } // end foreach

        /**
         *  VerDto[]   verifikationer med kontringsrader  #VER/#TRANS
         */
        $max = $faker->numberBetween( 10, 20 );
        for( $x = 0; $x < $max; $x++ ) {
            $dto->addVerDto( VerDto::load( $faker, $kontoNrs, $dimObjectNrs ));
        } // end for

        return $dto;
    }
}
