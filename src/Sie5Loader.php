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

use DateTime;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\PeriodDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Util\DateTimeUtil;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Dto\AccountsType;
use Kigkonsult\Sie5Sdk\Dto\AccountType;
use Kigkonsult\Sie5Sdk\Dto\BaseBalanceType;
use Kigkonsult\Sie5Sdk\Dto\BudgetType;
use Kigkonsult\Sie5Sdk\Dto\CompanyType;
use Kigkonsult\Sie5Sdk\Dto\DimensionsType;
use Kigkonsult\Sie5Sdk\Dto\DimensionType;
use Kigkonsult\Sie5Sdk\Dto\EntryInfoType;
use Kigkonsult\Sie5Sdk\Dto\FileInfoType;
use Kigkonsult\Sie5Sdk\Dto\FiscalYearsType;
use Kigkonsult\Sie5Sdk\Dto\JournalEntryType;
use Kigkonsult\Sie5Sdk\Dto\JournalType;
use Kigkonsult\Sie5Sdk\Dto\LedgerEntryType;
use Kigkonsult\Sie5Sdk\Dto\ObjectReferenceType;
use Kigkonsult\Sie5Sdk\Dto\ObjectType;
use Kigkonsult\Sie5Sdk\Dto\OriginalEntryInfoType;
use Kigkonsult\Sie5Sdk\Dto\Sie;

use function strcmp;

/**
 * Class Sie5Loader
 *
 * Load Sie(5) instance using Sie4EDto instance
 * Sie requirements
 * FileInfoTypeEntry :
 *   SoftwareProduct :  (name + version)
 *   FileCreationType : (time + by)
 *   CompanyTypeEntry :  (organizationId + name)
 * FiscalYearsType :  min one FiscalYearType
 * AccountingCurrencyType : (currency)
 * AccountsType : ( but AccountType minOccurs="0")
 * SignatureType !!
 */
class Sie5Loader extends Sie5LoaderBase
{
    /**
     * @var Sie4Dto|null
     */
    private ?Sie4Dto $sie4EDto = null;

    /**
     * @var Sie
     */
    private Sie $sie;

    /**
     * Sie5EntryLoader constructor
     */
    public function __construct()
    {
        $this->sie = self::newSie();
    }

    /**
     * @param Sie4Dto|null $sie4EDto
     * @return self
     * @throws InvalidArgumentException
     */
    public static function factory( ? Sie4Dto $sie4EDto = null ): self
    {
        $instance = new self();
        if( $sie4EDto !== null ) {
            $instance->setSie4EDto( $sie4EDto );
        }
        return $instance;
    }

    /**
     * Init Sie, prepare for Sie5 XML write
     *
     * @return Sie
     */
    private static function newSie() : Sie
    {
        return sie::factory()
            ->setXMLattribute(
                Sie::XMLNS,
                Sie::SIE5URI
            )
            ->setXMLattribute(
                Sie::XMLNS_XSI,
                Sie::XMLSCHEMAINSTANCE
            )
            ->setXMLattribute(
                Sie::XMLNS_XSD,
                Sie::XMLSCHEMA
            )
            ->setXMLattribute(
                Sie::XSI_SCHEMALOCATION,
                Sie::SIE5SCHEMALOCATION
            )
            ->setFileInfo(
                FileInfoType::factory()
                    ->setCompany( CompanyType::factory())
                    ->setFiscalYears( FiscalYearsType::factory())
            );
    }

    /**
     * @param Sie4Dto|null $sie4Idata
     * @return Sie
     * @throws InvalidArgumentException
     */
    public function getSie( ? Sie4Dto $sie4Idata = null ) : Sie
    {
        if( $sie4Idata !== null ) {
            $this->sie = self::newSie();
            $this->setSie4EDto( $sie4Idata );
        }

        self::processIdDto(
            false,
            $this->sie4EDto->getIdDto(), // IdDto always set
            $this->sie->getFileInfo()    // FileInfoTypeEntry, always set
        );
        $this->processAccountDtos();
        $this->processDimDtos();
        $this->processDimObjektDtos();
        $this->processVerDtos();

        return $this->sie;
    }

