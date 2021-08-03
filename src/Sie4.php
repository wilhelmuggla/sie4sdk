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

use Exception;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Api\Array2Sie4Dto;
use Kigkonsult\Sie4Sdk\Api\Json2Sie4Dto;
use Kigkonsult\Sie4Sdk\Api\Sie4Dto2Array;
use Kigkonsult\Sie4Sdk\Api\Sie4Dto2Json;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie5Sdk\Dto\Sie;
use Kigkonsult\Sie5Sdk\Dto\SieEntry;
use Kigkonsult\Sie5Sdk\XMLParse\Sie5Parser;
use Kigkonsult\Sie5Sdk\XMLWrite\Sie5Writer;
use RuntimeException;

/**
 * Class Sie4
 *
 * Parse Sie4 comments :
 *
 *   Note för #PROGRAM
 *     if missing, auto set
 *
 *   Note för #GEN
 *     if missing, 'datum' is set to 'now'
 *     if 'sign' is missing, '#PROGRAM programnamn' is used
 *
 *   #UNDERDIM are skipped
 *
 *   Note för #VER
 *     if 'verdatum' is missing, 'now' is used
 *     if 'sign' is missing, '#GEN sign' is used (for SieEntry)
 *
 *   Note för #TRANS/#RTRANS/#BTRANS
 *     only support for 'dimensionsnummer och objektnummer' in the 'objektlista'
 *     i.e. no support for 'hierarkiska dimensioner'
 *
 * Write Sie4 comments
 *   Sie4 file creation date has format 'YYYYmmdd', SieEntry 'YYYY-MM-DDThh:mm:ssZ'
 *
 *   The #KSUMMA checksum is experimental
 *
 * Load Sie 5 (export) comments :
 *
 *   The Sie instance is not complete, Signature (req) is missing
 */
class Sie4 implements Sie4Interface
{
    /**
     * Process input to Sie4IDto
     */

    /**
     * Parse Sie4I/Sie4E file/string into Sie4Dto instance
     *
     * @param string $source
     * @param null|bool $isSie4E  default false
     * @return Sie4Dto
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sie4IFileString2Sie4Dto( string $source, $isSie4E = false ) : Sie4Dto
    {
        $sie4Dto = Sie4Parser::factory()->process( $source );
        if( true === $isSie4E ) {
            Sie4Validator::assertSie4EDto( $sie4Dto );
        }
        else {
            Sie4Validator::assertSie4IDto( $sie4Dto );
        }
        return $sie4Dto;
    }

    /**
     * Transform (HTTP, $_REQUEST) input array to any Sie4E/4I Sie4Dto instance
     *
     * @param array $input
     * @return Sie4Dto
     */
    public static function array2Sie4Dto( array $input ) : Sie4Dto
    {
        return Array2Sie4Dto::process( $input );
    }

    /**
     * Transform input json string to any Sie4E/4I Sie4IDto instance
     *
     * @param string $json
     * @return Sie4Dto
     * @throws InvalidArgumentException
     */
    public static function json2Sie4Dto( string $json ) : Sie4Dto
    {
        return Json2Sie4Dto::process( $json );
    }

    /**
     * Convert Sie (Sie5 instance) into (Sie4E) Sie4Dto instance
     *
     * @param Sie $sie
     * @return Sie4Dto
     * @throws InvalidArgumentException
     */
    public static function sie2Sie4EDto( Sie $sie ) : Sie4Dto
    {
        $sie4EDto = Sie4ELoader::factory( $sie )->getSie4EDto();
        Sie4Validator::assertSie4EDto( $sie4EDto );
        return $sie4EDto;
    }

    /**
     * Convert SieEntry (Sie5 instance) into (Sie4I) Sie4Dto instance
     *
     * @param SieEntry $sieEntry
     * @return Sie4Dto
     * @throws InvalidArgumentException
     */
    public static function sieEntry2Sie4IDto( SieEntry $sieEntry ) : Sie4Dto
    {
        $sie4IDto = Sie4ILoader::factory( $sieEntry )->getSie4IDto();
        Sie4Validator::assertSie4IDto( $sie4IDto );
        return $sie4IDto;
    }

    /**
     * Transform Sie (5) XML into (Sie4I) Sie4Dto instance
     *
     * @param string $sieXML
     * @return Sie4Dto
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sieXML2Sie4IDto( string $sieXML ) : Sie4Dto
    {
        $sie = Sie5Parser::factory()->parseXmlFromString( $sieXML );
        return self::sie2Sie4EDto( $sie );
    }

    /**
     * Transform SieEntry (5) XML into (Sie4I) Sie4Dto instance
     *
     * @param string $sieEntryXML
     * @return Sie4Dto
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sieEntryXML2Sie4IDto( string $sieEntryXML ) : Sie4Dto
    {
        $sieEntry = Sie5Parser::factory()->parseXmlFromString( $sieEntryXML );
        return self::sieEntry2Sie4IDto( $sieEntry );
    }

    /**
     * Transform Sie (5) XML file into (Sie4E) Sie4Dto instance
     *
     * @param string $sieFile
     * @return Sie4Dto
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sieFile2Sie4IDto( string $sieFile ) : Sie4Dto
    {
        $sie = Sie5Parser::factory()->parseXmlFromFile( $sieFile );
        $sie4EDto = Sie4ELoader::factory( $sie )->getSie4EDto();
        Sie4Validator::assertSie4EDto( $sie4EDto );
        return $sie4EDto;
    }

    /**
     * Transform SieEntry (5) XML file into (Sie4I) Sie4Dto instance
     *
     * @param string $sieEntryFile
     * @return Sie4Dto
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sieEntryfile2Sie4IDto( string $sieEntryFile ) : Sie4Dto
    {
        $sieEntry = Sie5Parser::factory()->parseXmlFromFile( $sieEntryFile );
        $sie4IDto = Sie4ILoader::factory( $sieEntry )->getSie4IDto();
        Sie4Validator::assertSie4IDto( $sie4IDto );
        return $sie4IDto;
    }

    /**
     * Process Sie4IDto to output
     */

