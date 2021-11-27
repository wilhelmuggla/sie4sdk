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

use function implode;
use function sprintf;

class Sie4IWriter extends Sie4WriterBase
{
    /**
     * @param Sie4Dto|null $sie4IDto
     * @param string|null $outputfile
     * @param bool|null $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     * @deprecated
     */
    public function write4I(
        ? Sie4Dto $sie4IDto = null,
        ? string  $outputfile = null,
        ? bool $writeKsumma = false
    ) : string
    {
        if( $sie4IDto !== null && $writeKsumma ) {
            $sie4IDto = clone $sie4IDto;
            $sie4IDto->setKsumma( 1 ); // force recount
        }
        return $this->process( $sie4IDto, $outputfile );
    }

    /**
     * Return Sie4I string (without input validation)
     *
     * @param Sie4Dto|null $sie4Dto
     * @param string|null $outputfile
     * @param bool|null $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     */
    public function process(
        ? Sie4Dto $sie4Dto = null,
        ? string  $outputfile = null,
        ? bool $writeKsumma = null
    ) : string
    {
        if( $sie4Dto !== null ) {
            $this->setSie4Dto( $sie4Dto );
        }
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
        $this->writeUnderDim();
        $this->writeObjekt();

        $this->writeIbUb();
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
        return implode( $output );
    }
}
