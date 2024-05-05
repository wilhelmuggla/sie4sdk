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

use DateTime;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\Traits\FnrIdOrgnr2Trait;
use Kigkonsult\Sie4Sdk\Dto\Traits\FnrIdOrgnrTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\SignTrait;
use Kigkonsult\Sie4Sdk\Util\Assert;

use function count;
use function usort;

/**
 * Class IdDto
 *
 * Company name required for Sie4I
 *
 * genDate  default 'now'
 * programnamn/version default set
 */
class IdDto implements DtoInterface
{
    /**
     * @var string
     */
    private string $programnamn;

    /**
     * @var string
     */
    private string $version;

    /**
     * @var DateTime
     */
    private DateTime $genDate;

    /**
     * sign used as genSign
     */
    use SignTrait;

    /**
     * @var int
     */
    private int $sieTyp = 4;

    /**
     * @var string|null
     */
    private ?string $prosa = null;

    /**
     * @var string|null
     */
    private ?string $ftyp = null;

    /**
     * FnrId and orgnr
     */
    use FnrIdOrgnrTrait;
    use FnrIdOrgnr2Trait;

    /**
     * @var string|null
     */
    private ?string $bkod = null;

    /**
     * @var AdressDto|null
     */
    private ?AdressDto $adress = null;

    /**
     * @var string|null
     */
    private ?string $fnamn = null;

    /**
     * @var RarDto[]
     */
    private array $rarDtos = [];

    /**
     * @var int|null  ÅÅÅÅ
     */
    private ?int $taxar = null;

    /**
     * @var DateTime|null
     */
    private ?DateTime $omfattn = null;

    /**
     * @var string|null
     */
    private ?string $kptyp = null;

    /**
     * @var string|null
     */
    private ?string $valutakod = null;

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
     * Class factory method, fname (,fnrId/orgnr)
     *
     * @param string $fnamn
     * @param null|string $fnrId
     * @param null|string $orgnr
     * @return self
     */
    public static function factory( string $fnamn, ? string $fnrId = null, ? string $orgnr = null ) : self
    {
        $instance = new self();
        $instance->setFnamn( $fnamn );
        if( ! empty( $fnrId )) {
            $instance->setFnrId( $fnrId );
        }
        if( ! empty( $orgnr )) {
            $instance->setOrgnr( $orgnr );
        }
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
     * @throws InvalidArgumentException
     */
    public function setSieTyp( int|string $sieTyp ) : self
    {
        Assert::isIntegerish( self::SIETYP, $sieTyp );
        $this->sieTyp = (int) $sieTyp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProsa() : ?string
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
     * @return self
     */
    public function setProsa( string $prosa ) : self
    {
        $this->prosa = $prosa;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFtyp() : ?string
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
     * @return self
     */
    public function setFtyp( string $ftyp ) : self
    {
        $this->ftyp = $ftyp;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getBkod() : ?string
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
     * @return self
     */
    public function setBkod( string $bkod ) : self
    {
        $this->bkod = $bkod;
        return $this;
    }

    /**
     * @return AdressDto|null
     */
    public function getAdress() : ? AdressDto
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
     * @return self
     */
    public function setAdress( AdressDto $adress ) : self
    {
        $this->adress = $adress;
        return $this;
    }

    /**
     * Return fnamn
     *
     * @return string|null
     */
    public function getFnamn() : ?string
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
     * @return self
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
     * @return RarDto[]
     */
    public function getRarDtos() : array
    {
        usort( $this->rarDtos, RarDto::$SORTER );
        return $this->rarDtos;
    }

    /**
     * @param RarDto $rar
     * @return self
     */
    public function addRarDto( RarDto $rar ) : self
    {
        $this->rarDtos[] = $rar;
        return $this;
    }

    /**
     * @param RarDto[] $rarDtos
     * @return self
     */
    public function setRarDtos( array $rarDtos ) : self
    {
        $this->rarDtos = [];
        foreach( $rarDtos as $rarDto ) {
            $this->addRarDto( $rarDto );
        }
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTaxar() : ?int
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
     * @return self
     * @throws InvalidArgumentException
     */
    public function setTaxar( int|string $taxar ) : self
    {
        Assert::isIntegerish( self::TAXAR, $taxar );
        Assert::isYYYYDate( self::TAXAR, $taxar );
        $this->taxar = (int) $taxar;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getOmfattn() : ?DateTime
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
     * @return self
     */
    public function setOmfattn( DateTime $omfattn ) : self
    {
        $this->omfattn = $omfattn;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getKptyp() : ?string
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
     * @return self
     */
    public function setKptyp( string $kptyp ) : self
    {
        $this->kptyp = $kptyp;
        return $this;
    }

    /**
     * Return valutakod
     *
     * @return string|null
     */
    public function getValutakod() : ?string
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
     * @return self
     */
    public function setValutakod( string $valutakod ) : self
    {
        $this->valutakod = $valutakod;
        return $this;
    }
}
