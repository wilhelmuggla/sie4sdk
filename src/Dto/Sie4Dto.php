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
namespace Kigkonsult\Sie4Sdk\Dto;

use InvalidArgumentException;

use function usort;

/**
 * Class Sie5EntryLoader

 * Inherit unique timestamp and guid properties from parent
 */
class Sie4Dto extends BaseId
{
    /**
     * FLAGGA default 0
     *
     * @var int
     */
    private $flagga = 0;

    /**
     * KSUMMA, kontrollsumma, set if > 0
     *
     * @var int
     */
    private $ksumma = 0;

    /**
     * @var IdDto
     */
    private $idDto = null;

    /**
     * @var AccountDto[]  #KONTO/#KTYP/#ENHET
     */
    private $accountDtos = [];

    /**
     * @var SruDto[]   #SRU
     */
    private $sruDtos = [];

    /**
     * @var DimDto[]  #DIM
     */
    private $dimDtos = [];

    /**
     * @var UnderDimDto[]  #UNDERDIM
     */
    private $underDimDtos = [];

    /**
     * @var DimObjektDto[]   #OBJECT
     */
    private $dimObjektDtos = [];

    /**
     * @var BalansDto[]  Ingående balans  #IB
     */
    private $ibDtos = [];

    /**
     * @var BalansDto[]  Utgående balans #UB
     */
    private $ubDtos = [];

    /**
     * @var BalansObjektDto[]  Ingående balans för objekt  #OIB
     */
    private $oibDtos = [];

    /**
     * @var BalansObjektDto[]  Utgående balans för objekt   #OUB
     */
    private $oubDtos = [];

    /**
     * @var BalansDto[]   Saldo för resultatkonto  #RES
     */
    private $saldoDtos = [];

    /**
     * @var PeriodDto[]  Periodsaldopost  #PSALDO
     */
    private $pSaldoDtos = [];

    /**
     * @var PeriodDto[]  Periodbudgetpost  #PBUDGET
     */
    private $pBudgetDtos = [];

    /**
     * @var VerDto[]   verifikationer med kontringsrader  #VER/#TRANS
     */
    private $verDtos = [];

    /**
     * Class factory method, set idDto
     *
     * @param IdDto $idDto
     * @return self
     */
    public static function factory( IdDto $idDto ) : self
    {
        $instance = new self();
        $instance->setIdDto( $idDto );
        return $instance;
    }

    /**
     * @return int
     */
    public function getFlagga() : int
    {
        return $this->flagga;
    }

    /**
     * @param int $flagga
     * @return self
     */
    public function setFlagga( int $flagga ) : self
    {
        $this->flagga = $flagga;
        return $this;
    }

    /**
     * Return int
     *
     * @return int
     */
    public function getKsumma() : int
    {
        return $this->ksumma;
    }

    /**
     * Return bool true
     *
     * @return bool
     */
    public function isKsummaSet() : bool
    {
        return ( 0 < $this->ksumma );
    }

    /**
     * Set KSUMMA, (int) 0 if NOT
     *
     * @param int $ksumma
     * @return self
     * @throws InvalidArgumentException
     */
    public function setKsumma( int $ksumma ) : self
    {
        $this->ksumma = $ksumma;
        return $this;
    }

    /**
     * Return IdDto
     *
     * @return IdDto
     */
    public function getIdDto()
    {
        return $this->idDto;
    }

    /**
     * Return bool true if IdDto is set
     *
     * @return bool
     */
    public function isIdDtoSet() : bool
    {
        return ( null !== $this->idDto );
    }

    /**
     * Set IdDto
     *
     * @param IdDto $idDto
     * @return self
     */
    public function setIdDto( IdDto $idDto ) : self
    {
        $this->idDto = $idDto;
        return $this;
    }

    /**
     * Return int count AccountDtos
     *
     * @return int
     */
    public function countAccountDtos() : int
    {
        return count( $this->accountDtos );
    }

    /**
     * Return array AccountDto
     *
     * @return AccountDto[]
     */
    public function getAccountDtos() : array
    {
        usort( $this->accountDtos, AccountDto::$SORTER );
        return $this->accountDtos;
    }

    /**
     * Add single AccountDto using kontoNr/namn/typ, enhet opt
     *
     * @param int|string $kontoNr
     * @param string $kontoNamn
     * @param string $kontoTyp
     * @param null|string $enhet
     * @return self
     */
    public function addAccount(
        $kontoNr,
        string $kontoNamn,
        string $kontoTyp,
        $enhet = null
    ) : self
    {
        return $this->addAccountDto(
            AccountDto::factory(
                $kontoNr,
                $kontoNamn,
                $kontoTyp,
                $enhet
           )
        );
    }

