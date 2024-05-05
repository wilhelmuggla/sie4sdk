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

use Kigkonsult\Sie4Sdk\Lists\DimObjektDtoList;

class DimObjektDtoListLoader implements ListLoaderInterface
{
    /**
     * Return loaded DimObjectDtoList with dimensionNr + objektNr as primary key
     *
     * @override
     * @param string|MediaLoaderInterface $mediaLoader     fqcn or instance
     * @param null|mixed[]                $mediaLoaderArgs (fqcn) class construct args
     * @return DimObjektDtoList
     */
    public static function load(
        string|MediaLoaderInterface $mediaLoader,
        ? array                     $mediaLoaderArgs = []
    ) : DimObjektDtoList
    {
        if( is_string( $mediaLoader )) {
            $mediaLoader = new $mediaLoader( $mediaLoaderArgs );
        }
        $dimObjektDtoList = DimObjektDtoList::factory();
        foreach( $mediaLoader->getOutput() as $DimObjektDto ) {
            $dimObjektDtoList->append(
                $DimObjektDto,                    // list element
                DimObjektDtoList::getPrimaryKey(  // primary key
                    $DimObjektDto->getDimensionNr(),
                    $DimObjektDto->getObjektNr()
                )
            );
        } // end foreach
        return $dimObjektDtoList;
    }
}
