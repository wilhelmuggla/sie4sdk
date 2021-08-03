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
namespace Kigkonsult\Sie4Sdk;

use DateTime;
use Exception;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\AdressDto;
use Kigkonsult\Sie4Sdk\Dto\BalansDto;
use Kigkonsult\Sie4Sdk\Dto\BalansObjektDto;
use Kigkonsult\Sie4Sdk\Dto\DimDto;
use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Dto\IdDto;
use Kigkonsult\Sie4Sdk\Dto\PeriodDto;
use Kigkonsult\Sie4Sdk\Dto\RarDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\SruDto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use PHPUnit\Framework\TestCase;

class TestDemo extends TestCase
{
    private static $FMT0 = '%s START (#%s) %s on \'%s\'%s';
    private static $ERR1 = ' Sie4EDto assert error, ';
    private static $ERR2 = ' Sie4IDto string compare error';
    /**
     * @test
     */
    public function demoTest()
    {
        echo sprintf( PHP_EOL . 'START ' . __METHOD__ . PHP_EOL );

        $sie4Dto1 = Sie4Dto::factory(
            IdDto::factory( 'AC', '1234567890' )
                 ->setMultiple( 1 )
                 ->setProsa( 'kommentar kommentar kommentar')
                 ->setFtyp( 'AB' )
                 ->setAdress(
                     AdressDto::factory(
                         'Kontakt Person',
                         'Storgatan 1234',
                         '123 45 Storstad',
                         '012-345 67 89'
                     )
                 )
                 ->setFnamn( 'Acme Corp' )
                 ->addRarDto(
                     RarDto::factory( 0, new DateTime( '20200101' ), new DateTime( '20211231' ))
                 )
                ->addRarDto(
                    RarDto::factory( -1, new DateTime( '20210101' ), new DateTime( '20210630' ))
                )
                 ->setTaxar( 2021 )
                 ->setKptyp( 'EUBAS97' )
                 ->setValutakod( 'SEK' )
            )
                 ->setAccountDtos(
                     [
                         AccountDto::factory(
                             1510,
                             'Kundfordringar',
                             AccountDto::T
                         ),
                     ]
                 ) //setAccountDtos
                 ->addAccount(
                     4950,
                     'Förändring av lager av färdiga varor',
                     AccountDto::K,
                     'st'
                 )
                 ->addSruDto(
                     SruDto::factory( 1510, 12345 )
                 )
                 ->addSruDto(
                     SruDto::factory( 4950, 23456 )
                 )
                 ->setDimDtos(
                     [
                         DimDto::factoryDim( 1, 'Avdelning'),
                     ]
                 ) // end setDimDtos
                 ->addDim(6, 'Projekt' )
                 ->addDimObjekt(
                     6,
                     '47',
                     'Sie5-projektet'
                 )
                 ->setDimObjektDtos(
                     [
                         DimObjektDto::factoryDimObject(
                             1,
                             '0123',
                             'Serviceavdelningen'
                         )
                     ]
                 ) // end setDimObjektDtos
                 ->setVerDtos(
                     [
                         VerDto::factory()
                               ->addTransKontoNrBelopp( 3020, -28000.00 )
                               ->addTransKontoNrBelopp( 2610, -7000.00 )
                               ->addTransKontoNrBelopp( 1510, 35000.00 ),
                         VerDto::factory( null, 'Porto', new DateTime( '20080101' ))
                               ->addTransKontoNrBelopp( 1910, -1000.00 )
                               ->addTransKontoNrBelopp( 2640, 200.00 )
                               ->addTransDto(
                                   TransDto::factory( 6110, 800, TransDTo::BTRANS )
                               )
                               ->addTransDto(
                                   TransDto::factory( 6250, 800, TransDTo::RTRANS )
                               )
                               ->addTransDto(
                                   TransDto::factory( 6250, 800, TransDTo::TRANS )
                               ),
                         VerDto::factory( 12345, 'ver text for 12345' )
                               ->setTransDtos(
                                   [
                                       TransDto::factory( 1910, -2000.00, TransDto::RTRANS )
                                               ->setTranstext( 'trans text 1' )
                                               ->setTransdat(( new DateTime())->setTime( 0, 0, 0 )),
                                       TransDto::factory( 1910, -2000.00 )
                                               ->setTranstext( 'trans text 1' )
                                               ->setTransdat(( new DateTime())->setTime( 0, 0, 0 )),
                                       TransDto::factory( 2640, 400 ),
                                       TransDto::factory( 6250, 1600.00 )
                                               ->setObjektlista(
                                                   [
                                                       DimObjektDto::factoryDimObject(
                                                           6,
                                                           '47'
                                                       )
                                                   ]
                                               )
                                               ->setSign( 'verSign 12345-3' )
                                   ]
                               ),
                         VerDto::factory( 23456, 'ver text for 23456' )
                               ->setSerie( 'A' )
                               ->setRegdatum(( new DateTime( '-1 day' ))->setTime( 0, 0, 0 ))
                               ->setSign( 'sign 23456' )
                               ->setTransDtos(
                                   [
                                       TransDto::factory( 7010, 56900.00 )
                                               ->setTransdat(( new DateTime( '-1 day' ))->setTime( 0, 0, 0 ))
                                               ->setTranstext( 'ver 23456 trans text 1' )
                                               ->setSign( 'transSign 23456-1' )
                                               ->setObjektlista(
                                                   [
                                                       DimObjektDto::factoryDimObject(
                                                           1,
                                                           '456'
                                                       ),
                                                       DimObjektDto::factoryDimObject(
                                                           6,
                                                           '47'
                                                       )
                                                   ]
                                               )
                                               ->setKvantitet( 10 )
                                   ]
                               )
                               ->addTransKontoNrBelopp( 1910, -56900.00 )
                     ]
                 ); // end setVerDtos

        $countVerDtos      = $sie4Dto1->countVerDtos();
        $this->assertNotEmpty(
            $countVerDtos,
            'Sie4IDto has no VerDtos'
        );
        $countVerTransDtos = $sie4Dto1->countVerTransDtos();
        $this->assertNotEmpty(
            $countVerTransDtos,
            'Sie4IDto has no TransDtos'
        );
        // echo 'Sie4IDto has ' . $countVerDtos . ' VerDtos and ' . $countVerTransDtos . ' TransDtos' . PHP_EOL;

        $sie4Array = Sie4::sie4Dto2Array( $sie4Dto1 );
        // echo var_export( $sie4Array ) . PHP_EOL; // test ###
        $sie4Dto2  = Sie4::array2Sie4Dto( $sie4Array );

        $jsonString = Sie4::sie4Dto2Json( $sie4Dto2 );
        // echo $jsonString . PHP_EOL;
        $sie4Dto3   = Sie4::json2Sie4Dto( $jsonString );

        /*
        $this->assertEquals(
            StringUtil::cp437toUtf8(
                Sie4::sie4IDto2String( $sie4Dto1 )
            ),
            StringUtil::cp437toUtf8(
                Sie4::sie4IDto2String( $sie4Dto3 )
            ),
            'case 1 after array- and json-tests'
        );
        */

        $this->sie4ITests( 100, $sie4Dto1, $sie4Dto3 );

        $this->sie4ETests( 200, $sie4Dto1, $sie4Dto3 );
    }

