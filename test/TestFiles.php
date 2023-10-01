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

use DirectoryIterator;
use Exception;
use InvalidArgumentException;
use Kigkonsult\DsigSdk\Dto\Signature;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Kigkonsult\Sie5Sdk\XMLParse\Sie5Parser;
use Kigkonsult\Sie5Sdk\XMLWrite\Sie5Writer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

include 'PHPDiff/PHPDiff.php';

/**
 * Class TestFiles
 */
class TestFiles extends TestCase
{
    private static string $FMT0 = '%s START (#%s) %s on \'%s\'%s';

    /**
     * Return array of Sie4I file names
     *
     * testSie4IFile dataProvider
     *
     * @return array
     */
    public function sie4IFileTestProvider() : array
    {

        $testPath = __DIR__ . '/Sie4I_files';
        $dir      = new DirectoryIterator( $testPath );
        $dataArr  = [];

        $case     = 100;
        foreach( $dir as $file ) {
            if( ! $file->isFile() ) {
                continue;
            }
            $dataArr[] =
                [
                    $case,
                    $file->getPathname(),
                ];
            $case += 100;
        }

        return $dataArr;
    }

    /**
     * Reading Sie4I file, parse, write and compare
     *
     * Expects error due to attributes with default value
     *
     * @test
     * @dataProvider sie4IFileTestProvider
     *
     * @param int $case
     * @param string $fileName
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws Exception
     */
    public function sie4IFileTest1( int $case, string $fileName ) : void
    {
        $sie4Istring1Utf8 = file_get_contents( $fileName );
        $isKsummaSet1     = str_contains( $sie4Istring1Utf8, Sie4Parser::KSUMMA );
        $sie4Istring1     = StringUtil::utf8toCP437((string) $sie4Istring1Utf8 );
        // convert file content to CP437, save into tempFile
        $tempFile1 = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_21_');
        file_put_contents(
            $tempFile1,
            $sie4Istring1
        );

        // parse Sie4 file
        $sie4IDto     = Sie4Parser::factory()->process( $tempFile1 );
        unlink( $tempFile1 );

        // test #KSUMMA the remove it, will NOT match
        if( $isKsummaSet1 ) {
            $this->assertTrue( $sie4IDto->isKsummaSet());
        }

        $sie4Istring3 = (new Sie4IWriter())->process( $sie4IDto, null, false );

        // may result in diff due to string quotes and row ordering etc
        $this->assertEquals(
            rtrim( $sie4Istring1 ),
            rtrim( $sie4Istring3 ),
            __FUNCTION__ . ' ' . $case . '-23 ' . $fileName . ' file parse and write results in diff'
        );
    }

    /**
     * Reading Sie4I file, parse, write SieEntry xml and convert back (twice) and compare
     *
     * Expects error due to attributes with default value
     *
     * @test
     * @dataProvider sie4IFileTestProvider
     *
     * @param int $case
     * @param string $fileName
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws Exception
     */
    public function sie4IFileTest2( int $case, string $fileName ) : void
    {
        static $FMT1 = '%s (#%s) not valid%s%s%s';

        echo sprintf( self::$FMT0, PHP_EOL, __FUNCTION__, $case, basename( $fileName ), PHP_EOL );

        $sie4Istring1Utf8 = file_get_contents( $fileName );
        $sie4Istring1     = StringUtil::utf8toCP437((string) $sie4Istring1Utf8 );
        // convert file content to CP437, save into tempFile
        $tempFile1 = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_21_');
        file_put_contents(
            $tempFile1,
            $sie4Istring1
        );
        // echo 'sie4Istring1' . PHP_EOL . StringUtil::cp437toUtf8( $sie4Istring1 ) . PHP_EOL;

        // parse Sie4 file
        $sie4IDto     = Sie4Parser::factory()->process( $tempFile1 );
        $isKsummaSet1 = $sie4IDto->isKsummaSet();

        unlink( $tempFile1 );
        // echo 'sie4Dto' . PHP_EOL . var_export( $sie4Dto, true ) . PHP_EOL; // test ###

        /* will result in diff due to string quotes and row ordering
        $this->assertEquals(
            $sie4Istring1,
            Sie4::sie4IDto2String( $sie4IDto ),
            __FUNCTION__ . ' ' . ( $case + 1 ) . ' file parse and write results in diff'
        );
        */
        echo 'sie4IDto has ' .
            $sie4IDto->countVerDtos()      . ' VerDtos with ' .
            $sie4IDto->countVerTransDtos() . ' transDtos'   . PHP_EOL; // test ###

        // parse Sie4IDto into SieEntry
        $sieEntry1 = Sie5EntryLoader::factory( $sie4IDto )->getSieEntry();
        $expected = [];
        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry1->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 1, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );

        // write SieEntry1 to XML
        $sieEntry1String = Sie5Writer::factory()->write( $sieEntry1 );
        // parse xml back into SieEntry2
        $sieEntry2 = Sie5Parser::factory()->parseXmlFromString( $sieEntry1String );

        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry2->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 2, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );

        // write Sie4 from SieEntry2 to string

        $sie4IDtoTmp  = Sie4ILoader::factory(  $sieEntry2 )->getSie4IDto();

        if( $isKsummaSet1 ) {
            $sie4IDtoTmp->setKsumma( 1 );
        }
        $sie4Istring2 = Sie4IWriter::factory()->process( $sie4IDtoTmp );

        // echo 'sie4Istring2' . PHP_EOL . StringUtil::cp437toUtf8( $sie4Istring2 ) . PHP_EOL;

        // parse Sie4 string (!!) back into SieEntry, step by step
        $sie4IDto2       = Sie4Parser::factory()->process( $sie4Istring2 );
        $isKsummaSet2    = $sie4IDto2->isKsummaSet();
        $sieEntry3       = Sie5EntryLoader::factory( $sie4IDto2 )->getSieEntry();
        $sieEntry3String = Sie5Writer::factory()->write( $sieEntry3 );

        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry3->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 3, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );
        $this->assertSame(
            $isKsummaSet1, $isKsummaSet2, 'KSUMMA diff' .
            ', isKsummaSet1 : ' . var_export( $isKsummaSet1, true ) .
            ', isKsummaSet2 : ' . var_export( $isKsummaSet2, true )
        );
        // $sieEntry1 and $sieEntry3 has the same content
        $this->assertEquals(
            $sieEntry1String,
            $sieEntry3String,
            'sieEntry1 and sieEntry3 has NOT the same load'
        );

        // echo 'passed \'var_export( $sieEntryX, true )\', OK'; // test ###

        // convert SieEntry (again) to Sie4 string and file
        $tempFile3 = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_22_');
        $sie4IDto     = Sie4ILoader::factory( $sieEntry3 )->getSie4IDto();
        if( $isKsummaSet2 ) {
            $sie4Iwriter = Sie4IWriter::factory( $sie4IDto->setKsumma( 1 ));
            $dummy       = $sie4Iwriter->process();
            $kSummaBase  = $sie4Iwriter->getKsummaBase();
            echo 'sie4Istring3 (ksumma base in utf8) :' . PHP_EOL .
                StringUtil::cp437toUtf8(
                    chunk_split( $kSummaBase, 76, PHP_EOL )
                )
                . PHP_EOL;
        }

        $sie4Istring3 = Sie4IWriter::factory()->process( $sie4IDto );
        Sie4IWriter::factory()->process( $sie4IDto, $tempFile3 );

        $this->assertStringEqualsFile(
            $tempFile3,
            $sie4Istring3,
            'tempFile3 and sie4Istring3 has NOT the same load'
        );
        unlink( $tempFile3 );

        // convert to utf8 for opt display
        $sie4Istring2Utf8 = StringUtil::cp437toUtf8( $sie4Istring2 );
        $sie4Istring3Utf8 = StringUtil::cp437toUtf8( $sie4Istring3 );

        /*
        // view output files
        echo PHP_EOL . 'sie4Istring1 :' . PHP_EOL . $sie4Istring1 . PHP_EOL;
        echo PHP_EOL . 'sie4Istring2 :' . PHP_EOL . $sie4Istring2 . PHP_EOL;
        */
        // echo PHP_EOL . 'sie4Istring3 (i utf8) :' . PHP_EOL . $sie4Istring3Utf8 . PHP_EOL;

        /*
        error_log( 'sie4Istring1Utf8 ' . PHP_EOL . var_export( $sie4Istring1Utf8, true ));
        error_log( 'sie4Istring3Utf8 ' . PHP_EOL . var_export( $sie4Istring3Utf8, true ));
        */
        // file strings diff but in PHP from http://www.holomind.de/phpnet/diff.php
        $diff = PHPDiff( $sie4Istring1Utf8, $sie4Istring3Utf8 );
        $this->assertEmpty(
            $diff,
            'diff 1/3 (i utf8) : ' . PHP_EOL . $diff
        );
    }


    /**
     * Return array of Sie4E file names
     *
     * testSie4EFile dataProvider
     *
     * @return array
     */
    public function sie4EFileTestProvider() : array
    {

        $testPath = __DIR__ . '/Sie4E_files';
        $dir      = new DirectoryIterator( $testPath );
        $dataArr  = [];

        $case     = 200;
        foreach( $dir as $file ) {
            if( ! $file->isFile() ) {
                continue;
            }
            $dataArr[] =
                [
                    $case,
                    $file->getPathname(),
                ];
            $case += 100;
        }

        return $dataArr;
    }

    /**
     * Reading Sie4E file, parse, write Sie xml and convert back (twice) and compare
     *
     * Sie4E format supersedes Sie4I format
     * Expects error due to attributes with default value
     *
     * @test
     * @dataProvider sie4EFileTestProvider
     * @param int $case
     * @param string $fileName
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws Exception
     */
    public function sie4EFileTest( int $case, string $fileName ) : void
    {
        static $FMT1 = '%s (#%s) not valid%s%s%s';

        echo sprintf( self::$FMT0, PHP_EOL, __FUNCTION__, $case, basename( $fileName ), PHP_EOL );

        $sie4Estring1Utf8 = file_get_contents( $fileName );
        $sie4Estring1     = StringUtil::utf8toCP437( $sie4Estring1Utf8 );
        // convert file content to CP437, save into tempFile
        $tempFile1 = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_1_');
        file_put_contents(
            $tempFile1,
            $sie4Estring1
        );
        echo 'sie4Estring1' . PHP_EOL . StringUtil::cp437toUtf8( $sie4Estring1 ) . PHP_EOL; // test ###

        // parse Sie4 file
        $sie4EDto     = Sie4Parser::factory()->process( $tempFile1 );
        $isKsummaSet1 = $sie4EDto->isKsummaSet();

        unlink( $tempFile1 );

        /* will result in diff due to string quotes and row ordering
        $this->assertEquals(
            $sie4Estring1,
            Sie4::sie4EDto2String( $sie4EDto ),
            __FUNCTION__ . ' ' . ( $case + 1 ) . ' file parse and write results in diff'
        );
        */

        // echo 'sie4Dto' . PHP_EOL . var_export( $sie4Dto, true ) . PHP_EOL; // test ###
        echo 'sie4EDto has ' . PHP_EOL .
            $sie4EDto->countIbDtos()       . ' ibDtos'      . PHP_EOL .
            $sie4EDto->countUbDtos()       . ' ibDtos'      . PHP_EOL .
            $sie4EDto->countOibDtos()      . ' oibDtos'     . PHP_EOL .
            $sie4EDto->countOubDtos()      . ' oubDtos'     . PHP_EOL .
            $sie4EDto->countSaldoDtos()    . ' saldoDtos'   . PHP_EOL .
            $sie4EDto->countPsaldoDtos()   . ' pSaldoDtos'  . PHP_EOL .
            $sie4EDto->countPbudgetDtos()  . ' pBudgetDtos' . PHP_EOL .
            $sie4EDto->countVerDtos()      . ' VerDtos with ' .
            $sie4EDto->countVerTransDtos() . ' transDtos'   . PHP_EOL; // test ###

        // parse Sie4EDto into Sie
        $sie1 = Sie5Loader::factory( $sie4EDto )->getSie();
        $sie1->setSignature( new Signature()); // empty...

        echo 'sie1 XMl : ' . PHP_EOL . Sie5Writer::factory()->write( $sie1 ) . PHP_EOL; // test ###

        /*
         * will not validate ok due to empty journal id
        $expected = [];
        $this->assertTrue(      // ---- validate Sie BUT Signature (req) is empty, above
            $sie1->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 1, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );
        */

        // write Sie1 to XML
        $sie1String = Sie5Writer::factory()->write( $sie1 );
        // parse xml back into Sie2
        $sie2 = Sie5Parser::factory()->parseXmlFromString( $sie1String );
        $sie2->setSignature( new Signature()); // empty...

        /*
         * will not validate ok due to empty journal id
        $this->assertTrue(      // ---- validate Sie BUT Signature (req) is empty, above
            $sie2->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 2, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );
        */

        // write Sie4 from Sie2 to string
        $sie4EDtoTmp  = Sie4ELoader::factory( $sie2 )->getSie4EDto();

        if( $isKsummaSet1 ) {
            $sie4EDtoTmp->setKsumma( 1 );
        }
//      $sie4Estring2 = Sie4::sie4EDto2String( $sie4EDtoTmp ); // skip due validating break, IB/UB/RES missing
        $sie4Estring2 = Sie4EWriter::factory()->process( $sie4EDtoTmp );

        // echo 'sie4Istring2' . PHP_EOL . StringUtil::cp437toUtf8( $sie4Istring2 ) . PHP_EOL;

        // parse Sie4 string (!!) back into Sie, step by step
        $sie4EDto2       = Sie4Parser::factory()->process( $sie4Estring2 );
        $isKsummaSet2    = $sie4EDto2->isKsummaSet();
        // $sie3            = Sie4::sie4EDto2Sie( $sie4EDto2 ); no validation as above
        $sie3            = Sie5Loader::factory( $sie4EDto2 )->getSie();
        $sie3->setSignature( new Signature()); // empty...
        $sie3String      = Sie5Writer::factory()->write( $sie3 );

        /*
         * will not validate ok due to empty journal id
        $this->assertTrue(      // ---- validate Sie BUT Signature (req) is empty, above
            $sie3->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 3, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );
        */
        $this->assertSame(
            $isKsummaSet1, $isKsummaSet2, 'KSUMMA diff' .
            ', isKsummaSet1 : ' . var_export( $isKsummaSet1, true ) .
            ', isKsummaSet2 : ' . var_export( $isKsummaSet2, true )
        );
        // $sie1 and $sie3 has the same content
        $this->assertEquals(
            $sie1String,
            $sie3String,
            'sieEntry1 and sieEntry3 has NOT the same load'
        );

        // echo 'passed \'var_export( $sieEntryX, true )\', OK'; // test ###

        // convert Sie (again) to Sie4 string and file
        $tempFile3       = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_2_');
        $sie4EDto        = Sie4ELoader::factory( $sie3 )->getSie4EDto();
        if( $isKsummaSet2 ) {
            $sie4Iwriter = Sie4IWriter::factory( $sie4EDto->setKsumma( 1 ));
            $dummy       = $sie4Iwriter->process();
            $kSummaBase  = $sie4Iwriter->getKsummaBase();
            echo 'sie4Istring3 (ksumma base in utf8) :' . PHP_EOL .
                StringUtil::cp437toUtf8(
                    chunk_split( $kSummaBase, 76, PHP_EOL )
                )
                . PHP_EOL;
        }

        // $sie4Estring3 = Sie4::sie4EDto2String( $sie4EDto ); no validation as above
        $sie4Estring3 = Sie4EWriter::factory()->process( $sie4EDto );

        // Sie4::sie4EDto2File( $sie4EDto, $tempFile3 ); no validation as above
        Sie4EWriter::factory()->process( $sie4EDto, $tempFile3 );

        $this->assertStringEqualsFile(
            $tempFile3,
            $sie4Estring3,
            'tempFile3 and sie4Istring3 has NOT the same load'
        );
        unlink( $tempFile3 );

        // convert to utf8 for opt display
        $sie4Estring2Utf8 = StringUtil::cp437toUtf8( $sie4Estring2 );
        $sie4Estring3Utf8 = StringUtil::cp437toUtf8( $sie4Estring3 );

        /*
        // view output files
        echo PHP_EOL . 'sie4Istring1 :' . PHP_EOL . $sie4Istring1 . PHP_EOL;
        echo PHP_EOL . 'sie4Istring2 :' . PHP_EOL . $sie4Istring2 . PHP_EOL;
        */
        // echo PHP_EOL . 'sie4Istring3 (i utf8) :' . PHP_EOL . $sie4Istring3Utf8 . PHP_EOL;

        // file strings diff but in PHP from http://www.holomind.de/phpnet/diff.php
        $diff = PHPDiff( $sie4Estring1Utf8, $sie4Estring3Utf8 );
        $this->assertEmpty(
            $diff,
            'diff 1/3 (i utf8) : ' . PHP_EOL . $diff
        );
    }

    /**
     * testSie5IFile dataProvider
     * @return array
     */
    public function sie5FileTestProvider() : array
    {

        $testPath = __DIR__ . '/Sie5_files';
        $dir      = new DirectoryIterator( $testPath );
        $dataArr  = [];

        $case     = 100;
        foreach( $dir as $file ) {
            if( ! $file->isFile() ) {
                continue;
            }
            $dataArr[] =
                [
                    $case,
                    $file->getPathname(),
                ];
            $case += 100;
        }

        return $dataArr;
    }

    /**
     * Reading SieEntry file from Sie5_files, parse and write Sie4, convert back and compare
     *
     * NO ksumma test here
     *
     * @test
     * @dataProvider sie5FileTestProvider
     * @param int $case
     * @param string $fileName
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws Exception
     */
    public function sie5FileTest( int $case, string $fileName ) : void
    {
        echo sprintf( self::$FMT0, PHP_EOL, __FUNCTION__, $case, basename( $fileName ), PHP_EOL );

        // convert Sie5 (SieEntry) XML file to Sie4 string
        $sie4IString1 = Sie4IWriter::factory()->process(
            Sie4ILoader::factory(
                Sie5Parser::factory()->parseXmlFromFile( $fileName )
            )->getSie4IDto()
        );
        $sie4IString2 = Sie4IWriter::factory()->process(
            Sie4ILoader::factory(
                Sie5Parser::factory()->parseXmlFromString( file_get_contents( $fileName ))
            )->getSie4IDto()
        );

        $this->assertEquals(
            $sie4IString1,
            $sie4IString2,
            'Error comparing Sie4Is'
        );

        // echo 'sie4Istring1' . PHP_EOL . StringUtil::cp437toUtf8( $sie4IString1 ) . PHP_EOL;

        // convert Sie4 string to Sie5 (SieEntry) XML string

        $sie5XMLstring2 = Sie5Writer::factory()->write(
            Sie5EntryLoader::factory(
                Sie4Parser::factory()->process( $sie4IString1 )
            )->getSieEntry()
        );

        // compare SieEntry xml's, will turn up in some inconsistency
        $this->assertXmlStringEqualsXmlFile(
            $fileName,
            $sie5XMLstring2,
            'Error comparing XMLs'
        );
    }
}
