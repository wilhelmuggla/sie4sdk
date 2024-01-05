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

use DateTime;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Dto\AccountsTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\AccountTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\CompanyTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\DimensionsTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\DimensionTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\FileInfoTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\JournalEntryTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\JournalTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\LedgerEntryTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\ObjectReferenceType;
use Kigkonsult\Sie5Sdk\Dto\ObjectType;
use Kigkonsult\Sie5Sdk\Dto\OriginalEntryInfoType;
use Kigkonsult\Sie5Sdk\Dto\SieEntry;

/**
 * Class Sie5EntryLoader
 *
 * Load SieEntry(5) instance using Sie4IDto instance
 * SieEntry requirements
 * FileInfoTypeEntry :
 *   SoftwareProduct :  (name + version)
 *   FileCreationType : (time + by)
 *   CompanyTypeEntry :  (organizationId)
 */
class Sie5EntryLoader extends Sie5LoaderBase
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
     * Sie5EntryLoader constructor
     *
     * @param Sie4Dto|null $sie4IDto
     */
    public function __construct( ? Sie4Dto $sie4IDto = null )
    {
        $this->sieEntry = self::newSieEntry();
        if( $sie4IDto !== null ) {
            $this->setSie4IDto( $sie4IDto );
        }
    }

    /**
     * @param Sie4Dto $sie4IDto
     * @return self
     * @throws InvalidArgumentException
     */
    public static function factory(  Sie4Dto $sie4IDto ): self
    {
        return new self( $sie4IDto );
    }

    /**
     * Init SieEntry, prepare for Sie5 XML write
     *
     * @return SieEntry
     */
    private static function newSieEntry() : SieEntry
    {
        return sieEntry::factory()
            ->setXMLattribute(
                SieEntry::XMLNS,
                SieEntry::SIE5URI
            )
            ->setXMLattribute(
                SieEntry::XMLNS_XSI,
                SieEntry::XMLSCHEMAINSTANCE
            )
            ->setXMLattribute(
                SieEntry::XMLNS_XSD,
                SieEntry::XMLSCHEMA
            )
            ->setXMLattribute(
                SieEntry::XSI_SCHEMALOCATION,
                SieEntry::SIE5SCHEMALOCATION
            )
            ->setFileInfo(
                FileInfoTypeEntry::factory()
                    ->setCompany( CompanyTypeEntry::factory() )
            );
    }

    /**
     * @param Sie4Dto|null $sie4IDto
     * @return SieEntry
     * @throws InvalidArgumentException
     */
    public function getSieEntry( ? Sie4Dto $sie4IDto = null ) : SieEntry
    {
        static $FMT1 = 'No input';
        if( $sie4IDto !== null ) {
            $this->sieEntry = self::newSieEntry();
            $this->setSie4IDto( $sie4IDto );
        }
        elseif( ! isset( $this->sie4IDto )) {
            throw new InvalidArgumentException( $FMT1 );
        }

        self::processIdDto(
            true,
            $this->sie4IDto->getIdDto(),    // IdDto always set
            $this->sieEntry->getFileInfo()  // FileInfoTypeEntry, always set
        );
        $this->processAccountDtos();
        $this->processDimDtos();
        $this->processDimObjektDtos();
        $this->processVerDtos();

        return $this->sieEntry;
    }

    /**
     * Process Sie4 accountDtos into SieEntry
     *
     * @return void
     * @since 1.8.6 2023-09-29
     */
    private function processAccountDtos() : void
    {
        if( empty( $this->sie4IDto->countAccountDtos())) {
            return;
        }
        $accountDtos = $this->sie4IDto->getAccountDtos();
        $accounts    = $this->sieEntry->getAccounts();
        if( $accounts === null ) {
            $accounts = AccountsTypeEntry::factory();
            $this->sieEntry->setAccounts( $accounts );
        }
        foreach( $accountDtos as $accountDto ) {
            $kontoNr  = $accountDto->getKontoNr();
            $kontoTyp = $accountDto->isKontotypSet()
                ? $accountDto->getKontoTyp()
                : AccountDto::findOutKontoType( $kontoNr );
            $accountTypeEntry = AccountTypeEntry::factoryIdNameType(
                $accountDto->getKontoNr(),
                $accountDto->getKontoNamn(),
                (string) AccountDto::getKontoType( $kontoTyp, false )
            );
            if( $accountDto->isEnhetSet()) {
                $accountTypeEntry->setUnit( $accountDto->getEnhet());
            }
            $accounts->addAccount( $accountTypeEntry );
        } // end foreach
    }

    /**
     * Process Sie4 dimDtos into SieEntry
     *
     * @return void
     */
    private function processDimDtos() : void
    {
        if( empty( $this->sie4IDto->countDimDtos())) {
            return;
        }
        $dimensions = $this->sieEntry->getDimensions();
        if( $dimensions === null ) {
            $dimensions = DimensionsTypeEntry::factory();
            $this->sieEntry->setDimensions( $dimensions );
        }
        foreach( $this->sie4IDto->getDimDtos() as $dimDto ) {
            $dimensionTypeEntry = DimensionTypeEntry::factoryIdName(
                $dimDto->getDimensionNr(),
                $dimDto->getDimensionsNamn()
            );
            $dimensions->addDimension( $dimensionTypeEntry );
        } // end foreach
    }

    /**
     * Process Sie4 dimObjektDtos into SieEntry
     *
     * @return void
     */
    private function processDimObjektDtos() : void
    {
        $dimObjektDtos = $this->sie4IDto->getDimObjektDtos();
        if( empty( $dimObjektDtos )) {
            return;
        }
        $dimensions = $this->sieEntry->getDimensions();
        if( $dimensions === null ) {
            $dimensions = DimensionsTypeEntry::factory();
            $this->sieEntry->setDimensions( $dimensions );
        }
        foreach( $dimObjektDtos as $dimObjektDto ) {
            $dimensionNr = $dimObjektDto->getDimensionNr();
            // find or create DimensionTypeEntry
            $found = false;
            foreach((array) $dimensions->getDimension() as $dimensionTypeEntry ) {
                if( $dimensionNr === $dimensionTypeEntry->getId()) {
                    $found = true;
                    break;
                }
            } // end foreach
            if( ! $found ) { // create new dimensionTypeEntry
                $dimensionsNamn = StringUtil::$SP0;
                if( $dimObjektDto->isDimensionsNamnSet()) {
                    $dimensionsNamn = $dimObjektDto->getDimensionsNamn();
                }
                elseif( ! empty( $this->sie4IDto->countDimDtos())) {
                    foreach( $this->sie4IDto->getDimDtos() as $dimData ) {
                        // checked in dimDtos in validator, MUST exist
                        if( $dimensionNr === $dimData->getDimensionNr()) {
                            $dimensionsNamn = $dimData->getDimensionsNamn();
                            break;
                        }
                    } // end forech
                } // end if
                $dimensionTypeEntry = DimensionTypeEntry::factoryIdName(
                    $dimensionNr,
                    $dimensionsNamn
                );
                $dimensions->addDimension( $dimensionTypeEntry );
            } // end if ! found
            $dimensionTypeEntry->addObject(
                ObjectType::factoryIdName(
                    $dimObjektDto->getObjektNr(),
                    $dimObjektDto->getObjektNamn()
                )
            );
        } // end foreach
    }

    /**
     * Process Sie4 verDtos into SieEntry
     *
     * @return void
     */
    private function processVerDtos() : void
    {
        if( empty( $this->sie4IDto->countVerDtos())) {
            return;
        }
        $genSign = $this->sie4IDto->getIdDto()->isSignSet()
            ? $this->sie4IDto->getIdDto()->getSign()
            : SieEntry::PRODUCTNAME;
        foreach( $this->sie4IDto->getVerDtos() as $verDto ) {
            $serie = $verDto->isSerieSet() ? $verDto->getSerie() : StringUtil::$SP0;
            $journalTypeEntry      = $this->getJournalTypeEntry( $serie );
            $journalEntryTypeEntry = JournalEntryTypeEntry::factory();
            $journalTypeEntry->addJournalEntry( $journalEntryTypeEntry );
            self::processSingleVerDto( $verDto, $journalEntryTypeEntry, $genSign );
        } // end foreach
    }

    /**
     * Return found or new JournalTypeEntry
     *
     * @param string $serie
     * @return JournalTypeEntry
     */
    private function getJournalTypeEntry( string $serie ) : JournalTypeEntry
    {
        $journalTypeEntryFound = false;
        $journalTypeEntry      = null;
        $journals = $this->sieEntry->getJournal();
        if( ! empty( $journals )) {
            foreach( $journals as $journalTypeEntry ) {
                $journalTypeEntryId = $journalTypeEntry->getId();
                if( empty( $serie ) && empty( $journalTypeEntryId )) {
                    $journalTypeEntryFound = true;
                    break;
                }
                if( 0 === strcmp( $serie, (string) $journalTypeEntryId )) {
                    $journalTypeEntryFound = true;
                    break;
                }
            } // end foreach
        } // end if
        if( ! $journalTypeEntryFound ) {
            // create if NOT exists
            $journalTypeEntry = JournalTypeEntry::factory();
            $this->sieEntry->addJournal( $journalTypeEntry );
            $journalTypeEntry->setId( $serie );
        } // end if
        return $journalTypeEntry;
    }

    /**
     * @var string
     */
    private static string $YYYYMMDD = 'Ymd';

    /**
     * Process single VerDto
     *
     * If regdatum found, used if regdatum == verDatum
     *
     * @param VerDto                $verDto
     * @param JournalEntryTypeEntry $journalEntryTypeEntry
     * @param string $genSign
     * @return void
     */
    private static function processSingleVerDto(
        VerDto $verDto,
        JournalEntryTypeEntry $journalEntryTypeEntry,
        string $genSign
    ) : void
    {
        if( $verDto->isVernrSet()) {
            $journalEntryTypeEntry->setId( $verDto->getVernr());
        }
        // required
        $verDatum = $verDto->isVerdatumSet()
            ? $verDto->getVerdatum()
            : new DateTime();
        $journalEntryTypeEntry->setJournalDate( $verDatum );
        if( $verDto->isVertextSet()) {
            $journalEntryTypeEntry->setText( $verDto->getVertext());
        }
        $journalEntryTypeEntry->setOriginalEntryInfo(
            OriginalEntryInfoType::factoryByDate(
                ( $verDto->isSignSet() ? $verDto->getSign() : $genSign ),
                ( $verDto->isRegdatumSet() ? $verDto->getRegdatum() : $verDatum )
            )
        );
        foreach( $verDto->getTransDtos() as $transDto ) {
            if( self::TRANS !== $transDto->getTransType()) {
                // skip RTRANS,BTRANS
                continue;
            }
            $ledgerEntryTypeEntry = LedgerEntryTypeEntry::factory();
            $journalEntryTypeEntry->addLedgerEntry( $ledgerEntryTypeEntry );
            self::processSingleTransDto(
                $transDto,
                $ledgerEntryTypeEntry,
                $verDatum->format( self::$YYYYMMDD )
            );
        } // end foreach
    }

    /**
     * @param TransDto             $transDto
     * @param LedgerEntryTypeEntry $ledgerEntryTypeEntry
     * @param string               $verDatum    SIE4YYYYMMDD
     * @return void
     */
    private static function processSingleTransDto(
        TransDto                $transDto,
        LedgerEntryTypeEntry $ledgerEntryTypeEntry,
        string               $verDatum
    ) : void
    {
        $ledgerEntryTypeEntry->setAccountId( $transDto->getKontoNr());
        if( 0 < $transDto->countObjektlista()) {
            foreach( $transDto->getObjektlista() as $dimObjektDto ) {
                $ledgerEntryTypeEntry->addLedgerEntryTypeEntry(
                    LedgerEntryTypeEntry::OBJECTREFERENCE,
                    ObjectReferenceType::factoryDimIdObjectId(
                        $dimObjektDto->getDimensionNr(),
                        $dimObjektDto->getObjektNr()
                    )
                );
            } // end foreach
        }
        $ledgerEntryTypeEntry->setAmount( $transDto->getBelopp() ?? 0.0 );
        if( $transDto->isTransdatSet()) {
            // skipped if equal to verDatum
            $transDat = $transDto->getTransdat();
            if( $verDatum !== $transDat->format( self::$YYYYMMDD )) {
                $ledgerEntryTypeEntry->setLedgerDate( $transDto->getTransdat());
            }
        }
        if( $transDto->isTranstextSet()) {
            $ledgerEntryTypeEntry->setText( $transDto->getTranstext());
        }
        if( $transDto->isKvantitetSet()) {
            $ledgerEntryTypeEntry->setQuantity( $transDto->getKvantitet());
        }
    } // end foreach

    /**
     * @return Sie4Dto
     */
    public function getSie4IDto() : Sie4Dto
    {
        return $this->sie4IDto;
    }

    /**
     * @param Sie4Dto $sie4IDto
     * @return self
     * @throws InvalidArgumentException
     */
    public function setSie4IDto( Sie4Dto $sie4IDto ) : self
    {
        $this->sie4IDto = $sie4IDto;
        return $this;
    }
}