    /**
     * Specific Sie4I tests
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto1
     * @param Sie4Dto $sie4Dto3
     */
    public function sie4ITests( int $case, Sie4Dto $sie4Dto1, Sie4Dto $sie4Dto3 )
    {
        $outcome = true;
        try {
            Sie4Validator::assertSie4IDto( $sie4Dto3 );
        }
        catch( Exception $e ) {
            $outcome = $e->getMessage();
        }
        $this->assertTrue(
            $outcome,
            ++$case . self::$ERR1 . $outcome
        );
        $this->assertEquals(
            StringUtil::cp437toUtf8(
                Sie4::sie4IDto2String( $sie4Dto1 )
            ),
            StringUtil::cp437toUtf8(
                Sie4::sie4IDto2String( $sie4Dto3 )
            ),
            ++$case . self::$ERR2
        );
    }

    /**
     * Specific Sie4E tests
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto1
     * @param Sie4Dto $sie4Dto3
     */
    public function sie4ETests( int $case, Sie4Dto $sie4Dto1, Sie4Dto $sie4Dto3 )
    {
        // add specific Sie4E labels
        $sie4Dto1->getIdDto()->setBkod( 'ABCD' )
                 ->setOmfattn( new DateTime( '20210630' ));
        $sie4Dto1->addIbDto(
            BalansDto::factory( 0, 1234, 5678, 1.1 )
        )
                 ->addUbDto(
                     BalansDto::factory( 0, 2345, 5678, 1.1 )
                 )
                 ->addOibDto(
                     BalansObjektDto::factory( 0, 1111, 5678, 1.1 )
                                    ->setDimensionNr( 1 )
                                    ->setObjektNr( 'A' )
                 )
                 ->addOubDto(
                     BalansObjektDto::factory( 0, 2222, 5678, 1.1 )
                                    ->setDimensionNr( 2 )
                                    ->setObjektNr( 'B' )
                 )
                 ->addSaldoDto(
                     BalansDto::factory( 0, 3456, 5678, 1.1 )
                 )
                 ->addPsaldoDto(
                     PeriodDto::factory( 0, 4567, 5678, 1.1 )
                              ->setPeriod( 202101 )
                              ->setDimensionNr( 3 )
                              ->setObjektNr( 'PsaldoObjekt' )
                 )
                 ->addPBudgetDto(
                     PeriodDto::factory( 0, 1111, 5678, 1.1 )
                              ->setPeriod( 202101 )
                              ->setDimensionNr( 4 )
                              ->setObjektNr( 'PbudgetObjekt' )
                 );
        $sie4Dto3->getIdDto()->setBkod( 'ABCD' )
                 ->setOmfattn( new DateTime( '20210630' ));
        $sie4Dto3->addIbDto(
            BalansDto::factory( 0, 1234, 5678, 1.1 )
        )
                 ->addUbDto(
                     BalansDto::factory( 0, 2345, 5678, 1.1 )
                 )
                 ->addOibDto(
                     BalansObjektDto::factory( 0, 1111, 5678, 1.1 )
                                    ->setDimensionNr( 1 )
                                    ->setObjektNr( 'A' )
                 )
                 ->addOubDto(
                     BalansObjektDto::factory( 0, 2222, 5678, 1.1 )
                                    ->setDimensionNr( 2 )
                                    ->setObjektNr( 'B' )
                 )
                 ->addSaldoDto(
                     BalansDto::factory( 0, 3456, 5678, 1.1 )
                 )
                 ->addPsaldoDto(
                     PeriodDto::factory( 0, 4567, 5678, 1.1 )
                              ->setPeriod( 202101 )
                              ->setDimensionNr( 3 )
                              ->setObjektNr( 'PsaldoObjekt' )
                 )
                 ->addPBudgetDto(
                     PeriodDto::factory( 0, 1111, 5678, 1.1 )
                              ->setPeriod( 202101 )
                              ->setDimensionNr( 4 )
                              ->setObjektNr( 'PbudgetObjekt' )
                 );

        $outcome = true;
        try {
            Sie4Validator::assertSie4EDto( $sie4Dto3 );
        }
        catch( Exception $e ) {
            $outcome = $e->getMessage();
        }
        $this->assertTrue(
            $outcome,
            ++$case . self::$ERR1 . $outcome
        );
        $this->assertEquals(
            StringUtil::cp437toUtf8(
                Sie4::sie4EDto2String( $sie4Dto1 )
            ),
            StringUtil::cp437toUtf8(
                Sie4::sie4EDto2String( $sie4Dto3 )
            ),
            ++$case . self::$ERR2
        );
    }
}
