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

use Exception;
use RuntimeException;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\IdDto;
use Kigkonsult\Sie4Sdk\Dto\PeriodDto;
use Kigkonsult\Sie4Sdk\Dto\RarDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Util\DateTimeUtil;
use Kigkonsult\Sie5Sdk\Dto\BaseBalanceType;
use Kigkonsult\Sie5Sdk\Dto\BudgetType;
use Kigkonsult\Sie5Sdk\Dto\JournalEntryType;
use Kigkonsult\Sie5Sdk\Dto\LedgerEntryType;
use Kigkonsult\Sie5Sdk\Dto\Sie;

use function key;
use function reset;

/**
 * Class Sie4ILoader
 *
 * Convert Sie data into Sie4EDto
 */
class Sie4ELoader implements Sie4Interface
{
    /**
     * @var Sie4Dto
     */
    private $sie4EDto = null;

    /**
     * @var Sie
     */
    private $sie = null;

    /**
     * Sie4ILoader constructor
     */
    public function __construct()
    {
        $this->sie4EDto = new Sie4Dto();
    }

    /**
     * @param null|Sie $sie
     * @return self
     */
    public static function factory( $sie = null ) : self
    {
        $instance = new self();
        if( ! empty( $sie )) {
            $instance->setSie( $sie );
        }
        return $instance;
    }

    /**
     * Return converted Sie into Sie4Dto
     *
     * @param null|Sie $sie
     * @return Sie4Dto
     * @throws Exception
     * @throws InvalidArgumentException;
     * @throws RuntimeException;
     */
    public function getSie4EDto( $sie = null ) : Sie4Dto
    {
        static $FMT1 = 'Sie saknas';
        if( ! empty( $sie )) {
            $this->sie4EDto = new Sie4Dto();
            $this->setSie( $sie );
        }
        if( ! $this->isSieSet()) {
            throw new InvalidArgumentException( $FMT1, 4201 );
        }

        $this->processIdData();
        $this->processAccountData();
        $this->processDimData();
        $this->processVerData();

        return $this->sie4EDto;
    }

    /**
     * Updates IdData
     *
     * @return void
     * @throws RuntimeException
     * @throws Exception
     */
    private function processIdData()
    {
        $idDto = new IdDto();
        $fileInfo = $this->sie->getFileInfo();

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
        if( ! empty( $value )) {
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
            if( ! empty( $value )) {
                $idDto->setMultiple( $value );
            }
        }

        $value = $company->getName();
        if( ! empty( $value )) {
            $idDto->setFnamn( $value );
        }

        $arsNr = 0;
        foreach( $fileInfo->getFiscalYears()->getFiscalYear() as $fiscalYearType ) {
            $idDto->addRarDto(
                RarDto::factory(
                    $arsNr,
                    DateTimeUtil::gYearMonthToDateTime( $fiscalYearType->getStart(), false ),
                    DateTimeUtil::gYearMonthToDateTime( $fiscalYearType->getEnd(), true )
                )
            );
            $arsNr -= 1;
        } // end foreach

        $accountingCurrency = $fileInfo->getAccountingCurrency();
        if( ! empty( $accountingCurrency )) {
            $value = $accountingCurrency->getCurrency();
            if( ! empty( $value ) ) {
                $idDto->setValutakod( $value );
            }
        }

        $this->sie4EDto->setIdDto( $idDto );
    }

