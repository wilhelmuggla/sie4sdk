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
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\DimDto;
use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Dto\UnderDimDto;
use Kigkonsult\Sie4Sdk\Lists\AccountDtoList;
use Kigkonsult\Sie4Sdk\Lists\DimDtoList;
use Kigkonsult\Sie4Sdk\Lists\DimObjektDtoList;
use Kigkonsult\Sie4Sdk\Lists\UnderDimDtoList;
use Kigkonsult\Sie4Sdk\Rsc\Loader\AccountDtoListLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\Csv\AccountDtoListCsvFileLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\Csv\DimDtoListCsvFileLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\Csv\DimObjektDtoListCsvFileLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\Csv\UnderDimDtoListCsvFileLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\DimDtoListLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\DimObjektDtoListLoader;
use Kigkonsult\Sie4Sdk\Rsc\Loader\UnderDimDtoListLoader;
use PHPUnit\Framework\TestCase;

class TestRsc extends TestCase
{
    /**
     * Tests Rsc/Loader/AccountDtoListCsvFileLoader, Rsc/Loader/AccountDtoListLoader and Lists/AccountDtoList, includes some AsittagList tests...
     *
     * @test
     *
     * @return void
     */
    public function accountDtoListLoadTest() : void
    {
        $file           = __DIR__ . '/Rsc_files/kontoplan.csv';
        $accountDtoList = AccountDtoListLoader::load(
            AccountDtoListCsvFileLoader::factory( [ $file, ',', '"' ] )
        );
        /*
            $accountDtoList = AccountDtoListLoader::load(
            AccountDtoListCsvFileLoader::class,
            [ $file, ',', '"' ]
        );
        */
        // test $accounts class type
        $this->assertInstanceOf(
            AccountDtoList::class,
            $accountDtoList,
            __FUNCTION__ . '#1011, NOT instanceof ' . AccountDtoList::class . ', is instanceof ' . get_class( $accountDtoList )
        );
        // test $accounts element value type
        $this->assertEquals(
            AccountDto::class,
            $accountDtoList->getValueType(),
            __FUNCTION__ . '#1012, expects valueType ' . AccountDto::class . ', got ' . $accountDtoList->getValueType()
        );
        // test count load
        $this->assertNotEmpty(
            $accountDtoList->count(),
            __FUNCTION__ . '#1013, accountRsc empty ' . var_export( $accountDtoList, true )
        );
        $accountDtoList->rewind();;
        $firstAccountDto = $accountDtoList->current();
        // test tag output
        $cnt = 0;
        foreach( $accountDtoList->tagGet( AccountDto::I ) as $accountDto ) {
            $this->assertEquals(
                AccountDto::I,
                $accountDto->getKontoTyp(),
                __FUNCTION__ . '#1014, kontoNr ' . $accountDto->getKontoNr() . ' expected kontoyp I, actual ' . $accountDto->getKontoTyp()
            );
            ++$cnt;
        } // end foreach
        $this->assertNotEmpty( $cnt, __FUNCTION__ . ' NO  kontoyp I found' );

        // test element value type
        $accountDtoList->rewind();
        $this->assertInstanceOf(
            AccountDto::class,
            $accountDtoList->current(),
            __FUNCTION__ . '#1016, current NOT instanceof ' . AccountDto::class
        );

        // test kontoNr is set
        $expKontoNr = $firstAccountDto->getKontoNr();
        $this->assertTrue(
            $accountDtoList->pKeyExists( $expKontoNr ),
            __FUNCTION__ . '#1017 error, kontoNr ' . $firstAccountDto->getKontoNr() . ' do not exists'
        );
        $current    = $accountDtoList->pKeySeek( $expKontoNr )->current();
        $actKontoNr = $current->getKontoNr();
        $this->assertEquals(
            $expKontoNr,
            $actKontoNr,
            __FUNCTION__ . '#1019 exp kontoNr ' . $expKontoNr . ' NOT found in current, got ' . $actKontoNr
        );
        $expKontoNamn = $firstAccountDto->getKontoNamn();
        $actKontoNamn = $current->getKontoNamn();
        $this->assertEquals(
            $expKontoNamn,
            $actKontoNamn,
            __FUNCTION__ . '#1020 exp kontoNr ' . $expKontoNr . ' name : ' . $expKontoNamn . ' NOT found in current, got ' . $actKontoNamn
        );
        $expKontoTyp = $firstAccountDto->getKontoTyp();
        $actKontoTyp = $current->getKontoTyp();
        $this->assertEquals(
            $expKontoTyp,
            $actKontoTyp,
            __FUNCTION__ . '#1021 exp kontoNr ' . $expKontoNr . ' typ : ' . $expKontoTyp . ' NOT found in current, got ' . $actKontoTyp
        );
        $this->assertTrue(
            $accountDtoList->isKontoNrSet( $firstAccountDto ),
            __FUNCTION__ . '#1022 exp kontoNr ' . $expKontoNr . ' NOT found in AccountList'
        );

        // test setting of invalid element type
        $outcome = true;
        try {
            $accountDtoList->append( [] );
        }
        catch( Exception $e ) {
            $outcome = false;
        }
        $this->assertFalse( $outcome, __FUNCTION__ . '#1020 error load type' );

        $this->assertFalse(
            $accountDtoList->pKeyExists( '99' ),
            __FUNCTION__ . '#2025 AccountDtoList :  expkontoNr 99 not found'
        );
    }

