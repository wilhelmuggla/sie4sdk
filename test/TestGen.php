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
namespace Kigkonsult\Sie4Sdk;

use Exception;
use Kigkonsult\Sie4Sdk\Api\Array2Sie4Dto;
use Kigkonsult\Sie4Sdk\Api\Json2Sie4Dto;
use Kigkonsult\Sie4Sdk\Api\Sie4Dto2Array;
use Kigkonsult\Sie4Sdk\Api\Sie4Dto2Json;
use Kigkonsult\Sie4Sdk\Dto\IdDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\DimDto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\DtoLoader\Sie4Dto as Sie4DtoLoader;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use PHPUnit\Framework\TestCase;
use RuntimeException;

$phpDiffFile = __DIR__ . '/PHPDiff/PHPDiff.php';
if( ! in_array( $phpDiffFile, get_included_files())) {
    include $phpDiffFile;
}

class TestGen extends TestCase
{
    private static string $ERR1 = '#%d-%d Sie4%sDto assert error, %s%s';
    private static string $ERR2 = '#%d-%d Sie4%sDto string compare error';
    private static string $ERR3 = '#%s-%d Sie4%sDto %s error, %s - %s';

    /**
     * genTest1/2 dataProvider
     *
     * Using phpunit php/var directive to set number of test sets, now 5
     *
     * @return array
     */
    public static function genTestProvider() : array
    {
        $dataArr  = [];
        $max      = 1; // (int) $GLOBALS['GENTESTMAX'];

        for( $case = 1; $case <= $max; $case++ ) {
            $dataArr[] =
                [
                    $case,
                    Sie4DtoLoader::load(),
                ];
        } // end for

        return $dataArr;
    }

    /**
     * Simple double create-string/parse-string test, Sie4EDto
     *
     * @test
     * @dataProvider genTestProvider
     *
     * @param int $case
     * @param Sie4Dto $sie4Dto
     * @return void
     * @throws Exception
     */
    public function genTest1( int $case, Sie4Dto $sie4Dto ) : void
    {
        $case += 100;
        // create cp437 sie4String
        $sie4String1 = Sie4EWriter::factory()->process( $sie4Dto );

        // parse the Sie4 string back and create new Sie4 string
        $start = microtime( true ); // test ###
        $sie4Dto2 = Sie4Parser::factory()->process( $sie4String1 );
        $start = microtime( true ); // test ###
        $sie4String2 = Sie4EWriter::factory()->process( $sie4Dto2 );

        // compare sie4 strings
        $this->cmpSieStrings( $sie4String1, $sie4String2, __FUNCTION__, $case . '-10' );
        /*
        $sie4String2 = Sie4EWriter::factory()->process(
            Sie4Parser::factory()->process( $sie4String1 )
        );
        */
        // parse the Sie4 string back to a new Sie4Dto
        $sie4Dto3    = Sie4Parser::factory()->process( $sie4String2 );

        static $oneTimetest = true;
        if( $oneTimetest ) {
            $oneTimetest = false;
            // first only, test timestamp+guid, uniqueness in SieDto
            // also same in sie4Dto and sie4Dto3
            $this->checkTimeStampGuid4( $case, $sie4Dto3 );
            // check set fnrId, same in SieDto, IdDto, verDto and TransDto
            $this->checkFnrId5( $case, $sie4Dto3 );
            // check set orgnr, same in SieDto, IdDto, verDto and TransDto
            $this->checkOrgnr6( $case, $sie4Dto3 );
            // check serie/vernr, populatd down from VerDto to each TransDto
            $this->checkSerieVernr7( $case, $sie4Dto3 );

            $this->accountDtoListTest11( $case, $sie4Dto3 );
            $this->balansDtoListTest12( $case, $sie4Dto3 );
            $this->balansObjektDtoList13( $case, $sie4Dto3 );
            $this->dimDtoListTest14( $case, $sie4Dto3 );
            $this->dimObjektDtoListTest15( $case, $sie4Dto3 );
            $this->sruDtoListTest16( $case, $sie4Dto3 );
            $this->underDimDtoListTest17( $case, $sie4Dto3 );
            $this->transDtoListTest18( $case, $sie4Dto3 );
        } // end if

        // create new Sie4 string, compare
        $sie4String3 = Sie4EWriter::factory()->process( $sie4Dto3 );
        // compare sie4 strings
        $this->cmpSieStrings( $sie4String1, $sie4String3, __FUNCTION__, $case . '-11' );
    }

