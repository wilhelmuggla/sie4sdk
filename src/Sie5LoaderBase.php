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
namespace Kigkonsult\Sie4Sdk;

use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Dto\IdDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Util\DateTimeUtil;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Dto\AccountingCurrencyType;
use Kigkonsult\Sie5Sdk\Dto\FileCreationType;
use Kigkonsult\Sie5Sdk\Dto\FileInfoType;
use Kigkonsult\Sie5Sdk\Dto\FileInfoTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\FiscalYearType;
use Kigkonsult\Sie5Sdk\Dto\Sie;
use Kigkonsult\Sie5Sdk\Dto\SoftwareProductType;

use function str_contains;
use function str_replace;
use function trim;

abstract class Sie5LoaderBase  implements Sie4Interface
{
    /**
     * Process Sie4 idDto into Sie/SieEntry
     *
     * genSign logic also used in processVerDtos
     *
     * @param bool $toSieEntry
     * @param IdDto $idDto
     * @param FileInfoType|FileInfoTypeEntry $fileInfo
     * @return void
     */
    protected static function processIdDto(
        bool $toSieEntry,
        IdDto $idDto,
        FileInfoType|FileInfoTypeEntry $fileInfo
    ) : void
    {
        [ $name, $version ] = self::processNameVersion( $idDto );
        // required
        $fileInfo->setSoftwareProduct( SoftwareProductType::factoryNameVersion( $name, $version ));
        // required
        $genSign = $idDto->isSignSet() ? $idDto->getSign() : Sie::PRODUCTNAME;
        $fileInfo->setFileCreation( FileCreationType::factoryByTime( $genSign, $idDto->getGenDate()));
        // required
        $company = $fileInfo->getCompany();
        if( $idDto->isFnrIdSet()) {
            $company->setClientId( $idDto->getFnrId());
        }
        if( $idDto->isOrgnrSet()) {
            $company->setOrganizationId( $idDto->getOrgnr());
            $company->setMultiple( $idDto->getMultiple());
        }
        // required
        $company->setName( $idDto->getFnamn());
        // required (min 1)
        if( ! $toSieEntry && ( 0 < $idDto->countRarDtos())) {
            self::processRarDtos( $idDto, $fileInfo );
        }
        if( $idDto->isValutakodSet()) {
            $fileInfo->setAccountingCurrency(
                AccountingCurrencyType::factoryCurrency( $idDto->getValutakod())
            );
        }
    }

    /**
     * Return array, ( name, version )
     *
     * @param IdDto $idDto
     * @return string[]
     */
    protected static function processNameVersion( IdDto $idDto ) : array
    {
        static $PARNAME = '(' . self::PRODUCTNAME . ')';
        static $PARVRSN = '(' . self::PRODUCTVERSION . ')';
        $name     = $idDto->getProgramnamn();
        $version  = $idDto->getVersion();
        switch( true ) {
            case ( empty( $name ) || ( self::PRODUCTNAME === $name )) :
                $name    = SoftwareProductType::PRODUCTNAME;
                $version = SoftwareProductType::PRODUCTVERSION;
                break;
            case str_contains( $name, $PARNAME ) :
                $name    = trim( str_replace( $PARNAME, StringUtil::$SP0, $name ));
                $version = trim( str_replace( $PARVRSN, StringUtil::$SP0, $version ));
                break;
            case str_contains( $name, self::PRODUCTNAME ) :
                $name    = trim( str_replace( self::PRODUCTNAME, StringUtil::$SP0, $name ));
                $version = trim( str_replace( self::PRODUCTVERSION, StringUtil::$SP0, $version ));
                break;
            default :
                break;
        } // end switch
        return [ $name, $version ];
    }

    /**
     * @param IdDto $idDto
     * @param FileInfoType|FileInfoTypeEntry $fileInfo
     */
    protected static function processRarDtos(
        IdDto $idDto,
        FileInfoType|FileInfoTypeEntry $fileInfo
    ) : void
    {
        $fiscalYearsType = $fileInfo->getFiscalYears(); // is set !!
        foreach( $idDto->getRarDtos() as $rarDto ) {
            $fiscalYearsType->addFiscalYear(
                FiscalYearType::factory()
                    ->setStart( DateTimeUtil::gYearMonthFromDateTime( $rarDto->getStart()))
                    ->setEnd( DateTimeUtil::gYearMonthFromDateTime( $rarDto->getSlut()))
                    ->setPrimary( ( 0 === $rarDto->getArsnr()))
            );
        } // end foreach
    }

    /**
     * Return name for dimensionNr
     *
     * @param Sie4Dto $sieDto
     * @param int   $dimensionNr
     * @param DimObjektDto $dimObjektDto
     * @return string
     */
    protected static function getDimensionName( Sie4Dto $sieDto, int $dimensionNr, DimObjektDto $dimObjektDto ) : string
    {
        return match( true ) {
            // found in dimObjektDto
            $dimObjektDto->isDimensionsNamnSet() => $dimObjektDto->getDimensionNamn(),
             // found in dimDto
            (( 0 < $sieDto->countDimDtos()) &&
                $sieDto->getDimDtos()->isDimensionNrSet( $dimensionNr )) =>
                    $sieDto->getDimDtos()->getDimensionNamn( $dimensionNr ),
             // found in underDimDto
            (( 0 < $sieDto->countUnderDimDtos()) &&
                $sieDto->getUnderDimDtos()->isDimensionNrSet( $dimensionNr )) =>
                $sieDto->getUnderDimDtos()->getDimensionNamn( $dimensionNr ),
            default => StringUtil::$SP0
        }; // end match
    }
}
