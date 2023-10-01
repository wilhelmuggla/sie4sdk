<?php
/**
 * Sie4Sdk   PHP Sie4 SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Sdk
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021-2023 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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

use Exception;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\DimDto;
use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Dto\UnderDimDto;
use Kigkonsult\Sie4Sdk\Rsc\AccountList;
use Kigkonsult\Sie4Sdk\Rsc\DimList;
use Kigkonsult\Sie4Sdk\Rsc\DimObjektList;
use Kigkonsult\Sie4Sdk\Rsc\Loader\AccountListCsvFileLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\AccountListLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\DimListCsvFileLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\DimListLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\DimObjektListCsvFileLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\DimObjektListLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\UnderDimListCsvFileLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\UnderDimListLoader;
use Kigkonsult\Sie4Sdk\Rsc\RscAccountValidator;
use Kigkonsult\Sie4Sdk\Rsc\RscDimensionValidator;
use Kigkonsult\Sie4Sdk\Rsc\UnderDimList;
use PHPUnit\Framework\TestCase;

class TestRsc extends TestCase
{
    /**
     * Tests Rsc/AccountListCsvFileLoader, Rsc/AccountLoader and Rsc/AccountList, includes some AsittagList tests...
     *
     * @test
     *
     * @return void
     */
    public function accountListLoadTest() : void
    {
        $file        = __DIR__ . '/Rsc_files/kontoplan.csv';
        $load        = AccountListCsvFileLoader::factory( [ $file, ',', '"' ] )->getOutput();
        $accountList = AccountListLoader::load( $load );
        // test $accounts class type
        $this->assertInstanceOf(
            AccountList::class,
            $accountList,
            '#1011, NOT instanceof ' . AccountList::class . ', is instanceof ' . get_class( $accountList )
        );
        // test $accounts element value type
        $this->assertEquals(
            AccountDto::class,
            $accountList->getValueType(),
            '#1012, expects valueType ' . AccountDto::class . ', got ' . $accountList->getValueType()
        );
        // test count load
        $this->assertNotEmpty(
            $accountList->count(),
            '#1013, accountRsc empty ' . var_export( $accountList, true )
        );
        $firstAccountDto = reset( $load );
        // test tag output
        $cnt = 0;
        foreach( $accountList->tagGet( AccountDto::I ) as $accountDto ) {
            $this->assertEquals(
                AccountDto::I, $accountDto->getKontoTyp(), '#1014, kontoNr ' . $accountDto->getKontoNr() . ' expected kontoyp I, actual ' . $accountDto->getKontoTyp()
            );
            ++$cnt;
        } // end foreach
        $this->assertNotEmpty( $cnt, ' NO  kontoyp I found' );

        // echo 'test AccountDto (I) ' . var_export( $accountDto, true ) . PHP_EOL; // test
        // echo 'test AsittagList ' . var_export( array_slice( $accounts->get(),0, 2, true ), true ) . PHP_EOL; // test

        // test element value type
        $accountList->rewind();
        $this->assertInstanceOf(
            AccountDto::class, $accountList->current(), '#1015, current NOT instanceof ' . AccountDto::class
        );

        // test kontoNr is set
        $this->assertTrue(
            $accountList->isKontoNrSet( $firstAccountDto->getKontoNr() ),
            '#1016 error, kontoNr ' . $firstAccountDto->getKontoNr() . ' do not exists'
        );
        // test method exists
        $this->assertTrue(
            method_exists( $accountList, 'getAccountDto' ),
            '#1017 error, method getAccountDto do not exists'
        );
        // echo '#1018, konto 1011, ' . var_export( $accountList->getAccountDto( '1011' ), true ) . PHP_EOL; // test
        $expKontoNr = $firstAccountDto->getKontoNr();
        $actKontoNr = $accountList->pKeySeek( $expKontoNr)->current()->getKontoNr();
        $this->assertEquals(
            $expKontoNr,
            $actKontoNr,
            '#1019 exp kontoNr ' . $expKontoNr . ' NOT found in current, got ' . $actKontoNr
        );

        // test setting of invalid element type
        $outcome = true;
        try {
            $accountList->append( [] );
        } catch( Exception $e ) {
            $outcome = false;
        }
        $this->assertFalse( $outcome, '#1020 error load type' );

        $rscAccountValidator = RscAccountValidator::factory( $accountList );
        $transDtos = [];
        foreach( $accountList as $accountDto ) {
            $transDtos[] = TransDto::factory( $accountDto->getKontoNr(), 1.00 );
        }
        $ok = true;
        try {
            $rscAccountValidator->assertKontoNrs( $transDtos );
        }
        catch( Exception $e ) {
            $ok = $e->getMessage();
        }
        $this->assertTrue(
            $ok,
            '#1021 RscAccountValidator false !! ' . $ok
        );
        $this->assertFalse(
            $rscAccountValidator->kontoNrExists( '99' ),
            '#2022 RscAccountValidator : kontoNr 99 exp not found'
        );
    }

    /**
     * Tests Rsc/DimListCsvFileLoader, Rsc/DimListLoader and Rsc/DimList, includes some AsittagList tests...
     *
     * @test
     *
     * @return void
     */
    public function dimListLoadTest() : void
    {
        $file    = __DIR__ . '/Rsc_files/dimensioner.csv';
        $load    = DimListCsvFileLoader::factory( [ $file, ',', '"' ] )->getOutput();
        $dimList = DimListLoader::load( $load );
        // test $dimList class type
        $this->assertInstanceOf(
            DimList::class, $dimList, '#2011, NOT instanceof ' . DimList::class . ', is instanceof ' . get_class( $dimList )
        );
        // test $dimList element value type
        $this->assertEquals(
            DimDto::class,
            $dimList->getValueType(),
            '#2012, expects valueType ' . DimDto::class . ', got ' . $dimList->getValueType()
        );
        // test count load
        $this->assertNotEmpty(
            $dimList->count(),
            '#2013, dimList empty ' . var_export( $dimList, true )
        );
        $firstDimDto = reset( $load );

        // echo 'test AsittagList ' . var_export( array_slice( $dimList->get(),0, 2, true ), true ) . PHP_EOL; // test

        // test element value type
        $dimList->rewind();
        $this->assertInstanceOf(
            DimDto::class, $dimList->current(), '#2015, current NOT instanceof ' . DimDto::class
        );

        // test DimensionNr is set
        $this->assertTrue(
            $dimList->isDimensionNrSet( $firstDimDto->getDimensionNr()),
            '#2016 error, DimensionNr ' . $firstDimDto->getDimensionNr() . ' do not exists'
        );
        // test method exists
        $this->assertTrue(
            method_exists( $dimList, 'getDimDto' ),
            '#2017 error, method getDimDto do not exists'
        );
        // echo '#2018, pkey 1,  ' . var_export( $dimList->pKeyGet('1' ), true ) . PHP_EOL; // test

        // test setting of invalid element type
        $outcome = true;
        try {
            $dimList->append( [] );
        } catch( Exception $e ) {
            $outcome = false;
        }
        $this->assertFalse( $outcome, '#2020 error load type' );

        // test RscDimensionValidator with dimDtos and (faked) $underDimDto
        $dimDtos      = $underDimDtos = [];
        $underDimList = new UnderDimList();
        foreach( $dimList as $dimDto ) {
            $dimDtos[]   = $dimDto;
            $underDimDto = UnderDimDto::factoryUnderDim(
                1,
                'dimension 1 namn',
                $dimDto->getDimensionNr()
            );
            $underDimList->append( $underDimDto );
            $underDimDtos[] = $underDimDto;
        }
        $rscDimensionValidator = RscDimensionValidator::factory( $dimList, $underDimList );
        $ok = true;
        try {
            $rscDimensionValidator->assertDimNrs( $dimDtos );
            $rscDimensionValidator->assertDimNrs( $underDimDtos );
            $rscDimensionValidator->assertUnderDimDto( $underDimDtos );
        }
        catch( Exception $e ) {
            $ok = $e->getMessage();
        }
        $this->assertTrue(
            $ok,
            '#2021 RscDimensionValidator false !! ' . $ok
        );
        $this->assertFalse(
            $rscDimensionValidator->dimNrExists( 99 ),
            '#2022 RscDimensionValidator : dim 99 exp NOT found'
        );
        $this->assertFalse(
            $rscDimensionValidator->underDimNrExists( 99 ),
            '#2023 RscDimensionValidator : underDim 99 exp NOT found'
        );
    }

    /**
     * Tests Rsc/UnderDimListCsvFileLoader, Rsc/UnderDimListLoader and Rsc/UnderDimList, includes some AsittagList tests...
     *
     * @test
     *
     * @return void
     */
    public function underDimListLoadTest() : void
    {
        $file         = __DIR__ . '/Rsc_files/underDimensioner.csv';
        $load         = UnderDimListCsvFileLoader::factory( [ $file, ',', '"' ] )->getOutput();
        $underDimList = UnderDimListLoader::load( $load );
        // test $underDimList class type
        $this->assertInstanceOf(
            UnderDimList::class, $underDimList, '#3011, NOT instanceof ' . UnderDimList::class . ', is instanceof ' . get_class( $underDimList )
        );
        // test $dimList element value type
        $this->assertEquals(
            UnderDimDto::class,
            $underDimList->getValueType(),
            '#3012, expects valueType ' . UnderDimDto::class . ', got ' . $underDimList->getValueType()
        );
        // test count load
        $this->assertNotEmpty(
            $underDimList->count(),
            '#3013, underDimList empty ' . var_export( $underDimList, true )
        );
        $firstDimDto     = reset( $load );
        $firstSuperDimNr = $firstDimDto->getSuperDimNr();
        $this->assertCount(
            3, $underDimList->getUnderDimDtosForSuper( $firstSuperDimNr ), '#3014, underDimList for ' . $firstSuperDimNr . ' empty, 3 exp, ' . var_export( $underDimList, true )
        );

        // echo 'test AsittagList ' . var_export( array_slice( $underDimList->get(),0, 2, true ), true ) . PHP_EOL; // test

        // test element value type
        $underDimList->rewind();
        $this->assertInstanceOf(
            UnderDimDto::class, $underDimList->current(), '#3015, current NOT instanceof ' . UnderDimDto::class
        );

        // test DimensionNr is set
        $this->assertTrue(
            $underDimList->isDimensionNrSet( $firstDimDto->getDimensionNr()),
            '#3016 error, underDimensionNr ' . $firstDimDto->getDimensionNr() . ' do not exists'
        );
        // test method exists
        $this->assertTrue(
            method_exists( $underDimList, 'getUnderDimDto' ),
            '#3017 error, method getUnderDimDto do not exists'
        );
        // echo '#3018, pkey 1,  ' . var_export( $underDimList->pKeyGet('1' ), true ) . PHP_EOL; // test ??

        // test setting of invalid element type
        $outcome = true;
        try {
            $underDimList->append( [] );
        } catch( Exception $e ) {
            $outcome = false;
        }
        $this->assertFalse( $outcome, '#3020 error load type' );
    }

    /**
     * Tests Rsc/DimObjektListCsvFileLoader, Rsc/DimObjektListLoader and Rsc/DimObjektList, includes some AsittagList tests...
     *
     * @test
     *
     * @return void
     */
    public function dimObjektListLoadTest() : void
    {
        $file          = __DIR__ . '/Rsc_files/objekt.csv';
        $load          = DimObjektListCsvFileLoader::factory( [ $file, ',', '"' ] )->getOutput();
        $dimObjektList = DimObjektListLoader::load( $load );
        // test $dimList class type
        $this->assertInstanceOf(
            DimObjektList::class, $dimObjektList, '#4011, NOT instanceof ' . DimObjektList::class . ', is instanceof ' . get_class( $dimObjektList )
        );
        // test $dimList element value type
        $this->assertEquals(
            DimObjektDto::class,
            $dimObjektList->getValueType(),
            '#4012, expects valueType ' . DimObjektDto::class . ', got ' . $dimObjektList->getValueType()
        );
        // test count load
        $this->assertNotEmpty(
            $dimObjektList->count(),
            '#4013, dimObjektList empty ' . var_export( $dimObjektList, true )
        );
        $firstDimObjektDto = reset( $load );

        // echo 'test AsittagList ' . var_export( array_slice( $dimObjektList->get(),0, 2, true ), true ) . PHP_EOL; // test

        // test element value type
        $dimObjektList->rewind();
        $this->assertInstanceOf(
            DimObjektDto::class, $dimObjektList->current(), '#4015, current NOT instanceof ' . DimObjektDto::class
        );

        // test dimensionNr/objektNr is set
        $this->assertTrue(
            $dimObjektList->isObjektNrSet( $firstDimObjektDto->getDimensionNr(), $firstDimObjektDto->getObjektNr()),
            '#4016 error, DimensionNr ' . $firstDimObjektDto->getDimensionNr() .
            '  objektNr ' . $firstDimObjektDto->getobjektNr() . ' do not exists'
        );
        // test method exists
        $this->assertTrue(
            method_exists( $dimObjektList, 'getDimObjektDto' ),
            '#4017 error, method getDimObjektDto do not exists'
        );

        // test setting of invalid element type
        $outcome = true;
        try {
            $dimObjektList->append( [] );
        } catch( Exception $e ) {
            $outcome = false;
        }
        $this->assertFalse( $outcome, '#4020 error load type' );
    }
}