    /**
     * Compare two Sie strings
     *
     * @param null|string $str1
     * @param null|string $str2
     * @param string      $function
     * @param string      $errNo
     * @return void
     */
    private function cmpSieStrings( ? string $str1, ? string $str2, string $function, string $errNo ) : void
    {
        if( empty( $str1 ) || empty( $str2 )) {
            return;
        }
        $str1Utf8 = StringUtil::cp437toUtf8( $str1 );
        $str2Utf8 = StringUtil::cp437toUtf8( $str2 );
        $diff     = PHPDiff( $str1Utf8 . PHP_EOL, $str2Utf8 . PHP_EOL );
        $this->assertEquals(
            $str1Utf8,
            $str1Utf8,
            sprintf( self::$ERR2, $function, $errNo, 'E' ) . PHP_EOL . $diff
        );
    }

    /**
     * test timestamp+guid, uniqueness in SieDto
     * check timestamps and guids - same in sie4Dto and sie4Dto3
     *
     * @param int     $case
     * @param Sie4Dto $actual
     * @return void
     */
    public function checkTimeStampGuid4( int $case, Sie4Dto $actual ) : void
    {
        $expSie4DtoCorr  = $actual->getCorrelationId();
        $sie4DtoVerDtos = $actual->getVerDtos()->get();
        foreach( $actual->getVerDtos()->get() as $vx => $verDto ) {
            $testV = 50 . '-' . $vx;
            $actParentCorr = $verDto->getParentCorrelationId();
            $this->assertEquals(
                $expSie4DtoCorr,
                $actParentCorr,
                sprintf(self::$ERR3, $case, $testV . '-1', 'E', Sie4Dto::GUID, $expSie4DtoCorr, $actParentCorr )
            );
            $verCorrId = $sie4DtoVerDtos[$vx]->getCorrelationId();
            $value     = $verDto->getCorrelationId();
            $this->assertEquals(
                $verCorrId,
                $value,
                sprintf(self::$ERR3, $case, $testV . '-2', 'E', Sie4Dto::VERGUID, $verCorrId, $value )
            );
            $verTransDtos = $sie4DtoVerDtos[$vx]->getTransDtos()->get();
            foreach( $verDto->getTransDtos()->get() as $tx => $transDto ) {
                $testT = $testV . '-' . $tx;
                $exp   = $verTransDtos[$tx]->getTimestamp();
                $value = $transDto->getTimestamp();
                $this->assertEquals(
                    $exp,
                    $value,
                    sprintf(self::$ERR3, $case, $testT . '-3', 'E', Sie4Dto::TRANSTIMESTAMP, $exp, (string) $value )
                );
                $parentCorrId = $verTransDtos[$tx]->getParentCorrelationId();
                $this->assertEquals(
                    $verCorrId,
                    $parentCorrId,
                    sprintf(self::$ERR3, $case, $testT . '-4', 'E', Sie4Dto::TRANSGUID, $verCorrId, $parentCorrId )
                );
                $transCorrId = $verTransDtos[$tx]->getCorrelationId();
                $value       = $transDto->getCorrelationId();
                $this->assertEquals(
                    $transCorrId,
                    $value,
                    sprintf(self::$ERR3, $case, $testT . '-5', 'E', Sie4Dto::TRANSGUID, $transCorrId, $value )
                );
            } // end foreach
        } // end foreach
    }

