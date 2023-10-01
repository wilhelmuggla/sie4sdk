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
use Kigkonsult\Sie5Sdk\Dto\SieEntry;
use RuntimeException;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\PeriodDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Util\DateTimeUtil;
use Kigkonsult\Sie5Sdk\Dto\BaseBalanceType;
use Kigkonsult\Sie5Sdk\Dto\BudgetType;
use Kigkonsult\Sie5Sdk\Dto\LedgerEntryType;
use Kigkonsult\Sie5Sdk\Dto\Sie;

use function key;
use function reset;

/**
 * Class Sie4ILoader
 *
 * Convert Sie data into Sie4EDto
 */
class Sie4ELoader extends Sie4LoaderBase
{
    /**
     * @var Sie4Dto
     */
    private Sie4Dto $sie4EDto;

    /**
     * @var Sie
     */
    private Sie $sie;

    /**
     * @param null|Sie|SieEntry $sie
     * @return self
     */
    public static function factory( null|Sie|SieEntry $sie = null ) : self
    {
        $instance = new self();
        if( $sie !== null ) {
            $instance->setSie( $sie );
        }
        return $instance;
    }

    /**
     * Return converted Sie into Sie4Dto
     *
     * @param null|Sie|SieEntry $sie
     * @return Sie4Dto
     * @throws Exception
     * @throws InvalidArgumentException;
     * @throws RuntimeException;
     */
    public function getSie4EDto( null|Sie|SieEntry $sie = null ) : Sie4Dto
    {
        static $FMT1 = 'Sie saknas';
        if( $sie !== null ) {
            $this->setSie( $sie );
        }
        if( ! $this->isSieSet()) {
            throw new InvalidArgumentException( $FMT1, 4201 );
        }
        $this->sie4EDto = new Sie4Dto( self::processIdData( true, $this->sie->getFileInfo()));
        $this->processAccountData();
        $this->processDimData();
        $this->processVerData();
        return $this->sie4EDto;
    }

    /**
     * Updates AccountData, pSaldoData and pBudgetData
     *
     * @return void
     */
    private function processAccountData() : void
    {
        $accounts = $this->sie->getAccounts();
        if( $accounts === null ) {
            return;
        }
        foreach((array) $accounts->getAccount() as $accountTypeEntry ) {
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
        if( null !== $kvantitet ) {
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
        if( null !== $kvantitet ) {
            $period->setKvantitet( $kvantitet );
        }
        $objectReferences = $budgetType->getObjectReference();
        $objectReference  = reset( $objectReferences );
        if( ! empty( $objectReference )) {
            $period->setDimensionNr( $objectReference->getDimId());
            $period->setObjektNr( $objectReference->getObjectId());
        }
        return $period;
    }

    /**
     * Updates DimData and DimObjektData
     *
     * @return void
     */
    private function processDimData() : void
    {
        $dimensions = $this->sie->getDimensions();
        if( $dimensions === null ) {
            return;
        }
        foreach((array) $dimensions->getDimension() as $dimensionTypeEntry ) {
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
    private function processVerData() : void
    {
        $journals = $this->sie->getJournal();
        if( empty( $journals )) {
            return; // ??
        }
        foreach( $journals as $journalTypeEntry ) {
            $serie = $journalTypeEntry->getId();
            foreach( $journalTypeEntry->getJournalEntry() as $JournalEntryType ) {
                $this->sie4EDto->addVerDto( self::getVerDto( $JournalEntryType, $serie ));
            } // end foreach
        } // end foreach
    }

    /**
     * @param LedgerEntryType $LedgerEntryType
     * @param string          $verDatumYmd
     * @return TransDto
     */
    protected static function getTransDto(
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
        if( $transDate !== null &&
            ( $verDatumYmd !== $transDate->format( self::SIE4YYYYMMDD ))) {
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
        return isset( $this->sie );
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
