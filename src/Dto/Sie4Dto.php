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
namespace Kigkonsult\Sie4Sdk\Dto;

use Exception;
use InvalidArgumentException;

use Kigkonsult\Sie4Sdk\Lists\AccountDtoList;
use Kigkonsult\Sie4Sdk\Lists\BalansDtoList;
use Kigkonsult\Sie4Sdk\Lists\BalansObjektDtoList;
use Kigkonsult\Sie4Sdk\Lists\DimDtoList;
use Kigkonsult\Sie4Sdk\Lists\DimObjektDtoList;
use Kigkonsult\Sie4Sdk\Lists\PeriodDtoList;
use Kigkonsult\Sie4Sdk\Lists\SruDtoList;
use Kigkonsult\Sie4Sdk\Lists\UnderDimDtoList;
use Kigkonsult\Sie4Sdk\Lists\VerDtoList;
use function is_string;

/**
 * Class Sie5EntryLoader
 *
 * Inherit timestamp, guid, fnrId and orgnr properties from BaseId
 * to uniquely identify instance
 *
 * @since 1.8.3 2023-09-20
 */
class Sie4Dto extends BaseId
{
    /**
     * FLAGGA default 0
     *
     * @var int
     */
    private int $flagga = 0;

    /**
     * KSUMMA, kontrollsumma, set if > 0
     *
     * @var int
     */
    private int $ksumma = 0;

    /**
     * @var IdDto
     */
    private IdDto $idDto;

    /**
     * @var AccountDtoList   #KONTO/#KTYP/#ENHET
     */
    private AccountDtoList $accountDtos;

    /**
     * @var SruDtoList   #SRU
     */
    private SruDtoList $sruDtos;

    /**
     * @var DimDtoList  #DIM
     */
    private DimDtoList $dimDtos;

    /**
     * @var UnderDimDtoList  #UNDERDIM
     */
    private UnderDimDtoList $underDimDtos;

    /**
     * @var DimObjektDtoList   #OBJECT
     */
    private DimObjektDtoList $dimObjektDtos;

    /**
     * @var BalansDtoList  Ingående balans  #IB
     */
    private BalansDtoList $ibDtos;

    /**
     * @var BalansDtoList  Utgående balans #UB
     */
    private BalansDtoList $ubDtos;

    /**
     * @var BalansObjektDtoList  Ingående balans för objekt  #OIB
     */
    private BalansObjektDtoList $oibDtos;

    /**
     * @var BalansObjektDtoList  Utgående balans för objekt   #OUB
     */
    private BalansObjektDtoList $oubDtos;

    /**
     * @var BalansDtoList   Saldo för resultatkonto  #RES
     */
    private BalansDtoList $saldoDtos;

    /**
     * @var PeriodDtoList  Periodsaldopost  #PSALDO
     */
    private PeriodDtoList $pSaldoDtos;

    /**
     * @var PeriodDtoList  Periodbudgetpost  #PBUDGET
     */
    private PeriodDtoList $pBudgetDtos;

    /**
     * @var VerDtoList   verifikationer med kontringsrader  #VER/#TRANS
     */
    private VerDtoList $verDtos;

    /**
     * Class constructor
     *
     * @param IdDto $idDto
     * @throws InvalidArgumentException
     */
    public function __construct( IdDto $idDto )
    {
        try {
            parent::__construct();
            $this->accountDtos  = new AccountDtoList();
            $this->verDtos      = new VerDtoList();
            $this->setIdDto( $idDto );
        }
        catch( Exception $e ) {
            throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
        }
        $this->sruDtos       = new SruDtoList();
        $this->dimDtos       = new DimDtoList();
        $this->underDimDtos  = new UnderDimDtoList();
        $this->dimObjektDtos = new DimObjektDtoList();
        $this->ibDtos        = new BalansDtoList();
        $this->ubDtos        = new BalansDtoList();
        $this->oibDtos       = new BalansObjektDtoList();
        $this->oubDtos       = new BalansObjektDtoList();
        $this->saldoDtos     = new BalansDtoList();
        $this->pSaldoDtos    = new PeriodDtoList();
        $this->pBudgetDtos   = new PeriodDtoList();
    }