    /**
     * Process Sie4 accountDtos into Sie
     *
     * @return void
     */
    private function processAccountDtos() : void
    {
        if( empty( $this->sie4EDto->countAccountDtos())) {
            return;
        }
        $accountDtos = $this->sie4EDto->getAccountDtos();
        $accounts    = $this->sie->getAccounts();
        if( $accounts === null ) {
            $accounts = AccountsType::factory();
            $this->sie->setAccounts( $accounts );
        }
        foreach( $accountDtos as $accountDto ) {
            $kontoNr = $accountDto->getKontoNr();
            $accountType = AccountType::factoryIdNameType(
                $kontoNr,
                $accountDto->getKontoNamn(),
                (string) AccountDto::getKontoType( $accountDto->getKontoTyp(), false )
            );
            if( $accountDto->isEnhetSet()) {
                $accountType->setUnit( $accountDto->getEnhet());
            }
            // Check pPsaldoDtos (arsnr=0) for BaseBalanceType
            // (all but below skipped due to month/period not exists)
            if( $this->sie4EDto->isPsaldoKontoNrSet( $kontoNr )) {
                $accountType->addAccountType(
                    Sie::CLOSINGBALANCE,
                    self::getBaseBalanceType(
                        $this->sie4EDto->getPbudgetForKontoNr( $kontoNr )
                    )
                );
            }
            // Check pBudgetDtos (arsnr=0) for BUDGET
            if( $this->sie4EDto->isPbudgetKontoNrSet( $kontoNr )) {
                $accountType->addAccountType(
                    Sie::BUDGET,
                    self::getBudgetType(
                        $this->sie4EDto->getPbudgetForKontoNr( $kontoNr )
                    )
                );
            }
            $accounts->addAccount( $accountType );
        } // end foreach
    }

    /**
     * Return uppdated BaseBalanceType
     *
     * @param PeriodDto $periodDto
     * @return BaseBalanceType
     */
    private static function getBaseBalanceType( PeriodDto $periodDto ) : BaseBalanceType
    {
        $baseBalanceType = BaseBalanceType::factoryMonthAmount(
            DateTimeUtil::gYearMonthFromString( $periodDto->getPeriod()),
            $periodDto->getSaldo()
        );
        if( $periodDto->isKvantitetSet()) {
            $baseBalanceType->setQuantity( $periodDto->getKvantitet());
        }
        if( $periodDto->isDimensionsNrSet()) {
            $baseBalanceType->addBaseBalanceType(
                Sie::OBJECTREFERENCE,
                ObjectReferenceType::factoryDimIdObjectId(
                    $periodDto->getDimensionNr(),
                    $periodDto->getObjektNr()
                )
            );
        }
        return $baseBalanceType;
    }

    /**
     * Return uppdated BudgetType
     *
     * @param PeriodDto $periodDto
     * @return BudgetType
     */
    private static function getBudgetType( PeriodDto $periodDto ) : BudgetType
    {
        $budgetType = BudgetType::factoryMonthAmount(
            DateTimeUtil::gYearMonthFromString( $periodDto->getPeriod()),
            $periodDto->getSaldo()
        );
        if( $periodDto->isKvantitetSet()) {
            $budgetType->setQuantity( $periodDto->getKvantitet());
        }
        if( $periodDto->isDimensionsNrSet()) {
            $budgetType->addObjectReference(
                ObjectReferenceType::factoryDimIdObjectId(
                    $periodDto->getDimensionNr(),
                    $periodDto->getObjektNr()
                )
            );
        }
        return $budgetType;
    }