    /**
     * Updates AccountData, pSaldoData and pBudgetData
     *
     * @return void
     */
    private function processAccountData()
    {
        $accounts = $this->sie->getAccounts();
        if( empty( $accounts )) {
            return;
        }
        foreach( $accounts->getAccount() as $accountTypeEntry ) {
            $kontoNr = $accountTypeEntry->getId();
            $this->sie4EDto->addAccount(
                $kontoNr,
                $accountTypeEntry->getName(),
                (string) AccountDto::getKontoType( $accountTypeEntry->getType(), true ),
                $accountTypeEntry->getUnit()
            );
            foreach( $accountTypeEntry->getAccountType() as $elementSet ) {
                switch( key( $elementSet )) {
                    case Sie::CLOSINGBALANCE :
                        $this->sie4EDto->addPsaldoDto(
                            self::getClosingBalancePeriod( $kontoNr, reset( $elementSet ))
                        );
                        break;
                    case Sie::BUDGET :
                        $this->sie4EDto->addPbudgetDto(
                            self::getBudgetPeriod( $kontoNr, reset( $elementSet ))
                        );
                        break;
                } // end switch
            } // end foreach
        } // end foreach
    }

    /**
     * Return PeriodDto for Sie::CLOSINGBALANCE
     *
     * @param string          $kontoNr
     * @param BaseBalanceType $baseBalanceType
     * @return PeriodDto
     */
    private static function getClosingBalancePeriod(
        string $kontoNr,
        BaseBalanceType $baseBalanceType
    ) : PeriodDto
    {
        $period = new PeriodDto();
        $period->setArsnr( 0 );
        $period->setPeriod( DateTimeUtil::YYYYmmFromgYearMonth( $baseBalanceType->getMonth()));
        $period->setKontoNr( $kontoNr );
        $period->setSaldo( $baseBalanceType->getAmount());
        $kvantitet = $baseBalanceType->getQuantity();
        if( null != $kvantitet ) {
            $period->setKvantitet( $kvantitet );
        }
        foreach( $baseBalanceType->getBaseBalanceTypes() as $baseBalanceTypeSet ) {
            if( Sie::OBJECTREFERENCE !== key( $baseBalanceTypeSet )) {
                continue;
            }
            $typeInstance2 = $baseBalanceTypeSet[Sie::OBJECTREFERENCE];
            $period->setDimensionNr( $typeInstance2->getDimId());
            $period->setObjektNr( $typeInstance2->getObjectId());
            break;
        } // end foreach
        return $period;
    }

    /**
     * Return PeriodDto for Sie::BUDGET
     *
     * @param string     $kontoNr
     * @param BudgetType $budgetType
     * @return PeriodDto
     */
    private static function getBudgetPeriod(
        string $kontoNr,
        BudgetType $budgetType
    ) : PeriodDto
    {
        $period = new PeriodDto();
        $period->setArsnr( 0 );
        $period->setPeriod( DateTimeUtil::YYYYmmFromgYearMonth( $budgetType->getMonth()));
        $period->setKontoNr( $kontoNr );
        $period->setSaldo( $budgetType->getAmount());
        $kvantitet = $budgetType->getQuantity();
        if( null != $kvantitet ) {
            $period->setKvantitet( $kvantitet );
        }
        foreach( $budgetType->getObjectReference() as $objectReference ) {
            $period->setDimensionNr( $objectReference->getDimId());
            $period->setObjektNr( $objectReference->getObjectId());
            break;
        } // end foreach
        return $period;
    }

