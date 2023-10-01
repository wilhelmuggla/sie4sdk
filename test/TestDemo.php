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
use Kigkonsult\Sie4Sdk\Api\Array2Sie4Dto;
use Kigkonsult\Sie4Sdk\Api\Json2Sie4Dto;
use Kigkonsult\Sie4Sdk\Api\Sie4Dto2Array;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use PHPUnit\Framework\TestCase;

class TestDemo extends TestCase implements Sie4Interface
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

        $sie4Array = [
            self::FTGNAMN            => 'Acme Corp',
            self::ORGNRORGNR         => '556334-3689',
            self::VERDATUM           => [ date( 'Ymd' ) ],
            self::VERNR              => [ 123 ],
            self::VERTEXT            => [ 'Porto' ],
            self::TRANSKONTONR       => [ [ 1910, 2640, 6250 ] ],
            self::TRANSBELOPP        => [ [ -2000.00, 400.00, 1600.00 ] ]
        ];
        $dataArr[] = // minimal array business transaction input, single #VER with three $TRANS
            [
                ++$case,
                $sie4Array
            ];

        $dataArr[] = // minimal url-encoded query string, business transaction input
            [
                ++$case,
                http_build_query( $sie4Array )
            ];

        $dataArr[] = // minimal json business transaction input
            [
                ++$case,
                '{"ORGNRORGNR":"556334-3689","FNAMN":"Acme Corp","VERNR":[123],"VERDATUM":["20230920"],"VERTEXT":["Porto"],"TRANSKONTONR":[["1910","2640","6250"]],"TRANSBELOPP":[["-2000.00","400.00","1600.00"]]}'
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
     * @param string|string[]|Sie4Dto $input
     * @return void
     * @throws Exception
     */
    public function genTest1( int $case, string|array|Sie4Dto $input ) : void
    {
        static $ERR1 = '#%d-%d Sie4IDto assert error, %s%s';
        static $ERR2 = '#%d-%d Sie4IDto string compare error';
        static $ERR3 = '#%d-%d Sie4IDto array compare error';
        switch( true ) {
            case is_string( $input ) :
                if( str_starts_with( $input, '{"' )) { // json
                    $sie4Dto    = Json2Sie4Dto::process( $input );
                    $inputArray = Sie4Dto2Array::process( $sie4Dto );
                    break;
                }
                // URL-encoded string to array
                parse_str( $input, $array );
                $input = $array;
                // fall through
            case is_array( $input ) :
                $sie4Dto = Array2Sie4Dto::process( $input );
                $inputArray = $input;
                break;
            default :
                $sie4Dto = $input;
                $inputArray = Sie4Dto2Array::process( $sie4Dto );
        }
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
            StringUtil::cp437toUtf8( $sie4String2 ),
            sprintf( $ERR2, $case, 12 )
        );

        // parse string into array, compare with input
        $output = Sie4Dto2Array::process( Sie4Parser::factory( $sie4String2 )->process());
        unset( // equal inputArray and output, skip non-mandatory parts
            $inputArray[self::FLAGGPOST],
            $inputArray[self::PROGRAMNAMN],
            $inputArray[self::PROGRAMVERSION],
            $inputArray[self::GENDATUM],
            $inputArray[self::GENSIGN],
            $inputArray[self::ORGNRFORNVR],
            $inputArray[self::TIMESTAMP],
            $inputArray[self::GUID],
            $inputArray[self::VERTIMESTAMP],
            $inputArray[self::VERGUID],
            $inputArray[self::VERPARENTGUID],
            $inputArray[self::TRANSTIMESTAMP],
            $inputArray[self::TRANSGUID],
            $inputArray[self::TRANSPARENTGUID],
            $inputArray[self::REGDATUM],
            $inputArray[self::TRANSDAT],
            $output[self::FLAGGPOST],
            $output[self::PROGRAMNAMN],
            $output[self::PROGRAMVERSION],
            $output[self::GENDATUM],
            $output[self::GENSIGN],
            $output[self::ORGNRFORNVR],
            $output[self::TIMESTAMP],
            $output[self::GUID],
            $output[self::VERTIMESTAMP],
            $output[self::VERGUID],
            $output[self::VERPARENTGUID],
            $output[self::TRANSTIMESTAMP],
            $output[self::TRANSGUID],
            $output[self::TRANSPARENTGUID],
            $output[self::REGDATUM],
            $output[self::TRANSDAT],
        );
        foreach( $inputArray[self::TRANSBELOPP] as $bx => $belopp ) { // json may skip decimals
            foreach( $belopp as $tx => $transBelopp ) {
                $inputArray[self::TRANSBELOPP][$bx][$tx] = number_format((float) $transBelopp, 2, '.', '' );
            }
        }
        $this->assertEquals(
            $inputArray,
            $output,
            sprintf( $ERR2, $case, 14 )
        );
    }
}
