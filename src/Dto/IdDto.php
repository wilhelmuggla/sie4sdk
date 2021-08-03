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

use DateTime;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\Traits\SignTrait;
use Kigkonsult\Sie4Sdk\Sie4Validator;

use function count;
use function usort;

/**
 * Class IdDto
 *
 * CompanyId and organization number required
 *
 * genDate  default 'now'
 * programnamn/version default set
 */
class IdDto implements DtoInterface
{
    /**
     * @var string
     */
    private $programnamn = null;

    /**
     * @var string
     */
    private $version = null;

    /**
     * @var DateTime
     */
    private $genDate = null;

    /**
     * sign used as genSign
     */
    use SignTrait;

    /**
     * @var int
     */
    private $sieTyp = 4;

    /**
     * @var null|string
     */
    private $prosa = null;

    /**
     * @var null|string
     */
    private $ftyp = null;

    /**
     * @var string
     */
    private $fnrId = null;

    /**
     * @var null|string
     */
    private $orgnr = null;

    /**
     * @var int  default 1
     */
    private $multiple = 1;

    /**
     * @var null|string
     */
    private $bkod = null;

    /**
     * @var AdressDto
     */
    private $adress = null;

    /**
     * @var null|string
     */
    private $fnamn = null;

    /**
     * @var RarDto[]
     */
    private $rarDtos = [];

    /**
     * @var null|int  ÅÅÅÅ
     */
    private $taxar = null;

    /**
     * @var null|DateTime
     */
    private $omfattn = null;

    /**
     * @var null|string
     */
    private $kptyp = null;

    /**
     * @var null|string
     */
    private $valutakod = null;

    /**
     * IdDto constructor
     */
    public function __construct()
    {
        $this->genDate     = new DateTime();
        $this->programnamn = self::PRODUCTNAME;
        $this->version     = self::PRODUCTVERSION;
    }


    /**
     * Class factory method, fnrId/orgnr
     *
     * @param string $fnrId
     * @param string $orgnr
     * @return static
     */
    public static function factory( string $fnrId, string $orgnr ) : self
    {
        $instance = new self();
        $instance->setFnrId( $fnrId );
        $instance->setOrgnr( $orgnr );
        return $instance;
    }

    /**
     * Return programnamn
     *
     * @return string
     */
    public function getProgramnamn() : string
    {
        return $this->programnamn;
    }

    /**
     * Set programnamn
     *
     * @param string $programnamn
     * @return static
     */
    public function setProgramnamn( string $programnamn ) : self
    {
        $this->programnamn = $programnamn;
        return $this;
    }

    /**
     * Return version
     *
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param string $version
     * @return static
     */
    public function setVersion( string $version ) : self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Return generation date
     *
     * @return DateTime
     */
    public function getGenDate() : DateTime
    {
        return $this->genDate;
    }