    /**
     * Updates DimData and DimObjektData
     *
     * @return void
     */
    private function processDimData()
    {
        $dimensions = $this->sie->getDimensions();
        if( empty( $dimensions )) {
            return;
        }
        foreach( $dimensions->getDimension() as $dimensionTypeEntry ) {
            $dimensionsNr   = $dimensionTypeEntry->getId();
            $dimensionsNamn = $dimensionTypeEntry->getName();
            $this->sie4EDto->addDim(
                $dimensionsNr,
                $dimensionsNamn
            );
            $objects = $dimensionTypeEntry->getObject();
            if( empty( $objects )) {
                continue;
            }
            foreach( $objects as $objectType ) {
                $this->sie4EDto->addDimObjekt(
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
    private function processVerData()
    {
        $journals = $this->sie->getJournal();
        if( empty( $journals ) ) {
            return; // ??
        }
        foreach( $journals as $journalTypeEntry ) {
            $serie = $journalTypeEntry->getId();
            foreach( $journalTypeEntry->getJournalEntry() as $JournalEntryType ) {
                $this->sie4EDto->addVerDto(
                    self::getVerDto(
                        $JournalEntryType,
                        $serie
                    )
                );
            } // end foreach
        } // end foreach
    }

    /**
     * @param JournalEntryType $journalEntryType
     * @param null|int|string  $serie
     * @return VerDto
     */
    private static function getVerDto(
        JournalEntryType $journalEntryType,
        $serie
    ) : VerDto
    {
        $verDto  = new VerDto();
        if( ! empty( $serie ) || ( '0' === $serie )) {
            $verDto->setSerie( $serie );
        }
        $verNr   = $journalEntryType->getId();
        if( ! empty( $verNr )) {
            $verDto->setVernr( $journalEntryType->getId());
        }
        $verDatum    = $journalEntryType->getJournalDate();
        $verDatumYmd = $verDatum->format( self::SIE4YYYYMMDD );
        $verDto->setVerdatum( $verDatum );
        $vertext     = $journalEntryType->getText();
        if( ! empty( $vertext )) {
            $verDto->setVertext( $vertext );
        }
        $originalEntryInfo = $journalEntryType->getOriginalEntryInfo();
        $regdatum = $originalEntryInfo->getDate();
        if( $verDatumYmd !=
            $regdatum->format( self::SIE4YYYYMMDD )) {
            $verDto->setRegdatum( $regdatum );
        }
        $verDto->setSign( $originalEntryInfo->getBy());
        foreach( $journalEntryType->getLedgerEntry() as $LedgerEntryType ) {
            $verDto->addTransDto( self::getTransDto( $LedgerEntryType, $verDatumYmd ));
        } // end foreach
        return $verDto;
    }

    /**
     * @param LedgerEntryType $LedgerEntryType
     * @param string          $verDatumYmd
     * @return TransDto
     */
    private static function getTransDto(
        LedgerEntryType $LedgerEntryType,
        string $verDatumYmd
    ) : TransDto
    {
        $transDto      = new TransDto();
        $transDto->setKontoNr( $LedgerEntryType->getAccountId());
        $dimObjektData = $LedgerEntryType->getLedgerEntryTypes();
        if( ! empty( $dimObjektData )) {
            foreach( $dimObjektData as $elementSets ) {
                foreach( $elementSets as $elementSet ) {
                    if( ! isset( $elementSet[LedgerEntryType::OBJECTREFERENCE] )) {
                        continue 2;
                    }
                    $objectReferenceType = $elementSet[LedgerEntryType::OBJECTREFERENCE];
                    $transDto->addDimIdObjektId(
                        $objectReferenceType->getDimId(),
                        $objectReferenceType->getObjectId()
                    );
                } // end foreach
            } // end foreach
        } // end if
        $transDto->setBelopp( $LedgerEntryType->getAmount() ?? 0.0 );
        $transDate = $LedgerEntryType->getLedgerDate();
        if( ! empty( $transDate ) &&
            ( $verDatumYmd != $transDate->format( self::SIE4YYYYMMDD ))) {
            $transDto->setTransdat( $transDate );
        }
        $transtext = $LedgerEntryType->getText();
        if( ! empty( $transtext )) {
            $transDto->setTranstext( $transtext );
        }
        $kvantitet = $LedgerEntryType->getQuantity();
        if( null !== $kvantitet ) {
            $transDto->setKvantitet( $kvantitet );
        }
        return $transDto;
    }

    /**
     * @return Sie
     */
    public function getSie() : Sie
    {
        return $this->sie;
    }

    /**
     * @return bool
     */
    public function isSieSet() : bool
    {
        return ( null !== $this->sie );
    }

    /**
     * @param Sie $sie
     * @return self
     */
    public function setSie( Sie $sie ) : self
    {
        $this->sie = $sie;
        return $this;
    }
}
