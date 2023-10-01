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
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie5Sdk\Dto\LedgerEntryTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\SieEntry;

/**
 * Class Sie4ILoader
 *
 * Convert SieEntry data into Sie4IDto
 */
class Sie4ILoader extends Sie4LoaderBase
{
    /**
     * @var Sie4Dto
     */
    private Sie4Dto $sie4IDto;

    /**
     * @var SieEntry
     */
    private SieEntry $sieEntry;

    /**
     * @param SieEntry|null $sieEntry
     * @return self
     */
    public static function factory( ? SieEntry $sieEntry = null ) : self
    {
        $instance = new self();
        if( $sieEntry !== null ) {
            $instance->setSieEntry( $sieEntry );
        }
        return $instance;
    }

    /**
     * Return converted SieEntry into Sie4Dto
     *
     * @param SieEntry|null $sieEntry
     * @return Sie4Dto
     * @throws Exception
     * @throws InvalidArgumentException;
     */
    public function getSie4IDto( ? SieEntry $sieEntry = null ) : Sie4Dto
    {
        static $FMT1 = 'SieEntry saknas';
        if( $sieEntry !== null ) {
            $this->setSieEntry( $sieEntry );
        }
        if( ! $this->isSieEntrySet()) {
            throw new InvalidArgumentException( $FMT1, 4201 );
        }
        $this->sie4IDto = new Sie4Dto( self::processIdData( false, $this->sieEntry->getFileInfo()));
        $this->processAccountData();
        $this->processDimData();
        $this->processVerData();
        return $this->sie4IDto;
    }

    /**
     * Updates AccountData
     *
     * @return void
     */
    private function processAccountData() : void
    {
        $accounts = $this->sieEntry->getAccounts();
        if( $accounts === null ) {
            return;
        }
        foreach((array) $accounts->getAccount() as $accountTypeEntry ) {
            $this->sie4IDto->addAccount(
                $accountTypeEntry->getId(),
                $accountTypeEntry->getName(),
                (string) AccountDto::getKontoType( $accountTypeEntry->getType(), true ),
                $accountTypeEntry->getUnit()
            );
        } // end foreach
    }

    /**
     * Updates DimData and DimObjektData
     *
     * @return void
     */
    private function processDimData() : void
    {
        $dimensions = $this->sieEntry->getDimensions();
        if( $dimensions === null ) {
            return;
        }
        foreach((array) $dimensions->getDimension() as $dimensionTypeEntry ) {
            $dimensionsNr   = $dimensionTypeEntry->getId();
            $dimensionsNamn = $dimensionTypeEntry->getName();
            $this->sie4IDto->addDim(
                $dimensionsNr,
                $dimensionsNamn
            );
            $objects = $dimensionTypeEntry->getObject();
            if( empty( $objects )) {
                continue;
            }
            foreach( $objects as $objectType ) {
                $this->sie4IDto->addDimObjekt(
                    $dimensionsNr,
                    $objectType->getId(),
                    $objectType->getName()
                );
            } // end foreach
        } // end foreach
    }

    /**
     * Updates verDto/TransDto
     *
     * @return void
     */
    private function processVerData() : void
    {
        $journals = $this->sieEntry->getJournal();
        if( empty( $journals )) {
            return; // ??
        }
        foreach( $journals as $journalTypeEntry ) {
            $serie = $journalTypeEntry->getId();
            foreach( $journalTypeEntry->getJournalEntry() as $journalEntryTypeEntry ) {
                $this->sie4IDto->addVerDto( self::getVerDto( $journalEntryTypeEntry, $serie ));
            } // end foreach
        } // end foreach
    }

    /**
     * @param LedgerEntryTypeEntry $ledgerEntryTypeEntry
     * @param string $verDatumYmd
     * @return TransDto
     */
    protected static function getTransDto(
        LedgerEntryTypeEntry $ledgerEntryTypeEntry,
        string $verDatumYmd
    ) : TransDto
    {
        $transDto      = new TransDto();
        $transDto->setKontoNr( $ledgerEntryTypeEntry->getAccountId());
        $dimObjektData = $ledgerEntryTypeEntry->getLedgerEntryTypeEntries();
        if( ! empty( $dimObjektData )) {
            foreach( $dimObjektData as $elementSets ) {
                foreach( $elementSets as $elementSet ) {
                    if( ! isset( $elementSet[LedgerEntryTypeEntry::OBJECTREFERENCE] )) {
                        continue 2;
                    }
                    $objectReferenceType = $elementSet[LedgerEntryTypeEntry::OBJECTREFERENCE];
                    $transDto->addDimIdObjektId(
                        $objectReferenceType->getDimId(),
                        $objectReferenceType->getObjectId()
                    );
                } // end foreach
            } // end foreach
        } // end if
        $transDto->setBelopp( $ledgerEntryTypeEntry->getAmount() ?? 0.0 );
        $transDate = $ledgerEntryTypeEntry->getLedgerDate();
        if( $transDate !== null &&
            ( $verDatumYmd !== $transDate->format( self::SIE4YYYYMMDD ))) {
            $transDto->setTransdat( $transDate );
        }
        $transtext = $ledgerEntryTypeEntry->getText();
        if( ! empty( $transtext )) {
            $transDto->setTranstext( $transtext );
        }
        $kvantitet = $ledgerEntryTypeEntry->getQuantity();
        if( null !== $kvantitet ) {
            $transDto->setKvantitet( $kvantitet );
        }
        return $transDto;
    }

    /**
     * @return SieEntry
     */
    public function getSieEntry() : SieEntry
    {
        return $this->sieEntry;
    }

    /**
     * @return bool
     */
    public function isSieEntrySet() : bool
    {
        return isset( $this->sieEntry );
    }

    /**
     * @param SieEntry $sieEntry
     * @return self
     */
    public function setSieEntry( SieEntry $sieEntry ) : self
    {
        $this->sieEntry = $sieEntry;
        return $this;
    }
}