    /**
     * Set generation date
     *
     * @param DateTime $genDate
     * @return static
     */
    public function setGenDate( DateTime $genDate ) : self
    {
        $this->genDate = $genDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getSieTyp() : int
    {
        return $this->sieTyp;
    }

    /**
     * @param int|string $sieTyp
     * @return static
     * @throws InvalidArgumentException
     */
    public function setSieTyp( $sieTyp ) : self
    {
        Sie4Validator::assertIntegerish( self::SIETYP, $sieTyp );
        $this->sieTyp = (int) $sieTyp;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getProsa()
    {
        return $this->prosa;
    }

    /**
     * Return bool true if prosa is set
     *
     * @return bool
     */
    public function isProsaSet() : bool
    {
        return ( null !== $this->prosa );
    }

    /**
     * @param string $prosa
     * @return static
     */
    public function setProsa( string $prosa ) : self
    {
        $this->prosa = $prosa;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFtyp()
    {
        return $this->ftyp;
    }

    /**
     * Return bool true if ftyp is set
     *
     * @return bool
     */
    public function isFtypSet() : bool
    {
        return ( null !== $this->ftyp );
    }

    /**
     * @param string $ftyp
     * @return static
     */
    public function setFtyp( string $ftyp ) : self
    {
        $this->ftyp = $ftyp;
        return $this;
    }

    /**
     * Return fnrId
     *
     * @return null|string
     */
    public function getFnrId()
    {
        return $this->fnrId;
    }

    /**
     * Return bool true if fnr (company id) is set
     *
     * @return bool
     */
    public function isFnrIdSet() : bool
    {
        return ( null !== $this->fnrId );
    }

    /**
     * Set fnrId
     *
     * @param string $fnrId
     * @return static
     */
    public function setFnrId( string $fnrId ) : self
    {
        $this->fnrId = $fnrId;
        return $this;
    }

    /**
     * Return orgnr
     *
     * @return null|string
     */
    public function getOrgnr()
    {
        return $this->orgnr;
    }

    /**
     * Return bool true if orgnr is set
     *
     * @return bool
     */
    public function isOrgnrSet() : bool
    {
        return ( null !== $this->orgnr );
    }

    /**
     * Set orgnr
     *
     * @param string $orgnr
     * @return static
     */
    public function setOrgnr( string $orgnr ) : self
    {
        $this->orgnr = $orgnr;
        return $this;
    }

    /**
     * Return multiple (default 1)
     *
     * @return int
     */
    public function getMultiple() : int
    {
        return $this->multiple;
    }

    /**
     * Set multiple
     *
     * @param int $multiple
     * @return static
     */
    public function setMultiple( int $multiple ) : self
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getBkod()
    {
        return $this->bkod;
    }

    /**
     * Return bool true if bkod is set
     *
     * @return bool
     */
    public function isBkodSet() : bool
    {
        return ( null !== $this->bkod );
    }

    /**
     * @param string $bkod
     * @return static
     */
    public function setBkod( string $bkod ) : self
    {
        $this->bkod = $bkod;
        return $this;
    }

    /**
     * @return null|AdressDto
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * Return bool true if adress is set
     *
     * @return bool
     */
    public function isAdressSet() : bool
    {
        return ( null !== $this->adress );
    }

    /**
     * @param AdressDto $adress
     * @return static
     */
    public function setAdress( AdressDto $adress ) : self
    {
        $this->adress = $adress;
        return $this;
    }

    /**
     * Return fnamn
     *
     * @return null|string
     */
    public function getFnamn()
    {
        return $this->fnamn;
    }

    /**
     * Return bool true if fnamn is set
     *
     * @return bool
     */
    public function isFnamnSet() : bool
    {
        return ( null !== $this->fnamn );
    }

    /**
     * Set fnamn
     *
     * @param string $fnamn
     * @return static
     */
    public function setFnamn( string $fnamn ) : self
    {
        $this->fnamn = $fnamn;
        return $this;
    }

    /**
     * Return int count rarDtos
     *
     * @return int
     */
    public function countRarDtos() : int
    {
        return count( $this->rarDtos );
    }

    /**
     * @return array
     */
    public function getRarDtos() : array
    {
        static $SORTER = [ RarDto::class, 'sorter' ];
        usort( $this->rarDtos, $SORTER );
        return $this->rarDtos;
    }

    /**
     * @param RarDto $rar
     * @return static
     */
    public function addRarDto( RarDto $rar ) : self
    {
        $this->rarDtos[] = $rar;
        return $this;
    }

    /**
     * @param RarDto[] $rarDtos
     * @return static
     */
    public function setRarDtos( array $rarDtos ) : self
    {
        foreach( $rarDtos as $rarDto ) {
            $this->addRarDto( $rarDto );
        }
        return $this;
    }

    /**
     * @return null|int
     */
    public function getTaxar()
    {
        return $this->taxar;
    }

    /**
     * Return bool true if taxar is set
     *
     * @return bool
     */
    public function isTaxarSet() : bool
    {
        return ( null !== $this->taxar );
    }

    /**
     * @param int|string $taxar
     * @return static
     * @throws InvalidArgumentException
     */
    public function setTaxar( $taxar ) : self
    {
        Sie4Validator::assertIntegerish( self::TAXAR, $taxar );
        $this->taxar = (int) $taxar;
        return $this;
    }

    /**
     * @return null|DateTime
     */
    public function getOmfattn()
    {
        return $this->omfattn;
    }

    /**
     * Return bool true if omfattn is set
     *
     * @return bool
     */
    public function isOmfattnSet() : bool
    {
        return ( null !== $this->omfattn );
    }

    /**
     * @param DateTime $omfattn
     * @return static
     */
    public function setOmfattn( DateTime $omfattn ) : self
    {
        $this->omfattn = $omfattn;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getKptyp()
    {
        return $this->kptyp;
    }

    /**
     * Return bool true if kptyp is set
     *
     * @return bool
     */
    public function isKptypSet() : bool
    {
        return ( null !== $this->kptyp );
    }

    /**
     * @param string $kptyp
     * @return static
     */
    public function setKptyp( string $kptyp ) : self
    {
        $this->kptyp = $kptyp;
        return $this;
    }

    /**
     * Return valutakod
     *
     * @return null|string
     */
    public function getValutakod()
    {
        return $this->valutakod;
    }

    /**
     * Return bool true if valutakod is set
     *
     * @return bool
     */
    public function isValutakodSet() : bool
    {
        return ( null !== $this->valutakod );
    }

    /**
     * Set valutakod
     *
     * @param string $valutakod
     * @return static
     */
    public function setValutakod( string $valutakod ) : self
    {
        $this->valutakod = $valutakod;
        return $this;
    }
}