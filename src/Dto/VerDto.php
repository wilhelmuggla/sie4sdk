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
use Exception;
use Kigkonsult\Sie4Sdk\Dto\Traits\ParentCorrelationIdTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\SerieVernrTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\SignTrait;
use Kigkonsult\Sie4Sdk\Util\StringUtil;

/**
 * Class VerDto
 *
 * Inherit timestamp, guid, fnrId and orgnr(+multiple) properties from BaseId,
 * fnrId and orgnr(+multiple), serie and vernr are populated down to each TransDto instance
 * to uniquely identify instance
 *
 * Verdatum and trans required
 */
class VerDto extends BaseId
{
    /**
     * Serie and vernr
     */
    use SerieVernrTrait;

    /**
     * Parent CorrelationId
     */
    use ParentCorrelationIdTrait;

    /**
     * @var DateTime
     */
    private DateTime $verdatum;

    /**
     * @var string|null
     */
    private ?string $vertext = null;

    /**
     * @var DateTime|null
     */
    private ?DateTime $regdatum = null;

    use SignTrait;

    /**
     * @var TransDto[]  contains #TRANS/#RTRANS/#BTRANS
     */
    private array $transDtos = [];

    /**
     * @var callable
     */
    public static $SORTER = [ VerDto::class, 'verDtoSorter'];

    /**
     * Sort VerDto[] on serie and vernr
     *
     * @param VerDto $a
     * @param VerDto $b
     * @return int
     * @since 1.8.7 2023-12-08
     */
    public static function verDtoSorter( VerDto $a, VerDto $b ) : int
    {
        if( 0 !== ( $res = StringUtil::strSort((string) $a->getSerie(),(string) $b->getSerie()))) {
            return $res;
        }
        $aCmp = (string) $a->getVernr();
        $bCmp = (string) $b->getVernr();
        if( StringUtil::isInteger( $aCmp ) && StringUtil::isInteger( $bCmp )) {
            $aCmp = (int) $aCmp;
            $bCmp = (int) $bCmp;
            return match( true ) {
                ( $aCmp < $bCmp ) => -1,
                ( $aCmp > $bCmp ) => 1,
                default => 0
            };
        }
        return StringUtil::strSort( $aCmp, $aCmp );
    }

    /**
     * VerDto constructor
     *
     * Sets unique timestamp, guid and verdatum (opt overload later)
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->setVerdatum( new DateTime());
    }

    /**
     * Class factory method, opt vernr/text, verDatum default 'now'
     *
     * @param int|null $vernr
     * @param string|null $verText
     * @param DateTime|null $verDatum  default 'now'
     * @return self
     */
    public static function factory(
        ? int    $vernr = null,
        ? string $verText = null,
        ? DateTime $verDatum = null
    ) : self
    {
        $instance = new self();
        if( $vernr !== null ) {
            $instance->setVernr( $vernr );
        }
        if( ! empty( $verText )) {
            $instance->setVertext( $verText );
        }
        if( $verDatum !== null ) {
            $instance->setVerdatum( $verDatum );
        }
        return $instance;
    }

    /**
     * Set serie
     *
     * @param int|string $serie
     * @return self
     */
    public function setSerie( int | string $serie ) : self
    {
        $this->serie = (string) $serie;
        foreach( $this->transDtos as $transDto ) {
            $transDto->setSerie( $this->serie );
        }
        return $this;
    }

    /**
     * Set vernr
     *
     * @param int $vernr
     * @return self
     */
    public function setVernr( int $vernr ) : self
    {
        $this->vernr = $vernr;
        foreach( $this->transDtos as $transDto ) {
            $transDto->setvernr( $this->vernr );
        }
        return $this;
    }

    /**
     * Return verdatum
     *
     * @return DateTime
     */
    public function getVerdatum() : DateTime
    {
        return $this->verdatum;
    }

    /**
     * Return bool true if verdatum is set (always...)
     *
     * @return bool
     */
    public function isVerdatumSet() : bool
    {
        return isset( $this->verdatum );
    }

    /**
     * Set verdatum, DateTime
     *
     * @param DateTime $verdatum
     * @return VerDto
     */
    public function setVerdatum( DateTime $verdatum ) : VerDto
    {
        $this->verdatum = $verdatum;
        return $this;
    }

    /**
     * Return vertext
     *
     * @return string|null
     */
    public function getVertext() : ?string
    {
        return $this->vertext;
    }

    /**
     * Return bool true if vertext is set
     *
     * @return bool
     */
    public function isVertextSet() : bool
    {
        return ( null !== $this->vertext );
    }