    /**
     * Add single AccountDto
     *
     * @param AccountDto $accountData
     * @return self
     */
    public function addAccountDto( AccountDto $accountData ) : self
    {
        $this->accountDtos[] = $accountData;
        return $this;
    }

    /**
     * Set array AccountDto[]
     *
     * @param AccountDto[] $accountDtos
     * @return self
     */
    public function setAccountDtos( array $accountDtos ) : self
    {
        $this->accountDtos = [];
        foreach( $accountDtos as $accountDto ) {
            $this->addAccountDto( $accountDto );
        }
        return $this;
    }

    /**
     * Return int count SruDtos
     *
     * @return int
     */
    public function countSruDtos() : int
    {
        return count( $this->sruDtos );
    }

    /**
     * Return array SruDto
     *
     * @return SruDto[]
     */
    public function getSruDtos() : array
    {
        usort( $this->sruDtos, SruDto::$SORTER );
        return $this->sruDtos;
    }

    /**
     * Add single SruDto using kontoNr and sruKod
     *
     * @param int|string $kontoNr
     * @param int|string $sruKod
     * @return self
     */
    public function addSru( $kontoNr, $sruKod ) : self
    {
        return $this->addSruDto( SruDto::factory( $kontoNr, $sruKod ));
    }

    /**
     * Add single SruDto
     *
     * @param SruDto $sruDto
     * @return self
     */
    public function addSruDto( SruDto $sruDto ) : self
    {
        $this->sruDtos[] = $sruDto;
        return $this;
    }

    /**
     * Set array SruDto
     *
     * @param SruDto[] $sruDtos
     * @return self
     */
    public function setSruDtos( array $sruDtos ) : self
    {
        $this->sruDtos = [];
        foreach( $sruDtos as $sruDto ) {
            $this->addSruDto( $sruDto );
        }
        return $this;
    }

    /**
     * Return int count DimDtos
     *
     * @return int
     */
    public function countDimDtos() : int
    {
        return count( $this->dimDtos );
    }

    /**
     * Return array DimDto
     *
     * @return DimDto[]
     */
    public function getDimDtos() : array
    {
        usort( $this->dimDtos, DimDto::$SORTER );
        return $this->dimDtos;
    }

    /**
     * Add single DimObjektDto using dimensionNr and dimensionsNamn
     *
     * @param int|string $dimensionsNr
     * @param string $dimensionsNamn
     * @return self
     */
    public function addDim( $dimensionsNr, string $dimensionsNamn ) : self
    {
        return $this->addDimDto(
            DimDto::factoryDim(
                $dimensionsNr,
                $dimensionsNamn
            )
        );
    }

    /**
     * Add single DimDto
     *
     * @param DimDto $dimDto
     * @return self
     */
    public function addDimDto( DimDto $dimDto ) : self
    {
        $this->dimDtos[] = $dimDto;
        return $this;
    }

    /**
     * Set array DimDto
     *
     * @param DimDto[] $dimDtos
     * @return self
     */
    public function setDimDtos( array $dimDtos ) : self
    {
        $this->dimDtos = [];
        foreach( $dimDtos as $dimDto ) {
            $this->addDimDto( $dimDto );
        }
        return $this;
    }

    /**
     * Return int count UnderDimDtos
     *
     * @return int
     */
    public function countUnderDimDtos() : int
    {
        return count( $this->underDimDtos );
    }

    /**
     * Return array UnderDimDto
     *
     * @return UnderDimDto[]
     */
    public function getUnderDimDtos() : array
    {
        usort( $this->underDimDtos, UnderDimDto::$SORTER );
        return $this->underDimDtos;
    }

    /**
     * Add single UnderDimDto using (under-)dimensionNr, dimensionsNamn, superDimNr
     *
     * @param int|string $dimensionsNr
     * @param string     $dimensionsNamn
     * @param int|string $superDimNr
     * @return self
     */
    public function addUnderDim( $dimensionsNr, string $dimensionsNamn, $superDimNr ) : self
    {
        return $this->addUnderDimDto(
            UnderDimDto::factoryUnderDim(
                $dimensionsNr,
                $dimensionsNamn,
                $superDimNr
            )
        );
    }

    /**
     * Add single UnderDimDto
     *
     * @param UnderDimDto $underDimDto
     * @return self
     */
    public function addUnderDimDto( UnderDimDto $underDimDto ) : self
    {
        $this->underDimDtos[] = $underDimDto;
        return $this;
    }

