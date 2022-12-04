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
namespace Kigkonsult\Sie4Sdk;

use Exception;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use PHPUnit\Framework\TestCase;

class TestDemo extends TestCase
{
    /**
     * demoTest dataProvider
     *
     * @return array
     */
    public function demoTestProvider() : array
    {
        $dataArr   = [];
        $case      = 1000;

        $dataArr[] =
            [
                ++$case,
                Sie4Dto::factory( 'Acme Corp', '123', '556334-3689' )
                    ->addVerDto(
                        VerDto::factory( 123, 'Porto' )
                            ->addTransKontoNrBelopp( 1910, -2000.00 )
                            ->addTransKontoNrBelopp( 2640, 400.00 )
                            ->addTransKontoNrBelopp( 6250, 1600.00 )
                    )
            ];

        return $dataArr;
    }

    /**
     * Simple double create-string/parse-string test
     *
     * @test
     * @dataProvider demoTestProvider
     *
     * @param int $case
     * @param Sie4Dto $sie4Dto
     * @return void
     * @throws Exception
     */
    public function genTest1( int $case, Sie4Dto $sie4Dto ) : void
    {
        static $ERR1 = '#%d-%d Sie4IDto assert error, %s%s';
        static $ERR2 = '#%d-%d Sie4IDto string compare error';
        // assert as Sie4I
        $outcome = true;
        try {
            Sie4Validator::assertSie4IDto( $sie4Dto );
        }
        catch( Exception $e ) {
            $outcome = $e->getMessage();
        }
        $this->assertTrue(
            $outcome,
            sprintf(
                $ERR1,
                $case,
                11,
                PHP_EOL,
                StringUtil::cp437toUtf8(
                    Sie4IWriter::factory()->process( $sie4Dto )
                )
            )
        );
        // create sie4String
        $sie4String1 = Sie4IWriter::factory()->process( $sie4Dto );
        // parse the Sie4I string back and create new Sie4I string, compare
        $sie4String2 = Sie4IWriter::factory()->process( Sie4Parser::factory()->process( $sie4String1 ));

        // compare
        $this->assertEquals(
            StringUtil::cp437toUtf8( $sie4String1 ),
            StringUtil::cp437toUtf8($sie4String2 ),
            sprintf( $ERR2, $case, 12 )
        );
    }
}
