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
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Dto\IdDto;
use Kigkonsult\Sie4Sdk\Dto\RarDto;
use Kigkonsult\Sie4Sdk\Util\DateTimeUtil;
use Kigkonsult\Sie5Sdk\Dto\FileInfoType;
use Kigkonsult\Sie5Sdk\Dto\FileInfoTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\JournalEntryType;
use Kigkonsult\Sie5Sdk\Dto\JournalEntryTypeEntry;
use RuntimeException;

abstract class Sie4LoaderBase implements Sie4Interface
{
    /**
     * Return IdData
     *
     * @param bool $isSie4E
     * @param FileInfoType|FileInfoTypeEntry $fileInfo
     * @return IdDto
     * @throws RuntimeException
     * @throws Exception
     */
    protected static function processIdData( bool $isSie4E, FileInfoType|FileInfoTypeEntry $fileInfo ) : IdDto
    {
        $idDto    = new IdDto();

        $softwareProduct = $fileInfo->getSoftwareProduct();
        $value = $softwareProduct->getName();
        if( ! empty( $value )) {
            $idDto->setProgramnamn( $value );
        }
        $value = $softwareProduct->getVersion();
        if( ! empty( $value )) {
            $idDto->setVersion( $value );
        }

        $fileCreation = $fileInfo->getFileCreation();
        $value = $fileCreation->getTime();
        if( $value !== null ) {
            $idDto->setGenDate( $value);
        }
        $value = $fileCreation->getBy();
        if( ! empty( $value )) {
            $idDto->setSign( $value);
        }

        $company = $fileInfo->getCompany();
        $value   = $company->getClientId();
        if( ! empty( $value )) {
            $idDto->setFnrId(  $value );
        }

        $value = $company->getOrganizationId();
        if( ! empty( $value )) {
            $idDto->setOrgnr( $value );
            $value = $company->getMultiple();
            if( $value !== null ) {
                $idDto->setMultiple( $value );
            }
        }

        $value = $company->getName();
        if( ! empty( $value )) {
            $idDto->setFnamn( $value );
        }

        if( $isSie4E ) {
            $arsNr = 0;
            foreach( $fileInfo->getFiscalYears()->getFiscalYear() as $fiscalYearType ) {
                $idDto->addRarDto(
                    RarDto::factory(
                        $arsNr,
                        DateTimeUtil::gYearMonthToDateTime( $fiscalYearType->getStart(), false ),
                        DateTimeUtil::gYearMonthToDateTime( $fiscalYearType->getEnd(), true )
                    )
                );
                --$arsNr;
            } // end foreach
        } // end if

        $accountingCurrency = $fileInfo->getAccountingCurrency();
        if( $accountingCurrency !== null ) {
            $value = $accountingCurrency->getCurrency();
            if( ! empty( $value )) {
                $idDto->setValutakod( $value );
            }
        }

        return $idDto;
    }

    /**
     * @param JournalEntryType|JournalEntryTypeEntry $journalEntry
     * @param int|string|null $serie
     * @return VerDto
     */
    protected static function getVerDto(
        JournalEntryType|JournalEntryTypeEntry $journalEntry,
        int | string | null $serie = null
    ) : VerDto
    {
        $verDto  = new VerDto();
        if( ! empty( $serie ) || ( '0' === $serie )) {
            $verDto->setSerie( $serie );
        }
        $verNr   = $journalEntry->getId();
        if( $verNr !== null ) {
            $verDto->setVernr( $journalEntry->getId());
        }
        $verDatum    = $journalEntry->getJournalDate();
        $verDatumYmd = $verDatum->format( self::SIE4YYYYMMDD );
        $verDto->setVerdatum( $verDatum );
        $vertext     = $journalEntry->getText();
        if( ! empty( $vertext )) {
            $verDto->setVertext( $vertext );
        }
        $originalEntryInfo = $journalEntry->getOriginalEntryInfo();
        $regdatum = $originalEntryInfo->getDate();
        if( $verDatumYmd !== $regdatum->format( self::SIE4YYYYMMDD )) {
            $verDto->setRegdatum( $regdatum );
        }
        $verDto->setSign( $originalEntryInfo->getBy());
        foreach( $journalEntry->getLedgerEntry() as $LedgerEntryType ) {
            $verDto->addTransDto( static::getTransDto( $LedgerEntryType, $verDatumYmd ));
        } // end foreach
        return $verDto;
    }
}