    /**
     * Tests Rsc/DimDtoListCsvFileLoader, Rsc/DimDtoListLoader and Lists/DimDtoList, includes some AsitList tests...
     *
     * @test
     *
     * @return void
     */
    public function dimDtoListLoadTest() : void
    {
        $file    = __DIR__ . '/Rsc_files/dimensioner.csv';
        $dimDtoList = DimDtoListLoader::load(
            DimDtoListCsvFileLoader::class,
            [ $file, ',', '"' ]
        );
        // test $dimList class type
        $this->assertInstanceOf(
            DimDtoList::class,
            $dimDtoList,
            '#2011, NOT instanceof ' . DimDtoList::class . ', is instanceof ' . get_class( $dimDtoList )
        );
        // test $dimList element value type
        $this->assertEquals(
            DimDto::class,
            $dimDtoList->getValueType(),
            '#2012, expects valueType ' . DimDto::class . ', got ' . $dimDtoList->getValueType()
        );
        // test count load
        $this->assertNotEmpty(
            $dimDtoList->count(),
            '#2013, dimList empty ' . var_export( $dimDtoList, true )
        );
        $dimDtoList->rewind();
        $firstDimDto = $dimDtoList->current();

        // test element value type
        $dimDtoList->rewind();
        $this->assertInstanceOf(
            DimDto::class,
            $dimDtoList->current(),
            '#2015, current NOT instanceof ' . DimDto::class
        );

        // test DimensionNr is set
        $this->assertTrue(
            $dimDtoList->isDimensionNrSet( $firstDimDto->getDimensionNr()),
            '#2016 error, DimensionNr ' . $firstDimDto->getDimensionNr() . ' do not exists'
        );

        // test setting of invalid element type
        $outcome = true;
        try {
            $dimDtoList->append( [] );
        }
        catch( Exception $e ) {
            $outcome = false;
        }
        $this->assertFalse( $outcome, '#2020 error load type' );
    }