    /**
     * Class factory method, set idDto from arg or idDto::Fnamn(/fnrId/orgnr)
     *
     * @param string|IdDto $idDto (fnamn)
     * @param null|string $fnrId
     * @param null|string $orgnr
     * @return self
     * @throws Exception
     */
    public static function factory(
        string|IdDto $idDto,
        ? string $fnrId = null,
        ? string $orgnr = null
    ) : self
    {
        if( is_string( $idDto )) {
            $idDto = IdDto::factory( $idDto, $fnrId, $orgnr );
        }
        return new self( $idDto );
    }

    /**
     * @override
     * @param string $correlationId
     * @return $this
     */
    public function setCorrelationId( string $correlationId ) : static
    {
        parent::setCorrelationId( $correlationId );
        if( isset( $this->verDtos )) {
            $this->verDtos->setCorrIdDtoData( $correlationId );
        }
        return $this;
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
    public function getIdDto() : IdDto
    {
        return $this->idDto;
    }

    /**
     * Set IdDto
     *
     * Set this fnrId/orgnr/multiple into IdDto otherwise update this
     * Update VerDtos (incl TransDtos)
     *
     * @param IdDto $idDto
     * @return self
     */
    public function setIdDto( IdDto $idDto ) : self
    {
        $this->idDto = $idDto;
        $this->verDtos->setCorrIdDtoData( null, $idDto );
        return $this;
    }

    /**
     * Return int count accountDtos
     *
     * @return int
     */
    public function countAccountDtos() : int
    {
        return $this->accountDtos->count();
    }

    /**
     * Return AccountDtoList
     *
     * @return AccountDtoList
     */
    public function getAccountDtos() : AccountDtoList
    {
        return $this->accountDtos;
    }

    /**
     * Add single AccountDto using kontoNr/namn, typ/enhet opt
     *
     * @param int|string $kontoNr
     * @param string $kontoNamn
     * @param null|string $kontoTyp
     * @param null|string $enhet
     * @return self
     * @since 1.8.3 2023-09-20
     */
    public function addAccount(
        int | string $kontoNr,
        string       $kontoNamn,
        ? string     $kontoTyp = null,
        ? string     $enhet = null
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
        $this->accountDtos->append( $accountData, $accountData->getKontoNr());
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
        $this->accountDtos->init();
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
        return $this->sruDtos->count();
    }

    /**
     * Return array SruDto
     *
     * @return SruDtoList
     */
    public function getSruDtos() : SruDtoList
    {
        return $this->sruDtos;
    }

    /**
     * Add single SruDto using kontoNr and sruKod
     *
     * @param int|string $kontoNr
     * @param int|string $sruKod
     * @return self
     */
    public function addSru( int | string $kontoNr, int | string $sruKod ) : self
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
        $this->sruDtos->append( $sruDto, $sruDto->getKontoNr());
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
        $this->sruDtos->init();
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
        return $this->dimDtos->count();
    }

    /**
     * Return array DimDto
     *
     * @return DimDtoList
     */
    public function getDimDtos() : DimDtoList
    {
        return $this->dimDtos;
    }

