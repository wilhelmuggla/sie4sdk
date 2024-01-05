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
namespace Kigkonsult\Sie4Sdk\Rsc\Loader;

use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Rsc\DimObjektList;
use RuntimeException;

/**
 * Class DimObjektListCsvFileLoader
 *
 * Parse a csv-file into DimObjektDto[]
 * Inherit csv config and logic from BaseCsvFileLoader
 * Each csv-row has fields dimensionNr, objektNr, namn
 */
class DimObjektListCsvFileLoader extends BaseCsvFileLoader
{
    /**
     * Return DimObjektDto[], expects csv file with rows : dimensionNr, objektNr, namn
     *
     * Index  dimensionNr + objektNr
     *
     * @return DimObjektDto[]
     * @throws RuntimeException
     */
    public function getOutput() : array
    {
        $input  = $this->getinput();
        $output = [];
        foreach( $input as $lx => $line ) {
            $row            = $this->getRowdata( $line, $lx, 3 );
            $index          = DimObjektList::getPrimaryKey((int) $row[2], $row[0] );
            $output[$index] = DimObjektDto::factoryDimObject((int) $row[2], $row[0], $row[1] );
        } // end foreach
        return $output;
    }
}