    /**
     * Set vertext
     *
     * @param string $vertext
     * @return self
     */
    public function setVertext( string $vertext ) : self
    {
        $this->vertext = $vertext;
        return $this;
    }

    /**
     * Return regdatum
     *
     * @return DateTime|null
     */
    public function getRegdatum() : ?DateTime
    {
        return $this->regdatum;
    }

    /**
     * Return bool true if regdatum is set
     *
     * @return bool
     */
    public function isRegdatumSet() : bool
    {
        return ( null !== $this->regdatum );
    }

    /**
     * Set regdatum, DateTime
     *
     * @param DateTime $regdatum
     * @return self
     */
    public function setRegdatum( DateTime $regdatum ) : self
    {
        $this->regdatum = $regdatum;
        return $this;
    }

    /**
     * Return int, count of transDtos #TRANS (default) / #RTRANS / #BTRANS
     *
     * @param null|string $transType
     * @return int
     */
    public function countTransDtos( ? string $transType = null ) : int
    {
        $transType = $transType ?? self::TRANS;
        $count     = 0;
        foreach( $this->transDtos as $transDto ) {
            if( $transType === $transDto->getTransType()) {
                ++$count;
            }
        }
        return $count;
    }

    /**
     * Return array, TransDto[], #TRANS / #RTRANS / #BTRANS  (all default)
     *
     * @param null|string $transType
     * @return TransDto[]
     */
    public function getTransDtos( ? string $transType = null ) : array
    {
        $output = [];
        foreach( $this->transDtos as $transDto ) {
            if(( null === $transType ) || ( $transType === $transDto->getTransType())) {
                $output[] = $transDto;
            }
        }
        return $output;
    }

    /**
     * Add single transDto using kontoNr, belopp, #TRANS default
     *
     * @param int|string $kontoNr
     * @param float  $belopp
     * @return self
     */
    public function addTransKontoNrBelopp( int | string $kontoNr, float $belopp ) : self
    {
        return $this->addTransDto(
            TransDto::factory( $kontoNr, $belopp )
        );
    }

    /**
     * Add single transDto #TRANS (default) / #RTRANS / #BTRANS
     *
     * Populates down fnrId, orgnr(+multiple), serie and vernr
     * If missing, transdat is set from regdatum (if set)
     *
     * @param TransDto $transDto
     * @return self
     * @since 1.8.4 20230925
     */
    public function addTransDto( TransDto $transDto ) : self
    {
        $transDto->setParentCorrelationId( $this->getCorrelationId());
        if( $this->isFnrIdSet()) {
            $transDto->setFnrId( $this->getFnrId());
        }
        if( $this->isOrgnrSet()) {
            $transDto->setOrgnr( $this->getOrgnr());
            $transDto->setMultiple( $this->getMultiple());
        }
        if( $this->isSerieSet()) {
            $transDto->setSerie( $this->getSerie());
        }
        if( $this->isVernrSet()) {
            $transDto->setVernr( $this->getVernr());
        }
        if( ! $transDto->isTransdatSet() && $this->isRegdatumSet()) {
            $transDto->setTransdat( clone $this->getRegdatum());
        }
        $this->transDtos[] = $transDto;
        return $this;
    }

    /**
     * Set transDtos, array TransDto[] #TRANS (default) / #RTRANS / #BTRANS
     *
     * @param TransDto[] $transDtos
     * @return self
     */
    public function setTransDtos( array $transDtos ) : self
    {
        $this->transDtos = [];
        foreach( $transDtos as $transDto ) {
            $this->addTransDto( $transDto );
        }
        return $this;
    }

    /**
     * Set fnrId in each trandDto
     *
     * @override
     * @param string $fnrId
     * @return self
     */
    public function setFnrId( string $fnrId ) : self
    {
        $this->fnrId = $fnrId;
        foreach( $this->transDtos as $transDto ) {
            $transDto->setFnrId( $fnrId );
        }
        return $this;
    }

    /**
     * Set orgnr in each trandDto
     *
     * @override
     * @param string $orgnr
     * @return self
     */
    public function setOrgnr( string $orgnr ) : self
    {
        $this->orgnr = $orgnr;
        foreach( $this->transDtos as $transDto ) {
            $transDto->setOrgnr( $orgnr );
        }
        return $this;
    }

    /**
     * Set multiple in each trandDto
     *
     * @param int $multiple
     * @return self
     */
    public function setMultiple( int $multiple ) : self
    {
        $this->multiple = $multiple;
        foreach( $this->transDtos as $transDto ) {
            $transDto->setMultiple( $multiple );
        }
        return $this;
    }
}