    /**
     * Add single DimObjektDto using dimensionNr and dimensionsNamn
     *
     * @param int|string $dimensionsNr
     * @param string $dimensionsNamn
     * @return self
     */
    public function addDim( int | string $dimensionsNr, string $dimensionsNamn ) : self
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
        $this->dimDtos->append( $dimDto, $dimDto->getDimensionNr());
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
        $this->dimDtos->init();
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
        return $this->underDimDtos->count();
    }

    /**
     * Return UnderDimDtoList
     *
     * @return UnderDimDtoList
     */
    public function getUnderDimDtos() : UnderDimDtoList
    {
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
    public function addUnderDim(
        int | string $dimensionsNr,
        string $dimensionsNamn,
        int | string $superDimNr
    ) : self
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
        $superDimNr = $underDimDto->getSuperDimNr();
        $this->underDimDtos->append(
            $underDimDto,
            UnderDimDtoList::getPrimaryKey( $underDimDto->getDimensionNr(), $superDimNr ),
            $superDimNr
        );
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
        $this->underDimDtos->init();
        foreach( $underDimDtos as $underDimDto ) {
            $this->addUnderDimDto( $underDimDto );
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
        return $this->dimObjektDtos->count();
    }

    /**
     * Return DimObjekttDtoList
     *
     * @return DimObjektDtoList
     */
    public function getDimObjektDtos() : DimObjektDtoList
    {
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
    public function addDimObjekt( int | string $dimensionsNr, string $objektNr, string $objektNamn ) : self
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
        $dimensionNr = $dimObjektDto->getDimensionNr();
        $objektNr    = $dimObjektDto->getObjektNr();
        $this->dimObjektDtos->append(
            $dimObjektDto,               // list element
            DimObjektDtoList::getPrimaryKey( $dimensionNr, $objektNr ), // primary key
            [ $dimensionNr, $objektNr ]  // tags
        );
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
        $this->dimObjektDtos->init();
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
        return $this->ibDtos->count();
    }

    /**
     * Return BalansDto for IB for (arsnr === 0 and) kontonr
     *
     * @param string $kontoNr
     * @return false|BalansDto
     */
    public function getIbForKontoNr( string $kontoNr ) : BalansDto | bool
    {
        foreach( $this->ibDtos as $balansDto ) {
            if(( 0 === $balansDto->getArsnr()) &&
                ( $kontoNr === $balansDto->getKontoNr())) {
                return $balansDto;
            }
        }
        return false;
    }

    /**
     * Return array ibDto
     *
     * @return BalansDtoList
     */
    public function getIbDtos() : BalansDtoList
    {
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
        $this->ibDtos->append( $ibDto, $ibDto->getKontoNr());
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
        $this->ibDtos->init();
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
        return $this->ubDtos->count();
    }

    /**
     * Return BalansDto for UB with (arsnr === 0 and) kontonr
     *
     * @param string $kontoNr
     * @return false|BalansDto
     */
    public function getUbForKontoNr( string $kontoNr ) : BalansDto | bool
    {
        foreach( $this->ubDtos as $balansDto ) {
            if(( 0 === $balansDto->getArsnr()) &&
                ( $kontoNr === $balansDto->getKontoNr())) {
                return $balansDto;
            }
        }
        return false;
    }

    /**
     * Return array ibDto
     *
     * @return BalansDtolist
     */
    public function getUbDtos() : BalansDtolist
    {
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
        $this->ubDtos->append( $ubDto, $ubDto->getKontoNr());
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
        $this->ubDtos->init();
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
        return $this->oibDtos->count();
    }

    /**
     * Return array oibDto
     *
     * @return BalansObjektDtoList
     */
    public function getOibDtos() : BalansObjektDtoList
    {
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
        $arsNr       = $oibDto->getArsnr();
        $kontoNr     = $oibDto->getKontoNr();
        $dimensionNr = $oibDto->getDimensionNr();
        $objektNr    = $oibDto->getObjektNr();
        $this->oibDtos->append(
            $oibDto,
            BalansObjektDtoList::getPrimaryKey( $arsNr, $kontoNr, $dimensionNr, $objektNr ), // pKey
            [ $kontoNr, $dimensionNr, $objektNr ] // tags
        );
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
        $this->oibDtos->init();
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
        return $this->oubDtos->count();
    }

    /**
     * Return array oibDto
     *
     * @return BalansObjektDtoList
     */
    public function getOubDtos() : BalansObjektDtoList
    {
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
        $arsNr       = $oubDto->getArsnr();
        $kontoNr     = $oubDto->getKontoNr();
        $dimensionNr = $oubDto->getDimensionNr();
        $objektNr    = $oubDto->getObjektNr();
        $this->oubDtos->append(
            $oubDto,
            BalansObjektDtoList::getPrimaryKey( $arsNr, $kontoNr, $dimensionNr, $objektNr ), // pKey
            [ $kontoNr, $dimensionNr, $objektNr ] // tags
        );
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
        $this->oubDtos->init();
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
        return $this->saldoDtos->count();
    }

    /**
     * Return array saldoDto
     *
     * @return BalansDtoList
     */
    public function getSaldoDtos() : BalansDtoList
    {
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
        $this->saldoDtos->append( $saldoDto, $saldoDto->getKontoNr());
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
        $this->saldoDtos->init();
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
        return $this->pSaldoDtos->count();
    }

    /**
     * Return PeriodDto for pSaldo with (arsnr === 0 and) kontonr
     *
     * @param string $kontoNr
     * @return false|PeriodDto
     */
    public function getPsaldoForKontoNr( string $kontoNr ) : bool | PeriodDto
    {
        foreach( $this->pSaldoDtos->yield() as $periodDto ) {
            if(( 0 === $periodDto->getArsnr()) &&
                ( $kontoNr === $periodDto->getKontoNr())) {
                return $periodDto;
            }
        }
        return false;
    }

    /**
     * Return array pSaldoDto
     *
     * @return PeriodDtoList
     */
    public function getPsaldoDtos() : PeriodDtoList
    {
        return $this->pSaldoDtos;
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
            if(( 0 === $periodDto->getArsnr()) &&
                ( $kontoNr === $periodDto->getKontoNr())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add single pSaldoDto
     *
     * @param PeriodDto $pSaldoDto
     * @return self
     */
    public function addPsaldoDto( PeriodDto $pSaldoDto ) : self
    {
        $this->pSaldoDtos->append(
            $pSaldoDto,
            PeriodDtoList::getPrimaryKey(
                $pSaldoDto->getArsNr(),
                $pSaldoDto->getKontoNr(),
                $pSaldoDto->getDimensionNr(),
                $pSaldoDto->getObjektNr(),
                $pSaldoDto->getPeriod()
            )
        );
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
        $this->pSaldoDtos->init();
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
        return $this->pBudgetDtos->count();
    }

    /**
     * Return PeriodDto for pBudget for (arsnr === 0 and) kontonr
     *
     * @param string $kontoNr
     * @return false|PeriodDto
     */
    public function getPbudgetForKontoNr( string $kontoNr ) : bool | PeriodDto
    {
        foreach( $this->pBudgetDtos->yield() as $periodDto ) {
            if(( 0 === $periodDto->getArsnr()) &&
                ( $kontoNr === $periodDto->getKontoNr())) {
                return $periodDto;
            }
        }
        return false;
    }

    /**
     * Return array pBudgetDto
     *
     * @return PeriodDtoList
     */
    public function getPbudgetDtos() : PeriodDtoList
    {
        return $this->pBudgetDtos;
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
            if(( 0 === $periodDto->getArsnr()) &&
                ( $kontoNr === $periodDto->getKontoNr())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add single pBudgetDto
     *
     * @param PeriodDto $pBudgetDto
     * @return self
     */
    public function addPbudgetDto( PeriodDto $pBudgetDto ) : self
    {
        $this->pBudgetDtos->append(
            $pBudgetDto,
            PeriodDtoList::getPrimaryKey(
                $pBudgetDto->getArsNr(),
                $pBudgetDto->getKontoNr(),
                $pBudgetDto->getDimensionNr(),
                $pBudgetDto->getObjektNr(),
                $pBudgetDto->getPeriod()
            )
        );
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
        $this->pBudgetDtos->init();
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
        return $this->verDtos->count();
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
     * @return VerDtoList
     */
    public function getVerDtos() : VerDtoList
    {
        return $this->verDtos;
    }

    /**
     * Add single VerDto, fnrId and orgnr(+multiple) are added from idDto
     *
     * @param VerDto $verDto
     *
     * @return self
     * @since 1.8.4 20230925
     */
    public function addVerDto( VerDto $verDto ) : self
    {
        $verDto->setCorrIdDtoData( $this->getCorrelationId(), $this->idDto );
        $this->verDtos->append( $verDto, VerDtoList::getPrimaryKey( $verDto ));
        return $this;
    }

    /**
     * Set array VerDto[], will (re-)init verDtoList
     *
     * @param VerDto[] $verDtos
     * @return self
     */
    public function setVerDtos( array $verDtos ) : self
    {
        $this->verDtos->init();
        foreach( $verDtos as $verDto ) {
            $this->addVerDto( $verDto );
        }
        return $this;
    }
    /**
     * Set fnrId in idDto and each verDto
     *
     * @param string $fnrId
     * @return self
     */
    public function setFnrId( string $fnrId ) : self
    {
        $this->fnrId = $fnrId;
        $this->idDto->setFnrId( $fnrId );
        $this->verDtos->setCorrIdDtoData( null, $this->idDto );
        return $this;
    }

    /**
     * Set orgnr in idDto and each verDto
     *
     * @param string $orgnr
     * @return self
     */
    public function setOrgnr( string $orgnr ) : self
    {
        $this->orgnr = $orgnr;
        $this->idDto->setOrgnr( $orgnr );
        $this->verDtos->setCorrIdDtoData( null, $this->idDto );
        return $this;
    }

    /**
     * Set orgnr multiple in idDto and each verDto
     *
     * @param int $multiple
     * @return self
     */
    public function setMultiple( int $multiple ) : self
    {
        $this->multiple = $multiple;
        $this->idDto->setMultiple( $multiple );
        $this->verDtos->setCorrIdDtoData( null, $this->idDto );
        return $this;
    }
}