    /**
     * test setting fnrId in SieDto, must exist in each verDto and TransDto
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function checkFnrId5( int $case, Sie4Dto $sie4Dto ) : void
    {
        static $ERR4   = '#%s-%d Sie4Dto %s %s fnrId error, %s - %s';
        static $FNRID  = 'ABC';
        $case         .= '-FnrIdOrgnr-';
        $sie4Dto       = clone $sie4Dto;
        $sie4Dto->setFnrId( $FNRID );
        $sie4Dto->setOrgnr( $FNRID ); // test ###
        $sieIdDtoFnrId = $sie4Dto->getIdDto()->getFnrId();

        $this->assertEquals(
            $FNRID,
            $sie4Dto->getFnrId(),
            sprintf( $ERR4, $case, 51, '', '', $FNRID, $sie4Dto->getFnrId())
        );
        $this->assertEquals(
            $FNRID,
            $sieIdDtoFnrId,
            sprintf( $ERR4, $case, 52, '', 'IdDto', $FNRID, $sieIdDtoFnrId )
        );

        foreach( $sie4Dto->getVerDtos() as $vx => $verDto ) {
            $this->assertEquals(
                $FNRID,
                $verDto->getFnrId(),
                sprintf( $ERR4, $case, 53, $vx, 'VerDto', $FNRID, $sieIdDtoFnrId )
            );
            foreach( $verDto->getTransDtos() as $tx => $transDto ) {
                $this->assertEquals(
                    $FNRID,
                    $transDto->getFnrId(),
                    sprintf( $ERR4, $case, 54, $vx . '-' . $tx, 'TransDto', $FNRID, $sieIdDtoFnrId )
                );
            } // end foreach
        } // end foreach
    }

    /**
     * test setting orgnr in SieDto, must exist in each verDto and TransDto
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function checkOrgnr6( int $case, Sie4Dto $sie4Dto ) : void
    {
        static $ERR4  = '#%s%d Sie4Dto %s %s orgnr error, %s - %s';
        static $ORGNR = 'ABCorgnr';
        static $MULTI = 2;
        $case        .= '-FnrIdOrgnr-';
        $sie4Dto = clone $sie4Dto;
        $sie4Dto->setOrgnr( $ORGNR );
        $sie4Dto->setMultiple( $MULTI );

        $orgnrM = $sie4Dto->getOrgnr() . $sie4Dto->getMultiple();
        $this->assertEquals(
            $ORGNR . $MULTI,
            $orgnrM,
            sprintf( $ERR4, $case, 61, '', '', $ORGNR . $MULTI, $orgnrM )
        );
        $orgnrM = $sie4Dto->getIdDto()->getOrgnr() . $sie4Dto->getIdDto()->getMultiple();
        $this->assertEquals(
            $ORGNR . $MULTI,
            $orgnrM,
            sprintf( $ERR4, $case, 62, '', 'IdDto', $ORGNR . $MULTI, $orgnrM )
        );

        foreach( $sie4Dto->getVerDtos() as $vx => $verDto ) {
            $orgnrM = $verDto->getOrgnr() . $verDto->getMultiple();
            $this->assertEquals(
                $ORGNR . $MULTI,
                $orgnrM,
                sprintf( $ERR4, $case, 63, $vx, 'VerDto', $ORGNR . $MULTI, $orgnrM )
            );
            foreach( $verDto->getTransDtos() as $tx => $transDto ) {
                $orgnrM = $transDto->getOrgnr() . $transDto->getMultiple();
                $this->assertEquals(
                    $ORGNR . $MULTI,
                    $orgnrM,
                    sprintf( $ERR4, $case, 64, $vx . '-' . $tx, 'TransDto', $ORGNR . $MULTI, $orgnrM )
                );
            } // end foreach
        } // end foreach
    }

    /**
     * test setting orgnr in SieDto, must exist in each verDto and TransDto
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function checkSerieVernr7( int $case, Sie4Dto $sie4Dto ) : void
    {
        static $ERR5 = '#%s%d Sie4Dto %s %s serie/vernr error, %s - %s';
        $case       .= '-serieVernr';
        foreach( $sie4Dto->getVerDtos() as $vx => $verDto ) {
            $serie   = $verDto->isSerieSet() ? $verDto->getSerie() : StringUtil::$SP0;
            $vernr   = $verDto->isVernrSet() ? $verDto->getVernr() : StringUtil::$SP0;
            $exp     = $serie . $vernr;
            foreach( $verDto->getTransDtos() as $tx => $transDto ) {
                $actual = $transDto->getSerie() . $transDto->getVernr();
                $this->assertEquals(
                    $exp,
                    $actual,
                    sprintf( $ERR5, $case, 7, $vx . '-' . $tx, 'TransDto', $exp, $actual )
                );
            } // end foreach
        } // end foreach
    }

    /**
     * Test AccountDtoList::isKontoNrSet()
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function accountDtoListTest11( int $case, Sie4Dto $sie4Dto ) : void
    {
        if( 0 === $sie4Dto->countAccountDtos()) {
            return;
        }
        $accountDtoList = $sie4Dto->getAccountDtos();
        $kontoNrs       = $accountDtoList->getPkeys();
        $kontoNr        = array_rand( array_flip( $kontoNrs ));
        $this->assertTrue(
            $accountDtoList->isKontoNrSet( $kontoNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 1, Sie4Dto::KONTONR, $kontoNr )
        );
        $found = false;
        foreach( $accountDtoList->get() as $accountDto ) {
            if( $kontoNr == $accountDto->getKontoNr()) { // note ==
                $found = true;
                break;
            }
        }
        if( true !== $found ) {
            ob_start();
            var_dump( $kontoNr, $kontoNrs );
            $dsp = ob_get_contents();
            ob_end_clean();
            $this->assertTrue(
                false,
                sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 2, Sie4Dto::KONTONR, $dsp )
            );
        }
    }

    /**
     * Test BalansDtoList::isKontoNrSet()
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function balansDtoListTest12( int $case, Sie4Dto $sie4Dto ) : void
    {
        if( 0 < $sie4Dto->countIbDtos()) {
            $balansDtoList = $sie4Dto->getIbDtos();
            $kontoNrs      = $balansDtoList->getPkeys();
            $kontoNr       = array_rand( array_flip( $kontoNrs ));
            $this->assertTrue(
                $balansDtoList->isKontoNrSet( $kontoNr ),
                sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 11, Sie4Dto::KONTONR, $kontoNr )
            );
            $found = false;
            foreach( $balansDtoList->get() as $balansDto ) {
                if( $kontoNr == $balansDto->getKontoNr()) { // note ==
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 12, Sie4Dto::KONTONR, $kontoNr )
            );
        }
        if( 0 < $sie4Dto->countUbDtos()) {
            $balansDtoList = $sie4Dto->getUbDtos();
            $kontoNrs      = $balansDtoList->getPkeys();
            $kontoNr       = array_rand( array_flip( $kontoNrs ));
            $this->assertTrue(
                $balansDtoList->isKontoNrSet( $kontoNr ),
                sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 21, Sie4Dto::KONTONR, $kontoNr )
            );
            $found = false;
            foreach( $balansDtoList->get() as $balansDto ) {
                if( $kontoNr == $balansDto->getKontoNr()) { // note ==
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 22, Sie4Dto::KONTONR, $kontoNr )
            );
        }
    }

    /**
     * Test BalansObjektDtoList::isKontoNrSet()
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function balansObjektDtoList13( int $case, Sie4Dto $sie4Dto ) : void
    {
        switch( true ) {
            case ( 0 < $sie4Dto->countOibDtos()) :
                $balansObjektDtoList = $sie4Dto->getOibDtos();
                break;
            case ( 0 < $sie4Dto->countOubDtos()) :
                $balansObjektDtoList = $sie4Dto->getOubDtos();
                break;
            default :
                return;
        } // end case
        $balansObjektDtos = $balansObjektDtoList->get();
        $x = array_rand( $balansObjektDtos );
        $balansObjektDto  = $balansObjektDtos[$x];
        $kontoNr          = $balansObjektDto->getKontoNr();
//      $dimensionNr      = $balansObjektDto->getDimensionNr();
//      $objektNr         = $balansObjektDto->getObjektNr();
        $this->assertTrue(
            $balansObjektDtoList->isKontoNrSet( $kontoNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 1, Sie4Dto::KONTONR, $kontoNr )
        );
    }

    /**
     * Test DimDtoList::isDimensionNrSet() / getDimensionNamn() / getDimDto()
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function dimDtoListTest14( int $case, Sie4Dto $sie4Dto ) : void
    {
        if( 0 === $sie4Dto->countDimDtos()) {
            return;
        }
        $dimDtoList    = $sie4Dto->getDimDtos();
        $dimDtos       = $dimDtoList->get();
        $x             = array_rand( $dimDtos );
        $dimDto        = $dimDtos[$x];
        $dimensionNr   = $dimDto->getDimensionNr();
        $dimensionNamn = $dimDto->getDimensionNamn();
        $this->assertTrue(
            $dimDtoList->isDimensionNrSet( $dimensionNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 1, Sie4Dto::DIMENSIONNR, $dimensionNr )
        );
        $this->assertSame(
            $dimensionNamn,
            $dimDtoList->getDimensionNamn( $dimensionNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 2, Sie4Dto::DIMENSIONNR, $dimensionNr )
        );
        $this->assertSame(
            $dimDto,
            $dimDtoList->getDimDto( $dimensionNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 3, Sie4Dto::DIMENSIONNR, $dimensionNr )
        );
        $ok = true;
        try {
            $dimDtoList->getDimDto( 12345 );
        }
        catch( Exception ) {
            $ok = false;
        }
        $this->assertFalse(
            $ok,
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 4, Sie4Dto::DIMENSIONNR, 12345 )
        );
    }

    /**
     * Test DimObjektDtoList::isObjektNrSet()
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function dimObjektDtoListTest15( int $case, Sie4Dto $sie4Dto ) : void
    {
        if( 0 === $sie4Dto->countDimObjektDtos()) {
            return;
        }
        $dimObjektDtoList = $sie4Dto->getDimObjektDtos();
        $dimObjektDtos    = $dimObjektDtoList->get();
        $x                = array_rand( $dimObjektDtos );
        $dimObjektDto     = $dimObjektDtos[$x];
        $dimensionNr      = $dimObjektDto->getDimensionNr();
        $objektNr         = $dimObjektDto->getObjektNr();
        $this->assertTrue(
            $dimObjektDtoList->isObjektNrSet( $dimensionNr, $objektNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 1, Sie4Dto::OBJEKTNR, $dimensionNr . '/' . $objektNr )
        );
    }

    /**
     * Test SruDtoList::isKontoNrSet() + getSruKod()
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function sruDtoListTest16( int $case, Sie4Dto $sie4Dto ) : void
    {
        if( 0 === $sie4Dto->countSruDtos()) {
            return;
        }
        $sruDtoList = $sie4Dto->getSruDtos();
        $sruDtos    = $sruDtoList->get();
        $x          = array_rand( $sruDtos );
        $sruDto     = $sruDtos[$x];
        $kontoNr    = $sruDto->getKontoNr();
        $sruKod     = $sruDto->getSruKod();
        $this->assertTrue(
            $sruDtoList->isKontoNrSet( $kontoNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 1, Sie4Dto::KONTONR, $kontoNr )
        );
        $this->assertSame(
            $sruKod,
            $sruDtoList->getSruKod( $kontoNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 2, Sie4Dto::SRUKOD, $kontoNr . '/' . $sruKod )
        );
    }

    /**
     * Test UnderDimDtoList::isDimensionNrSet() / getDimensionNamn() / getSuperDimensionNr() / getUnderDimDtosForSuper
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function underDimDtoListTest17( int $case, Sie4Dto $sie4Dto ) : void
    {
        if( 0 === $sie4Dto->countUnderDimDtos()) {
            return;
        }
        $underDimDtoList = $sie4Dto->getUnderDimDtos();
        $underDimDtos    = $underDimDtoList->get();
        $x               = array_rand( $underDimDtos );
        $underDimDto     = $underDimDtos[$x];
        $dimensionNr     = $underDimDto->getDimensionNr();
        $dimensionNamn   = $underDimDto->getDimensionNamn();
        $superNr         = $underDimDto->getSuperDimNr();
        $this->assertTrue(
            $underDimDtoList->isDimensionNrSet( $dimensionNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 1, Sie4Dto::DIMENSIONNR, $dimensionNr )
        );
        $this->assertSame(
            $dimensionNamn,
            $underDimDtoList->getDimensionNamn( $dimensionNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 2, Sie4Dto::DIMENSIONNR, $dimensionNr )
        );
        $this->assertSame(
            $superNr,
            $underDimDtoList->getSuperDimensionNr( $dimensionNr ),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 3, Sie4Dto::DIMENSIONNR, $dimensionNr . '/' . $superNr )
        );
        $this->assertTrue(
            in_array( $underDimDto, $underDimDtoList->getUnderDimDtosForSuper( $superNr )),
            sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 4, Sie4Dto::DIMENSIONNR, $dimensionNr . '/' . $superNr )
        );
    }

    /**
     * Test VerDtoList::iBalans() / TransDtoList::iBalans()
     *
     * @param int     $case
     * @param Sie4Dto $sie4Dto
     * @return void
     */
    public function transDtoListTest18( int $case, Sie4Dto $sie4Dto ) : void
    {
        foreach( $sie4Dto->getVerDtos()->getIterator() as $vx =>$verDto ) {
            $this->assertTrue(
                $verDto->iBalans( $diff ),
                sprintf( self::$ERR3, __FUNCTION__, $case, 'I', 3, Sie4Dto::VERNR, $verDto->getVernr() . '/' . $vx . '/' . $diff )
            );
        }
    }