    /**
     * Process Sie4 dimDtos into Sie
     *
     * @return void
     */
    private function processDimDtos() : void
    {
        if( empty( $this->sie4EDto->countDimDtos())) {
            return;
        }
        $dimensions = $this->sie->getDimensions();
        if( $dimensions === null ) {
            $dimensions = DimensionsType::factory();
            $this->sie->setDimensions( $dimensions );
        }
        foreach( $this->sie4EDto->getDimDtos() as $dimDto ) {
            $DimensionType = DimensionType::factoryIdName(
                $dimDto->getDimensionNr(),
                $dimDto->getDimensionsNamn()
            );
            $dimensions->addDimension( $DimensionType );
        } // end foreach
    }

    /**
     * Process Sie4 dimObjektDtos into Sie
     *
     * @return void
     */
    private function processDimObjektDtos() : void
    {
        $dimObjektDtos = $this->sie4EDto->getDimObjektDtos();
        if( empty( $dimObjektDtos )) {
            return;
        }
        $dimensions = $this->sie->getDimensions();
        if( $dimensions === null ) {
            $dimensions = DimensionsType::factory();
            $this->sie->setDimensions( $dimensions );
        }
        foreach( $dimObjektDtos as $dimObjektDto ) {
            $dimensionNr = $dimObjektDto->getDimensionNr();
            // find or create DimensionType
            $found = false;
            foreach( $dimensions->getDimension() as $DimensionType ) {
                if( $dimensionNr === $DimensionType->getId()) {
                    $found = true;
                    break;
                }
            } // end foreach
            if( ! $found ) { // create new DimensionType
                $dimensionsNamn = StringUtil::$SP0;
                if( $dimObjektDto->isDimensionsNamnSet()) {
                    $dimensionsNamn = $dimObjektDto->getDimensionsNamn();
                }
                elseif( ! empty( $this->sie4EDto->countDimDtos())) {
                    foreach( $this->sie4EDto->getDimDtos() as $dimData ) {
                        // checked in dimDtos in validator, MUST exist
                        if( $dimensionNr === $dimData->getDimensionNr()) {
                            $dimensionsNamn = $dimData->getDimensionsNamn();
                            break;
                        }
                    } // end forech
                } // end if
                $DimensionType = DimensionType::factoryIdName(
                    $dimensionNr,
                    $dimensionsNamn
                );
                $dimensions->addDimension( $DimensionType );
            } // end if ! found
            $DimensionType->addObject(
                ObjectType::factoryIdName(
                    $dimObjektDto->getObjektNr(),
                    $dimObjektDto->getObjektNamn()
                )
            );
        } // end foreach
    }

    /**
     * Process Sie4 verDtos into Sie
     *
     * @return void
     */
    private function processVerDtos() : void
    {
        if( empty( $this->sie4EDto->countVerDtos())) {
            return;
        }
        $genSign = $this->sie4EDto->getIdDto()->isSignSet()
            ? $this->sie4EDto->getIdDto()->getSign()
            : Sie::PRODUCTNAME;
        foreach( $this->sie4EDto->getVerDtos() as $verDto ) {
            $serie = $verDto->isSerieSet() ? $verDto->getSerie() : StringUtil::$SP0;
            $JournalType = $this->getJournalType( $serie );
            $JournalEntryType = JournalEntryType::factory();
            $JournalType->addJournalEntry( $JournalEntryType );
            self::processSingleVerDto( $verDto, $JournalEntryType, $genSign );
        } // end foreach
    }

    /**
     * Return found or new JournalType
     *
     * @param string $serie
     * @return JournalType
     */
    private function getJournalType( string $serie ) : JournalType
    {
        $JournalTypeFound = false;
        $JournalType      = null;
        $journals = $this->sie->getJournal();
        if( ! empty( $journals )) {
            foreach( $journals as $JournalType ) {
                $JournalTypeId = $JournalType->getId() ?? StringUtil::$SP0;
                if( empty( $serie ) && empty( $JournalTypeId )) {
                    $JournalTypeFound = true;
                    break;
                }
                if( 0 === strcmp( $serie, $JournalTypeId )) {
                    $JournalTypeFound = true;
                    break;
                }
            } // end foreach
        } // end if
        if( ! $JournalTypeFound ) {
            // create if NOT exists
            $JournalType = JournalType::factory();
            $this->sie->addJournal( $JournalType );
            $JournalType->setId( $serie )
                        ->setName( $serie );
        } // end if
        return $JournalType;
    }

