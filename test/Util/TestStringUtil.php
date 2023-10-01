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

use PHPUnit\Framework\TestCase;

class TestStringUtil extends TestCase
{
    /**
     * Testing StringUtil::trimString
     *
     * @test
     */
    public function trimStringTest() : void
    {
        $exp   = 'aa bb cc';
        $value = ' aa  bb  cc ';
        $this->assertEquals(
            $exp,
            StringUtil::trimString( $value ),
            __FUNCTION__ . ' error, exp: ' . $exp . ', got:  ' . $value
        );
    }

    /**
     * Testing StringUtil::after
     *
     * @test
     */
    public function afterTest() : void
    {
        $exp      = '  cc ';
        $needle   = 'bb';
        $haystack = ' aa  bb  cc ';
        $result   = StringUtil::after( $needle, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #1, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );

        $exp      = '';
        $needle   = 'dd';
        $haystack = ' aa  bb  cc ';
        $result   = StringUtil::after( $needle, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #2, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );
    }

    /**
     * Testing StringUtil::afterLast
     *
     * @test
     */
    public function afterLastTest() : void
    {
        $exp      = '  c2 ';
        $needle   = '  bb';
        $haystack = ' aa  bb  c1  bb  c2 ';
        $result   = StringUtil::afterLast( $needle, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #1, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );

        $exp      = '';
        $needle   = 'dd';
        $haystack = ' aa  bb  aa  bb  cc ';
        $result   = StringUtil::afterLast( $needle, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #2, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );
    }

    /**
     * Testing StringUtil::before
     *
     * @test
     */
    public function beforeTest() : void
    {
        $exp      = ' aa';
        $needle   = '  bb';
        $haystack = ' aa  bb  c1  bb  c2 ';
        $result   = StringUtil::before( $needle, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #1, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );

        $exp      = ' aa  bb';
        $needle   = '  cc';
        $haystack = ' aa  bb  cc  bb  cc ';
        $result   = StringUtil::before( $needle, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #2, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );

        $exp      = '';
        $needle   = 'dd';
        $haystack = ' aa  bb  aa  bb  cc ';
        $result   = StringUtil::before( $needle, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #3, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );
    }

    /**
     * Testing StringUtil::beforeLast
     *
     * @test
     */
    public function beforeLastTest() : void
    {
        $exp      = ' aa  bb  c1';
        $needle   = '  bb';
        $haystack = ' aa  bb  c1  bb  c2 ';
        $result   = StringUtil::beforeLast( $needle, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #1, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );

        $exp      = '';
        $needle   = 'dd';
        $haystack = ' aa  bb  aa  bb  cc ';
        $result   = StringUtil::beforeLast( $needle, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #3, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );
    }

    /**
     * Testing StringUtil::between
     *
     * @test
     */
    public function betweenTest() : void
    {
        $exp      = '';
        $needle1  = '  b3';
        $needle2  = '  b4';
        $haystack = ' aa  b1  c1  b2  c2 ';
        $result   = StringUtil::between( $needle1, $needle2, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #1, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );

        $exp      = '  c1  b2  c2 ';
        $needle1  = '  b1';
        $needle2  = '  b4';
        $haystack = ' aa  b1  c1  b2  c2 ';
        $result   = StringUtil::between( $needle1, $needle2, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #2, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );

        $exp      = ' aa  b1  c1';
        $needle1  = '  b3';
        $needle2  = '  b2';
        $haystack = ' aa  b1  c1  b2  c2 ';
        $result   = StringUtil::between( $needle1, $needle2, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #3, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );

        $exp      = '  c1';
        $needle1  = '  b1';
        $needle2  = '  b2';
        $haystack = ' aa  b1  c1  b2  c2 ';
        $result   = StringUtil::between( $needle1, $needle2, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #4, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );
    }

    /**
     * Testing StringUtil::betweenLast
     *
     * @test
     */
    public function betweenLastTest() : void
    {
        $exp      = '  b2';
        $needle1  = '  a1';
        $needle2  = '  c1';
        $haystack = ' a1  b1  c1  a1  b2  c1 ';
        $result   = StringUtil::betweenLast( $needle1, $needle2, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #1, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );

        $exp      = '';
        $needle1  = '  a1';
        $needle2  = '  c3';
        $haystack = ' a1  b1  c1  a1  b2  c1 ';
        $result   = StringUtil::betweenLast( $needle1, $needle2, $haystack );
        $this->assertEquals(
            $exp,
            $result,
            __FUNCTION__ . ' error #1, exp: ->' . $exp . '<->, got ->' . $result . '<-'
        );
    }
}