    /**
     * @test
     * @dataProvider genTestProvider
     *
     * @param int $case
     * @param Sie4Dto $sie4Dto
     * @return void
     * @throws Exception
     */
    public function genTest2( int $case, Sie4Dto $sie4Dto ) : void
    {
        // SAVE here to solve sie4/sie5 disparity and to avoid last assert errors
        $fixes = [];
        $fixes[Sie4Dto::GENSIGN]   = $sie4Dto->getIdDto()->getSign() ?? Sie4Dto::PRODUCTNAME;
        $fixes[Sie4Dto::PROSA]     = $sie4Dto->getIdDto()->getProsa();
        $fixes[Sie4Dto::FTYP]      = $sie4Dto->getIdDto()->getFtyp();
        $fixes[Sie4Dto::ADRESS]    = $sie4Dto->getIdDto()->getAdress();
        $fixes[Sie4Dto::RAR]       = $sie4Dto->getIdDto()->getRarDtos();
        $fixes[Sie4Dto::TAXAR]     = $sie4Dto->getIdDto()->getTaxar();
        $fixes[Sie4Dto::KPTYPE]    = $sie4Dto->getIdDto()->getKptyp();
        $fixes[Sie4Dto::SRU]       = [];
        foreach( $sie4Dto->getSruDtos()->yield() as $sruDto ) {
            $fixes[Sie4Dto::SRU][] = $sruDto;
        }
        $fixes[Sie4Dto::DIMENSIONNR]       = [];
        foreach( $sie4Dto->getDimDtos()->yield() as $dimDto ) {
            $fixes[Sie4Dto::DIMENSIONNR][] = $dimDto;
        }
        $fixes[Sie4Dto::UNDERDIM]       = [];
        foreach( $sie4Dto->getUnderDimDtos()->yield() as $underDimDto ) {
            $fixes[Sie4Dto::UNDERDIM][] = $underDimDto;
        }
        $fixes[Sie4Dto::OBJEKT]    = [];
        foreach( $sie4Dto->getDimObjektDtos()->yield() as $dimObjektDto ) {
            $fixes[Sie4Dto::OBJEKT][] = $dimObjektDto;
        }

        // create utf8 sie4String
        $sie4String1    = Sie4EWriter::factory()->process( $sie4Dto );
        $sie4String1Utf = StringUtil::cp437toUtf8( $sie4String1 );

        if( empty( $case )) {
            // first only, save and read from file, compare
            $tmpFilename = tempnam( sys_get_temp_dir(), __FUNCTION__ );
            Sie4EWriter::factory( $sie4Dto )->process( null, $tmpFilename, $sie4Dto->isKsummaSet() );
            $sie4Dto2    = Sie4Parser::factory( $tmpFilename )->process();
            unlink( $tmpFilename );
            $sie4String2    = Sie4EWriter::factory()->process( $sie4Dto2 );
            // compare sie4 strings
            $this->cmpSieStrings( $sie4String1, $sie4String2, __FUNCTION__, ( 200 + $case ) . '-21' );
        }

        /*
        if( empty( $case )) {
            echo 'sie4Dto (case #' . $case . ') has ' . PHP_EOL .
                $sie4Dto->countAccountDtos()   . ' accontDtos'    . PHP_EOL .
                $sie4Dto->countDimDtos()       . ' dimDtos'       . PHP_EOL .
                $sie4Dto->countUnderDimDtos()  . ' underDimDtos'  . PHP_EOL .
                $sie4Dto->countDimObjektDtos() . ' dimObjektDtos' . PHP_EOL .
                $sie4Dto->countIbDtos()        . ' ibDtos'        . PHP_EOL .
                $sie4Dto->countUbDtos()        . ' ibDtos'        . PHP_EOL .
                $sie4Dto->countOibDtos()       . ' oibDtos'       . PHP_EOL .
                $sie4Dto->countOubDtos()       . ' oubDtos'       . PHP_EOL .
                $sie4Dto->countSaldoDtos()     . ' saldoDtos'     . PHP_EOL .
                $sie4Dto->countPsaldoDtos()    . ' pSaldoDtos'    . PHP_EOL .
                $sie4Dto->countPbudgetDtos()   . ' pBudgetDtos'   . PHP_EOL .
                $sie4Dto->countVerDtos()       . ' VerDtos with ' .
                $sie4Dto->countVerTransDtos()  . ' transDtos'     . PHP_EOL . PHP_EOL; // test ###
            echo 'sie4String1' . PHP_EOL . StringUtil::cp437toUtf8( $sie4String1 ) . PHP_EOL . PHP_EOL; // test ###
        }
        */
        $case += 200;

        // assert as Sie4E
        $outcome = true;
        try {
            Sie4Validator::assertSie4EDto( $sie4Dto );
        }
        catch( Exception $e ) {
            $outcome = $e->getMessage();
        }
        $this->assertTrue(
            $outcome,
            sprintf( self::$ERR1, $case, 22, 'E', PHP_EOL, $sie4String1Utf )
        );

        // test convert to array and back and compare sie4Strings
        $sie4Array   = Sie4Dto2Array::process( $sie4Dto );
        // echo var_export( $sie4Array ) . PHP_EOL; // test ###
        $sie4Dto2    = Array2Sie4Dto::process( $sie4Array );
        $sie4String2 = Sie4EWriter::factory()->process( $sie4Dto2 );

        // compare sie4 strings
        $this->cmpSieStrings( $sie4String1, $sie4String2, __FUNCTION__, $case . '-23' );

        // test convert to json and back and compare sie4Strings
        $jsonString = Sie4Dto2Json::process( $sie4Dto );
        $sie4Dto3    = Json2Sie4Dto::process( $jsonString );

        // check $sie4Dto/$sie4Dto3 strings
        $sie4String3    = Sie4EWriter::factory()->process( $sie4Dto3 );
        // compare sie4 strings
        $this->cmpSieStrings( $sie4String1, $sie4String3, __FUNCTION__, $case . '-24' );

        // parse the Sie4E string back and create new Sie4E string, compare
        $sie4String4 = Sie4EWriter::factory()->process(
            Sie4Parser::factory()->process( $sie4String3 )
        );
        // skip opt KSUMMA
        if( $sie4Dto->isKsummaSet()) {
            $sie4String1b = str_contains( $sie4String1, Sie4Dto::KSUMMA)
                ? StringUtil::beforeLast( Sie4Dto::KSUMMA, $sie4String1 )
                : $sie4String1;
            $sie4String4b = str_contains( $sie4String4, Sie4Dto::KSUMMA)
                ? StringUtil::beforeLast( Sie4Dto::KSUMMA, $sie4String4 )
                : $sie4String4;
        }
        else {
            $sie4String1b = $sie4String1;
            $sie4String4b = $sie4String4;
        }
        // compare sie4 strings
        $this->cmpSieStrings( $sie4String1b, $sie4String4b, __FUNCTION__, $case . '-25' );

        // save test-file
        if( isset( $GLOBALS['TESTSAVEDIR'] )) {
            $path      = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . $GLOBALS['TESTSAVEDIR'];
            if( ! is_dir( $path ) && ! mkdir( $path ) && ! is_dir( $path )) {
                throw new RuntimeException( sprintf( 'Directory "%s" don\'t exists', $path ) );
            }
            $saveFileName = $path . DIRECTORY_SEPARATOR . __FUNCTION__ . $case;
            file_put_contents( $saveFileName . '.sie4E', $sie4String4 );
        } // end if save-file

        // prep as Sie4I
        $idDto1 = $sie4Dto3->getIdDto();
        $idDto2 = new IdDto();
        $idDto2->setProsa( $idDto1->getProsa());
        $idDto2->setFtyp( $idDto1->getFtyp());
        $idDto2->setFnrId( $idDto1->getFnrId());
        $idDto2->setOrgnr( $idDto1->getOrgnr());
        // skip Bkod
        $idDto2->setMultiple( $idDto1->getMultiple());
        $idDto2->setAdress( $idDto1->getAdress());
        $idDto2->setFnamn( $idDto1->getFnamn());
        $idDto2->setRarDtos( $idDto1->getRarDtos());
        $idDto2->setTaxar( $idDto1->getTaxar());
        // skip omfattn
        $idDto2->setKptyp( $idDto1->getKptyp());
        $idDto2->setValutakod( $idDto1->getValutakod());

        $sie4Dto3->setIdDto( $idDto2 );

        $sie4Dto3->setIbDtos( [] );
        $sie4Dto3->setUbDtos( [] );
        $sie4Dto3->setOibDtos( [] );
        $sie4Dto3->setOubDtos( [] );
        $sie4Dto3->setSaldoDtos( [] );
        $sie4Dto3->setPsaldoDtos( [] );
        $sie4Dto3->setPbudgetDtos( [] );

        // assert as Sie4I
        $outcome = true;
        try {
            Sie4Validator::assertSie4IDto( $sie4Dto3 );
        }
        catch( Exception $e ) {
            $outcome = $e->getMessage();
        }
        $this->assertTrue(
            $outcome,
            sprintf(
                self::$ERR1,
                $case,
                26,
                'I',
                PHP_EOL,
                StringUtil::cp437toUtf8(
                    Sie4EWriter::factory()->process( $sie4Dto3 ) // note Sie4EWriter
                )
            )
        );

        // write Sie4 string
        $sie4String3 = Sie4IWriter::factory()->process( $sie4Dto3 );

//       error_log( __FUNCTION__ . ' ' . $case . PHP_EOL . $sie4String3 ); // test ###

        // save test-file
        if( isset( $GLOBALS['TESTSAVEDIR'] )) {
            file_put_contents( $saveFileName . '.sie4I', $sie4String3 ); // cp437
        } // end if save-file

        // parse Sie4IDto into SieEntry
        $sieEntry = Sie5EntryLoader::factory( $sie4Dto3 )->getSieEntry();
        $expected = [];
        // validate SieEntry
        $this->assertTrue(
            $sieEntry->isValid( $expected ),
            sprintf( self::$ERR1, $case, 27, 'I', PHP_EOL, var_export( $expected, true ) . PHP_EOL )
        );

        // parse SieEntry into Sie4
        $sie4IDto5 = Sie4ILoader::factory( $sieEntry )->getSie4IDto();
        if( $sie4Dto->isKsummaSet()) {
            $sie4IDto5->setKsumma( 1 );
        }

        // assert as Sie4I
        $outcome = true;
        try {
            Sie4Validator::assertSie4IDto( $sie4IDto5 );
        }
        catch( Exception $e ) {
            $outcome = $e->getMessage();
        }
        $this->assertTrue(
            $outcome,
            sprintf(
                self::$ERR1,
                $case,
                28,
                'I',
                PHP_EOL,
                StringUtil::cp437toUtf8(
                    Sie4EWriter::factory()->process( $sie4Dto3 ) // note Sie4EWriter
                )
            )
        );

        // fixes here to solve sie5/sie5 disparity and to avoid last assert errors
        $sie4IDto5->getIdDto()->setProgramnamn( Sie4Dto::PRODUCTNAME );
        $sie4IDto5->getIdDto()->setVersion( Sie4Dto::PRODUCTVERSION );
        $sie4IDto5->getIdDto()->setSign( $fixes[Sie4Dto::GENSIGN] );
        $sie4IDto5->getIdDto()->setProsa( $fixes[Sie4Dto::PROSA] );
        $sie4IDto5->getIdDto()->setFtyp( $fixes[Sie4Dto::FTYP] );
        $sie4IDto5->getIdDto()->setAdress( $fixes[Sie4Dto::ADRESS] );
        $sie4IDto5->getIdDto()->setRarDtos( $fixes[Sie4Dto::RAR] );
        $sie4IDto5->getIdDto()->setTaxar( $fixes[Sie4Dto::TAXAR] );
        $sie4IDto5->getIdDto()->setKptyp( $fixes[Sie4Dto::KPTYPE] );
        $sie4IDto5->setSruDtos( $fixes[Sie4Dto::SRU] );

        $sie4IDto5->setDimDtos( $fixes[Sie4Dto::DIMENSIONNR] );
        $sie4IDto5->setUnderDimDtos( $fixes[Sie4Dto::UNDERDIM] );
        $sie4IDto5->setDimObjektDtos( $fixes[Sie4Dto::OBJEKT] );

        // Remove non-mandatory #KTYP
        foreach( $sie4Dto3->getAccountDtos()->get() as $accountDto ) {
            $accountDto->setKontoTyp();
        }
        $sie4String3Utf = StringUtil::cp437toUtf8(
            Sie4IWriter::factory()->process( $sie4Dto3 )
        );
        // Remove non-mandatory #KTYP
        foreach( $sie4IDto5->getAccountDtos() as $accountDto ) {
            $accountDto->setKontoTyp();
        }
        // write Sie4 string
        $sie4String5 = Sie4IWriter::factory()->process( $sie4IDto5 );

        // final compare of Sie4I AND Sie5 (SieEntry from Sie4I),
        // WILL? return errors due to Sie4/Sie5 disparity, ex non-mandatory #KTYP
        // compare sie4 strings
        $this->cmpSieStrings( $sie4String3, $sie4String5, __FUNCTION__, $case . '-29' );
    }

