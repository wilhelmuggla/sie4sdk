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

use InvalidArgumentException;
use Kigkonsult\Asit\It;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Util\ArrayUtil;
use Kigkonsult\Sie4Sdk\Util\FileUtil;
use Kigkonsult\Sie4Sdk\Util\StringUtil;

use function implode;
use function sprintf;

class Sie4IWriter extends Sie4WriterBase
{
    /**
     * @param null|Sie4Dto  $sie4IDto
     * @param null|string   $outputfile
     * @param null|bool     $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     * @deprecated
     */
    public function write4I(
        $sie4IDto = null,
        $outputfile = null,
        $writeKsumma = false
    ) : string
    {
        if( ! empty( $sie4IDto ) && $writeKsumma ) {
            $sie4IDto = clone $sie4IDto;
            $sie4IDto->setKsumma( 1 ); // force recount
        }
        return $this->process( $sie4IDto, $outputfile );
    }

    /**
     * @param null|Sie4Dto $sie4IDto
     * @param null|string  $outputfile
     * @param null|bool    $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     */
    public function process(
        $sie4IDto = null,
        $outputfile = null,
        $writeKsumma = null
    ) : string
    {
        if( ! empty( $sie4IDto )) {
            $this->setSie4Dto( $sie4IDto );
        }
        Sie4Validator::assertSie4IDto( $this->sie4Dto );
        if( ! empty( $outputfile )) {
            FileUtil::assertWriteFile( $outputfile, 5201 );
        }
        $this->writeKsumma = ( $this->sie4Dto->isKsummaSet() || ( $writeKsumma ?? false ));
        $this->output      = new It();

        $this->output->append(
            sprintf( self::$SIEENTRYFMT1, self::FLAGGA, $this->sie4Dto->getFlagga())
        );
        if( $this->sie4Dto->isKsummaSet() || ( $writeKsumma ?? false )) {
            $this->output->append( self::KSUMMA );
        }
        $this->writeProgram();
        $this->writeFormat();
        $this->writeGen();
        $this->writeSietyp();
        $this->writeProsa();

        $this->writeFtyp();
        $this->writeFnr();
        $this->writeOrgnr();
        $this->writeAdress();
        $this->writeFnamn();

        $this->writeRar();
        $this->writeTaxar();
        $this->writeKptyp();
        $this->writeValuta();

        $this->writeKonto();
        $this->writeSRU();
        $this->writeDim();
        $this->writeObjekt();

        $this->writeIBUB();
        $this->writeOibOub();
        $this->writeRes();
        $this->writePsaldoPbudget();
        $this->writeVerDtos();

        if( $this->writeKsumma ) {
            $this->computeAndWriteKsumma();
        }
        $output = ArrayUtil::eolEndElements( $this->output->get());
        if( ! empty( $outputfile )) {
            FileUtil::writeFile( $outputfile, $output, 5205 );
        }
        return implode( StringUtil::$SP0, $output );
    }
}