    /**
     * Write Sie4IDto instance to Sie4 string
     *
     * @param Sie4Dto    $sie4IDto
     * @return string
     * @throws InvalidArgumentException
     */
    public static function sie4IDto2String( Sie4Dto $sie4IDto ) : string
    {
        Sie4Validator::assertSie4IDto( $sie4IDto );
        return Sie4IWriter::factory()->process( $sie4IDto );
    }

    /**
     * Write Sie4EDto instance to Sie4 string
     *
     * @param Sie4Dto $sie4EDto
     * @return string
     * @throws InvalidArgumentException
     */
    public static function sie4EDto2String( Sie4Dto $sie4EDto ) : string
    {
        Sie4Validator::assertSie4EDto( $sie4EDto );
        return Sie4EWriter::factory()->process( $sie4EDto );
    }

    /**
     * Write Sie4IDto instance to Sie4I file, opt with KSUMMA
     *
     * @param Sie4Dto     $sie4IDto
     * @param string      $outputfile
     * @throws InvalidArgumentException
     */
    public static function sie4IDto2File(
        Sie4Dto $sie4IDto,
        string $outputfile
    )
    {
        Sie4Validator::assertSie4IDto( $sie4IDto );
        Sie4IWriter::factory()->process( $sie4IDto, $outputfile );
    }

    /**
     * Write Sie4EDto instance to Sie4E file, opt with KSUMMA
     *
     * @param Sie4Dto $sie4EDto
     * @param string  $outputfile
     * @throws InvalidArgumentException
     */
    public static function sie4EDto2File(
        Sie4Dto $sie4EDto,
        string $outputfile
    )
    {
        Sie4Validator::assertSie4EDto( $sie4EDto );
        Sie4EWriter::factory()->process( $sie4EDto, $outputfile );
    }

    /**
     * Transform any Sie4E/4I Sie4Dto instance to array
     *
     * @param Sie4Dto $sie4Dto
     * @return array
     */
    public static function sie4Dto2Array( Sie4Dto $sie4Dto ) : array
    {
        return Sie4Dto2Array::process( $sie4Dto );
    }

    /**
     * Transform any Sie4E/4I Sie4Dto instance to json string
     *
     * @param Sie4Dto $sie4Dto
     * @return string
     * @throws InvalidArgumentException
     */
    public static function sie4Dto2Json( Sie4Dto $sie4Dto ) : string
    {
        return Sie4Dto2Json::process( $sie4Dto );
    }

    /**
     * Convert Sie4EDto instance to Sie (Sie 5) instance
     *
     * @param Sie4Dto $sie4EDto
     * @return Sie
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sie4EDto2Sie( Sie4Dto $sie4EDto ) : Sie
    {
        Sie4Validator::assertSie4EDto( $sie4EDto );
        return Sie5Loader::factory( $sie4EDto )->getSie();
    }


    /**
     * Convert Sie4IDto instance to SieEntry (Sie 5) instance
     *
     * @param Sie4Dto $sie4IDto
     * @return SieEntry
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sie4IDto2SieEntry( Sie4Dto $sie4IDto ) : SieEntry
    {
        Sie4Validator::assertSie4IDto( $sie4IDto );
        return Sie5EntryLoader::factory( $sie4IDto )->getSieEntry();
    }

    /**
     * Transform Sie4EDto instance to Sie (Sie 5) XML string
     *
     * @param Sie4Dto $sie4EDto
     * @return string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sie4IDto2SieXml( Sie4Dto $sie4EDto ) : string
    {
        Sie4Validator::assertSie4EDto( $sie4EDto );
        $sie = Sie5Loader::factory( $sie4EDto )->getSie();
        return Sie5Writer::factory()->write( $sie );
    }

    /**
     * Transform Sie4IDto instance to SieEntry (Sie 5) XML string
     *
     * @param Sie4Dto $sie4IDto
     * @return string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sie4IDto2SieEntryXml( Sie4Dto $sie4IDto ) : string
    {
        Sie4Validator::assertSie4IDto( $sie4IDto );
        $sieEntry = Sie5EntryLoader::factory( $sie4IDto )->getSieEntry();
        return Sie5Writer::factory()->write( $sieEntry );
    }
}