    /*
     * The genTest11 testProvider
     */
    public static function genTestProvider11() : array
    {
        static $decimal_separator   = '.';
        static $thousands_separator = '';
        $case     = 200;
        $dataArr  = [];
        for( $decimal = 0.98; $decimal <= 1.02; $decimal += 0.01 ) {
            $case2     = ++$case * 10;
            // test as float
            $belopp1   = -2000 - $decimal;
            $belopp2   = 1600 + $decimal;
            $kvantitet = ( -1 * ( $belopp1 / 1000 ));
            $dataArr[] = [ ++$case2, self::getSieDto( $case2, $belopp1, $belopp2, $kvantitet ) ];
            // test as float in string
            $belopp1   = number_format( $belopp1, 2, $decimal_separator, $thousands_separator );
            $belopp2   = number_format( $belopp2, 2, $decimal_separator, $thousands_separator );
            $kvantitet = ( 0.0 === fmod( $kvantitet, (float) ((int) $kvantitet )))
                ? number_format( $kvantitet, 6, $decimal_separator, $thousands_separator )
                : (int) $kvantitet;
            $dataArr[] = [ ++$case2, self::getSieDto( $case2, $belopp1, $belopp2, $kvantitet ) ];
        } // end for
        return $dataArr;
    }

    /**
     * @param int          $case
     * @param float|string $belopp1
     * @param float|string $belopp2
     * @param float|string $kvantitet
     * @return Sie4Dto
     * @throws Exception
     */
    private static function getSieDto(
        int $case,
        float|string $belopp1,
        float|string $belopp2,
        float|string $kvantitet
    )
    {
        $belopp3 = 0 - (float) $belopp1 - (float) $belopp2;
        return Sie4Dto::factory( 'Acme Corp', (string) $case, '556334-3689' )
            ->addVerDto(
                VerDto::factory( $case, 'Porto' )
                    ->addTransDto(
                        TransDto::factory( 1910, $belopp1 )
                            ->setKvantitet( $kvantitet )
                    )
                    ->addTransDto( TransDto::factory( 2640, $belopp2 ))
                    ->addTransDto( TransDto::factory( 6250, $belopp3 ))
            );
    }

    /**
     * Test input TransDto::belopp/kvantitet as float/string
     *
     * @test
     * @dataProvider genTestProvider11
     *
     * @param int $case
     * @param Sie4Dto $sie4Dto
     * @return void
     * @throws Exception
     */
    public function genTest11( int $case, Sie4Dto $sie4Dto ) : void
    {
        // create cp437 sie4String
        $sie4String1 = Sie4IWriter::factory()->process( $sie4Dto );
        // parse the Sie4 string back and create new Sie4 string
        $sie4String2 = Sie4IWriter::factory()->process(
            Sie4Parser::factory()->process( $sie4String1 )
        );
        // compare sie4 strings
        $this->cmpSieStrings( $sie4String1, $sie4String2, __FUNCTION__, (string) $case );
    }
}