    /**
     * Tests Rsc/UnderDimDtoListCsvFileLoader, Rsc/UnderDimDtoListLoader and Lists/UnderDimDtoList, includes some AsittagList tests...
     *
     * @test
     *
     * @return void
     */
    public function underDimDtoListLoadTest() : void
    {
        $file         = __DIR__ . '/Rsc_files/underDimensioner.csv';
        $underDimDtoList = UnderDimDtoListLoader::load(
            UnderDimDtoListCsvFileLoader::class,
            [ $file, ',', '"' ]
        );
        // test $underDimDtoList class type
        $this->assertInstanceOf(
            UnderDimDtoList::class,
            $underDimDtoList,
            '#3011, NOT instanceof ' . UnderDimDtoList::class . ', is instanceof ' . get_class( $underDimDtoList )
        );
        // test $underDimDtoList element value type
        $this->assertEquals(
            UnderDimDto::class,
            $underDimDtoList->getValueType(),
            '#3012, expects valueType ' . UnderDimDto::class . ', got ' . $underDimDtoList->getValueType()
        );
        // test count load
        $this->assertNotEmpty(
            $underDimDtoList->count(),
            '#3013, underDimDtoList empty ' . var_export( $underDimDtoList, true )
        );
        $underDimDtoList->rewind();
        $firstUnderDimDto   = $underDimDtoList->current();
        $firstSuperDimNr    = $firstUnderDimDto->getSuperDimNr();
        $firstDimensionNr   = $firstUnderDimDto->getDimensionNr();
        $firstDimensionNamn = $firstUnderDimDto->getDimensionNamn();
        $this->assertCount(
            3,
            $underDimDtoList->getUnderDimDtosForSuper( $firstSuperDimNr ),
            '#3014, underDimDtoList for ' . $firstSuperDimNr . ' empty, 3 exp, ' . var_export( $underDimDtoList, true )
        );

        // test element value type
        $underDimDtoList->rewind();
        $this->assertInstanceOf(
            UnderDimDto::class,
            $underDimDtoList->current(),
            '#3015, current NOT instanceof ' . UnderDimDto::class
        );

        // test DimensionNr is set
        $this->assertTrue(
            $underDimDtoList->isDimensionNrSet( $firstDimensionNr ),
            '#3016 error, underDimensionNr ' . $firstUnderDimDto->getDimensionNr() . ' do not exists'
        );
        // test DimensionNamn
        $dimensionNamn = $underDimDtoList->pKeySeek( $firstDimensionNr )->current()->getDimensionNamn();
        $this->assertSame(
            $firstDimensionNamn,
            $dimensionNamn,
            '#3016 error, underDimensionNamn ' . $firstDimensionNamn . ' NOT equals ' . $dimensionNamn
        );

        // test setting of invalid element type
        $outcome = true;
        try {
            $underDimDtoList->append( [] );
        }
        catch( Exception $e ) {
            $outcome = false;
        }
        $this->assertFalse( $outcome, '#3020 error load type' );
    }

    /**
     * Tests Rsc/DimObjektDtoListCsvFileLoader, Rsc/DimObjektDtoListLoader and Lists/DimObjektDtoList, includes some AsitList tests...
     *
     * @test
     *
     * @return void
     */
    public function dimObjektDtoListLoadTest() : void
    {
        $file          = __DIR__ . '/Rsc_files/objekt.csv';
        $dimObjektDtoList = DimObjektDtoListLoader::load(
            DimObjektDtoListCsvFileLoader::class,
            [ $file, ',', '"' ]
        );
        // test $dimList class type
        $this->assertInstanceOf(
            DimObjektDtoList::class,
            $dimObjektDtoList,
            '#4011, NOT instanceof ' . DimObjektDtoList::class . ', is instanceof ' . get_class( $dimObjektDtoList )
        );
        // test $dimList element value type
        $this->assertEquals(
            DimObjektDto::class,
            $dimObjektDtoList->getValueType(),
            '#4012, expects valueType ' . DimObjektDto::class . ', got ' . $dimObjektDtoList->getValueType()
        );
        // test count load
        $this->assertNotEmpty(
            $dimObjektDtoList->count(),
            '#4013, dimObjektDtoList empty ' . var_export( $dimObjektDtoList, true )
        );

        // test element value type
        $dimObjektDtoList->rewind();
        $firstDimObjektDto = $dimObjektDtoList->current();

        $this->assertInstanceOf(
            DimObjektDto::class,
            $dimObjektDtoList->current(),
            '#4015, current NOT instanceof ' . DimObjektDto::class
        );

        // test dimensionNr/objektNr is set
        $this->assertTrue(
            $dimObjektDtoList->isObjektNrSet( $firstDimObjektDto->getDimensionNr(), $firstDimObjektDto->getObjektNr()),
            '#4016 error, DimensionNr ' . $firstDimObjektDto->getDimensionNr() .
            '  objektNr ' . $firstDimObjektDto->getobjektNr() . ' do not exists'
        );

        // test setting of invalid element type
        $outcome = true;
        try {
            $dimObjektDtoList->append( [] );
        }
        catch( Exception $e ) {
            $outcome = false;
        }
        $this->assertFalse( $outcome, '#4020 error load type' );
    }
}
