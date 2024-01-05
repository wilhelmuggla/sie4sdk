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
namespace Kigkonsult\Sie4Sdk\Util;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TestAssert extends TestCase
{
    /**
     * Testing Assert::isNonPositiveInt exception
     *
     * @test
     */
    public function isNonPositiveIntTest() : void
    {
        $ok    = false;
        $value = 1;
        try {
            Assert::isNonPositiveInt( 'test', $value );
        }
        catch ( InvalidArgumentException ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error with value ' . $value );
    }

    /**
     * Testing Assert::isIntegerish exception
     *
     * @test
     */
    public function isIntegerishTest() : void
    {
        $ok    = false;
        $value = 'a';
        try {
            Assert::isIntegerish( 'test', $value );
        }
        catch ( InvalidArgumentException ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error with value ' . $value );
    }

    /**
     * Testing Assert::isYYYYDate exception
     *
     * @test
     */
    public function isYYYYDateTest() : void
    {
        $ok    = false;
        $value = 'a';
        try {
            Assert::isYYYYDate( 'test', $value );
        }
        catch ( InvalidArgumentException ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error #1 with value ' . $value );

        $ok    = false;
        $value = '1789';
        try {
            Assert::isYYYYDate( 'test', $value );
        }
        catch ( InvalidArgumentException ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error #2 with value ' . $value );
    }

    /**
     * Testing Assert::isYYYYMMDate exception
     *
     * @test
     */
    public function isYYYYMMDateTest() : void
    {
        $ok    = false;
        $value = 'a';
        try {
            Assert::isYYYYMMDate( 'test', $value );
        }
        catch ( InvalidArgumentException ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error #1 with value ' . $value );

        $ok    = false;
        $value = 20230;
        try {
            Assert::isYYYYMMDate( 'test', $value );
        }
        catch ( InvalidArgumentException ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error #2 with value ' . $value );

        $ok    = false;
        $value = '202300';
        try {
            Assert::isYYYYMMDate( 'test', $value );
        }
        catch ( InvalidArgumentException ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error #3 with value ' . $value );
    }
}