    /**
     * Set array UnderDimDto
     *
     * @param UnderDimDto[] $underDimDtos
     * @return self
     */
    public function setUnderDimDtos( array $underDimDtos ) : self
    {
        $this->underDimDtos = [];
        foreach( $underDimDtos as $underdimDto ) {
            $this->addUnderDimDto( $underdimDto );
        }
        return $this;
    }

    /**
     * Return int count DimObjektDtos
     *
     * @return int
     */
    public function countDimObjektDtos() : int
    {
        return count( $this->dimObjektDtos );
    }

    /**
     * Return array DimObjekttDto
     *
     * @return DimObjektDto[]
     */
    public function getDimObjektDtos() : array
    {
        usort( $this->dimObjektDtos, DimObjektDto::$SORTER );
        return $this->dimObjektDtos;
    }

    /**
     * Add single DimObjektDto using dimensionNr, objektNr and $objektNamn
     *
     * @param int|string $dimensionsNr
     * @param string $objektNr
     * @param string $objektNamn
     * @return self
     */
    public function addDimObjekt( $dimensionsNr, string $objektNr, string $objektNamn ) : self
    {
        return $this->addDimObjektDto(
            DimObjektDto::factoryDimObject(
                $dimensionsNr,
                $objektNr,
                $objektNamn
            )
        );
    }

    /**
     * Add single DimObjektDto
     *
     * @param DimObjektDto $dimObjektDto
     * @return self
     */
    public function addDimObjektDto( DimObjektDto $dimObjektDto ) : self
    {
        $this->dimObjektDtos[] = $dimObjektDto;
        return $this;
    }

    /**
     * Set array DimObjektDto[]
     *
     * @param DimObjektDto[] $dimObjektDtos
     * @return self
     */
    public function setDimObjektDtos( array $dimObjektDtos ) : self
    {
        $this->dimObjektDtos = [];
        foreach( $dimObjektDtos as $dimObjektDto ) {
            $this->addDimObjektDto( $dimObjektDto );
        }
        return $this;
    }

    /**
     * Return int count ibDtos
     *
     * @return int
     */
    public function countIbDtos() : int
    {
        return count( $this->ibDtos );
    }

    /**
     * Return BalansDto for IB with arsnr == 0 and kontonr
     *
     * @param string $kontoNr
     * @return false|BalansDto
     */
    public function getIbForKontoNr( string $kontoNr )
    {
        foreach( $this->ibDtos as $balansDto ) {
            if(( 0 == $balansDto->getArsnr()) &&
                ( $kontoNr == $balansDto->getKontoNr())) {
                return $balansDto;
            }
        }
        return false;
    }