    /**
     * Process single VerDto
     *
     * If regdatum found, used if regdatum == verDatum
     *
     * @param VerDto                $verDto
     * @param JournalEntryType $JournalEntryType
     * @param string $genSign
     * @return void
     */
    private static function processSingleVerDto(
        VerDto $verDto,
        JournalEntryType $JournalEntryType,
        string $genSign
    ) : void
    {
        if( $verDto->isVernrSet()) {
            $JournalEntryType->setId( $verDto->getVernr());
        }
        // required
        $verDatum = $verDto->isVerdatumSet()
            ? $verDto->getVerdatum()
            : ( new DateTime())->setTime( 0,0 );
        $JournalEntryType->setJournalDate( $verDatum );
        if( $verDto->isVertextSet()) {
            $JournalEntryType->setText( $verDto->getVertext());
        }
        $JournalEntryType->setEntryInfo(
            EntryInfoType::factoryByDate(
                ( $verDto->isSignSet() ? $verDto->getSign() : $genSign ),
                ( $verDto->isRegdatumSet() ? $verDto->getRegdatum() : $verDatum )
            )
        );
        $JournalEntryType->setOriginalEntryInfo(
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
            $LedgerEntryType = LedgerEntryType::factory();
            $JournalEntryType->addLedgerEntry( $LedgerEntryType );
            self::processSingleTransDto(
                $transDto,
                $LedgerEntryType,
                $verDatum->format( self::SIE4YYYYMMDD )
            );
        } // end foreach
    }

    /**
     * @param TransDto        $transDto
     * @param LedgerEntryType $LedgerEntryType
     * @param string          $verDatum SIE4YYYYMMDD
     * @return void
     */
    private static function processSingleTransDto(
        TransDto           $transDto,
        LedgerEntryType $LedgerEntryType,
        string          $verDatum
    ) : void
    {
        $LedgerEntryType->setAccountId( $transDto->getKontoNr());
        if( 0 < $transDto->countObjektlista()) {
            foreach( $transDto->getObjektlista() as $dimObjektDto ) {
                $LedgerEntryType->addLedgerEntryType(
                    LedgerEntryType::OBJECTREFERENCE,
                    ObjectReferenceType::factoryDimIdObjectId(
                        $dimObjektDto->getDimensionNr(),
                        $dimObjektDto->getObjektNr()
                    )
                );
            } // end foreach
        }
        $LedgerEntryType->setAmount( $transDto->getBelopp() ?? 0.0 );
        if( $transDto->isTransdatSet()) {
            // skipped if equal to verDatum
            $transDat = $transDto->getTransdat();
            if( $verDatum !== $transDat->format( self::SIE4YYYYMMDD )) {
                $LedgerEntryType->setLedgerDate( $transDto->getTransdat());
            }
        }
        if( $transDto->isTranstextSet()) {
            $LedgerEntryType->setText( $transDto->getTranstext());
        }
        if( $transDto->isKvantitetSet()) {
            $LedgerEntryType->setQuantity( $transDto->getKvantitet());
        }
    } // end foreach

    /**
     * @return Sie4Dto
     */
    public function getSie4EDto() : Sie4Dto
    {
        return $this->sie4EDto;
    }

    /**
     * @param Sie4Dto $sie4EDto
     * @return self
     * @throws InvalidArgumentException
     */
    public function setSie4EDto( Sie4Dto $sie4EDto ) : self
    {
        $this->sie4EDto = $sie4EDto;
        return $this;
    }
}
