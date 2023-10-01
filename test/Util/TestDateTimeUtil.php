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
namespace Kigkonsult\Sie4Sdk\Util;

use Exception;
use PHPUnit\Framework\TestCase;

class TestDateTimeUtil extends TestCase
{
    /**
     * Testing DateTimeUtil::getDateTime exception
     *
     * @test
     */
    public function getDateTimeTest() : void
    {
        $ok    = false;
        $value = 'test';
        try {
            DateTimeUtil::getDateTime( $value, 'test', 1 );
        }
        catch ( Exception ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error #1 with value ' . $value );

        $ok    = false;
        $value = '-123456';
        try {
            DateTimeUtil::getDateTime( $value, 'test', 2 );
        }
        catch ( Exception ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error #2 with value ' . $value );
    }

    /**
     * Testing DateTimeUtil::assertTimestamp exception
     *
     * @ test    seems to accept any float...
     */
    public function assertTimestampTest() : void
    {
        $ok     = false;
        $value  = PHP_FLOAT_MAX;
        try {
            DateTimeUtil::assertTimestamp( $value, 1 );
        }
        catch ( Exception ) {
            $ok = true;
        }
        $this->assertTrue(
            $ok,
            __FUNCTION__ . ' error #1 with value ' . $value .
            ' and result ' .
            DateTimeUtil::getDateTime( '@' . (int) $value, 'test', 1 )->format( 'c' )
        );
    }

    /**
     * Testing DateTimeUtil::gYearMonthFromString
     *
     * @test
     */
    public function gYearMonthFromStringTest() : void
    {
        $this->assertEquals(
            'yyyy-mm',
            DateTimeUtil::gYearMonthFromString( 'yyyymm' )
        );
    }

    /**
     * Testing DateTimeUtil::YYYYmmFromgYearMonth
     *
     * @test
     */
    public function YYYYmmFromgYearMonthTest() : void
    {
        $this->assertEquals(
            'yyyymm',
            DateTimeUtil::YYYYmmFromgYearMonth( 'yyyy-mm' )
        );
    }

    /**
     * Testing DateTimeUtil::gYearMonthToDateTime exception
     *
     * @test
     */
    public function gYearMonthToDateTimeTest() : void
    {
        $ok     = false;
        $value  = 'yyyy-mm';
        try {
            DateTimeUtil::gYearMonthToDateTime( $value, false );
        }
        catch ( Exception ) {
            $ok = true;
        }
        $this->assertTrue(
            $ok,
            __FUNCTION__ . ' error #1 with value ' . $value
        );

        $ok     = false;
        $value  = '2023-13';
        try {
            DateTimeUtil::gYearMonthToDateTime( $value, false );
        }
        catch ( Exception ) {
            $ok = true;
        }
        $this->assertTrue(
            $ok,
            __FUNCTION__ . ' error #2 with value ' . $value
        );
    }
}