    /**
     * Return bool true if IB with arsnr == 0 and kontonr is found
     *
     * @param string $kontoNr
     * @return bool
     */
    public function isIbKontoNrSet( string $kontoNr ) : bool
    {
        foreach( $this->ibDtos as $balansDto ) {
            if(( 0 == $balansDto->getArsnr()) &&
                ( $kontoNr == $balansDto->getKontoNr())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return array ibDto
     *
     * @return BalansDto[]
     */
    public function getIbDtos() : array
    {
        usort( $this->ibDtos, BalansDto::$SORTER );
        return $this->ibDtos;
    }

    /**
     * Add single ibDto
     *
     * @param BalansDto $ibDto
     * @return self
     */
    public function addIbDto( BalansDto $ibDto ) : self
    {
        $this->ibDtos[] = $ibDto;
        return $this;
    }

    /**
     * Set array ibDto
     *
     * @param BalansDto[] $ibDtos
     * @return self
     */
    public function setIbDtos( array $ibDtos ) : self
    {
        $this->ibDtos = [];
        foreach( $ibDtos as $ibDto ) {
            $this->addIbDto( $ibDto );
        }
        return $this;
    }

    /**
     * Return int count ubDtos
     *
     * @return int
     */
    public function countUbDtos() : int
    {
        return count( $this->ubDtos );
    }

    /**
     * Return BalansDto for UB with arsnr == 0 and kontonr
     *
     * @param string $kontoNr
     * @return false|BalansDto
     */
    public function getUbForKontoNr( string $kontoNr )
    {
        foreach( $this->ubDtos as $balansDto ) {
            if(( 0 == $balansDto->getArsnr()) &&
                ( $kontoNr == $balansDto->getKontoNr())) {
                return $balansDto;
            }
        }
        return false;
    }

    /**
     * Return bool true if UB with arsnr == 0 and kontonr is found
     *
     * @param string $kontoNr
     * @return bool
     */
    public function isUbKontoNrSet( string $kontoNr ) : bool
    {
        foreach( $this->ubDtos as $balansDto ) {
            if(( 0 == $balansDto->getArsnr()) &&
                ( $kontoNr == $balansDto->getKontoNr())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return array ibDto
     *
     * @return BalansDto[]
     */
    public function getUbDtos() : array
    {
        usort( $this->ubDtos, BalansDto::$SORTER );
        return $this->ubDtos;
    }

    /**
     * Add single ubDto
     *
     * @param BalansDto $ubDto
     * @return self
     */
    public function addUbDto( BalansDto $ubDto ) : self
    {
        $this->ubDtos[] = $ubDto;
        return $this;
    }

    /**
     * Set array ubDto
     *
     * @param BalansDto[] $ubDtos
     * @return self
     */
    public function setUbDtos( array $ubDtos ) : self
    {
        $this->ubDtos = [];
        foreach( $ubDtos as $ubDto ) {
            $this->addUbDto( $ubDto );
        }
        return $this;
    }

    /**
     * Return int count oibDtos
     *
     * @return int
     */
    public function countOibDtos() : int
    {
        return count( $this->oibDtos );
    }

    /**
     * Return array oibDto
     *
     * @return BalansObjektDto[]
     */
    public function getOibDtos() : array
    {
        usort( $this->oibDtos, BalansObjektDto::$SORTER );
        return $this->oibDtos;
    }

    /**
     * Add single oibDto
     *
     * @param BalansObjektDto $oibDto
     * @return self
     */
    public function addOibDto( BalansObjektDto $oibDto ) : self
    {
        $this->oibDtos[] = $oibDto;
        return $this;
    }

    /**
     * Set array oibDto
     *
     * @param BalansObjektDto[] $oibDtos
     * @return self
     */
    public function setOibDtos( array $oibDtos ) : self
    {
        $this->oibDtos = [];
        foreach( $oibDtos as $oibDto ) {
            $this->addOibDto( $oibDto );
        }
        return $this;
    }

    /**
     * Return int count oubDtos
     *
     * @return int
     */
    public function countOubDtos() : int
    {
        return count( $this->oubDtos );
    }

    /**
     * Return array oibDto
     *
     * @return BalansObjektDto[]
     */
    public function getOubDtos() : array
    {
        usort( $this->oubDtos, BalansObjektDto::$SORTER );
        return $this->oubDtos;
    }

    /**
     * Add single oubDto
     *
     * @param BalansObjektDto $oubDto
     * @return self
     */
    public function addOubDto( BalansObjektDto $oubDto ) : self
    {
        $this->oubDtos[] = $oubDto;
        return $this;
    }

    /**
     * Set array oubDto
     *
     * @param BalansObjektDto[] $oubDtos
     * @return self
     */
    public function setOubDtos( array $oubDtos ) : self
    {
        $this->oubDtos = [];
        foreach( $oubDtos as $oubDto ) {
            $this->addOubDto( $oubDto );
        }
        return $this;
    }

    /**
     * Return int count saldoDtos
     *
     * @return int
     */
    public function countSaldoDtos() : int
    {
        return count( $this->saldoDtos );
    }

    /**
     * Return array saldoDto
     *
     * @return BalansDto[]
     */
    public function getSaldoDtos() : array
    {
        usort( $this->saldoDtos, BalansDto::$SORTER );
        return $this->saldoDtos;
    }

    /**
     * Add single saldoDto
     *
     * @param BalansDto $saldoDto
     * @return self
     */
    public function addSaldoDto( BalansDto $saldoDto ) : self
    {
        $this->saldoDtos[] = $saldoDto;
        return $this;
    }

    /**
     * Set array saldoDto
     *
     * @param BalansDto[] $saldoDtos
     * @return self
     */
    public function setSaldoDtos( array $saldoDtos ) : self
    {
        $this->saldoDtos = [];
        foreach( $saldoDtos as $saldoDto ) {
            $this->addSaldoDto( $saldoDto );
        }
        return $this;
    }

    /**
     * Return int count pSaldoDtos
     *
     * @return int
     */
    public function countPsaldoDtos() : int
    {
        return count( $this->pSaldoDtos );
    }

    /**
     * Return PeriodDto for pSaldo with arsnr == 0 and kontonr
     *
     * @param string $kontoNr
     * @return false|BalansDto
     */
    public function getPsaldoForKontoNr( string $kontoNr )
    {
        foreach( $this->pSaldoDtos as $periodDto ) {
            if(( 0 == $periodDto->getArsnr()) &&
                ( $kontoNr == $periodDto->getKontoNr())) {
                return $periodDto;
            }
        }
        return false;
    }

    /**
     * Return bool true if pSaldo with arsnr == 0 and kontonr is found
     *
     * @param string $kontoNr
     * @return bool
     */
    public function isPsaldoKontoNrSet( string $kontoNr ) : bool
    {
        foreach( $this->pSaldoDtos as $periodDto ) {
            if(( 0 == $periodDto->getArsnr()) &&
                ( $kontoNr == $periodDto->getKontoNr())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return array pSaldoDto
     *
     * @return PeriodDto[]
     */
    public function getPsaldoDtos() : array
    {
        usort( $this->pSaldoDtos, PeriodDto::$SORTER );
        return $this->pSaldoDtos;
    }

    /**
     * Add single pSaldoDto
     *
     * @param PeriodDto $pSaldoDto
     * @return self
     */
    public function addPsaldoDto( PeriodDto $pSaldoDto ) : self
    {
        $this->pSaldoDtos[] = $pSaldoDto;
        return $this;
    }

    /**
     * Set array pSaldoDto
     *
     * @param PeriodDto[] $pSaldoDtos
     * @return self
     */
    public function setPsaldoDtos( array $pSaldoDtos ) : self
    {
        $this->pSaldoDtos = [];
        foreach( $pSaldoDtos as $pSaldoDto ) {
            $this->addPsaldoDto( $pSaldoDto );
        }
        return $this;
    }

    /**
     * Return int count pBudgetDtos
     *
     * @return int
     */
    public function countPbudgetDtos() : int
    {
        return count( $this->pBudgetDtos );
    }

    /**
     * Return PeriodDto for pBudget with arsnr == 0 and kontonr
     *
     * @param string $kontoNr
     * @return false|PeriodDto
     */
    public function getPbudgetForKontoNr( string $kontoNr )
    {
        foreach( $this->pBudgetDtos as $periodDto ) {
            if(( 0 == $periodDto->getArsnr()) &&
                ( $kontoNr == $periodDto->getKontoNr())) {
                return $periodDto;
            }
        }
        return false;
    }

    /**
     * Return bool true if pBudget with arsnr == 0 and kontonr is found
     *
     * @param string $kontoNr
     * @return bool
     */
    public function isPbudgetKontoNrSet( string $kontoNr ) : bool
    {
        foreach( $this->pBudgetDtos as $periodDto ) {
            if(( 0 == $periodDto->getArsnr()) &&
                ( $kontoNr == $periodDto->getKontoNr())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return array pBudgetDto
     *
     * @return PeriodDto[]
     */
    public function getPbudgetDtos() : array
    {
        usort( $this->pBudgetDtos, PeriodDto::$SORTER );
        return $this->pBudgetDtos;
    }

    /**
     * Add single pBudgetDto
     *
     * @param PeriodDto $pBudgetDto
     * @return self
     */
    public function addPbudgetDto( PeriodDto $pBudgetDto ) : self
    {
        $this->pBudgetDtos[] = $pBudgetDto;
        return $this;
    }

    /**
     * Set array pBudgetDto
     *
     * @param PeriodDto[] $pBudgetDtos
     * @return self
     */
    public function setPbudgetDtos( array $pBudgetDtos ) : self
    {
        $this->pBudgetDtos = [];
        foreach( $pBudgetDtos as $pBudgetDto ) {
            $this->addPbudgetDto( $pBudgetDto );
        }
        return $this;
    }

    /**
     * Return int count verDtos
     *
     * @return int
     */
    public function countVerDtos() : int
    {
        return count( $this->verDtos );
    }

    /**
     * Return int total count of transDtos in VerDtos
     *
     * @return int
     */
    public function countVerTransDtos() : int
    {
        $count = 0;
        foreach( $this->verDtos as $verDto ) {
            $count += $verDto->countTransDtos();
        }
        return $count;
    }

    /**
     * Return sorted array VerDto
     *
     * @return VerDto[]
     */
    public function getVerDtos() : array
    {
        usort( $this->verDtos, VerDto::$SORTER );
        return $this->verDtos;
    }

    /**
     * Add single VerDto
     *
     * @param VerDto $verDto
     *
     * @return self
     */
    public function addVerDto( VerDto $verDto ) : self
    {
        $this->verDtos[] = $verDto;
        return $this;
    }

    /**
     * Set array VerDto[]
     *
     * @param VerDto[] $verDtos
     * @return self
     */
    public function setVerDtos( array $verDtos ) : self
    {
        $this->verDtos = [];
        foreach( $verDtos as $verDto ) {
            $this->addVerDto( $verDto );
        }
        return $this;
    }
}
