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
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\Traits\ParentCorrelationIdTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\SerieVernrTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\SignTrait;
use Kigkonsult\Sie4Sdk\Lists\TransDtoList;
use Traversable;

use function is_string;

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
    private ? string $vertext = null;

    /**
     * @var DateTime|null
     */
    private ? DateTime $regdatum = null;

    use SignTrait;

    /**
     * @var TransDtoList  contains #TRANS/#RTRANS/#BTRANS
     */
    private TransDtoList $transDtos;

    /**
     * VerDto constructor
     *
     * Sets unique timestamp, guid and verdatum (opt overload later)
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct();
        $this->setVerdatum( new DateTime());
        $this->transDtos = new TransDtoList();
    }

    /**
     * Class factory method, opt vernr/text, verDatum default 'now'
     *
     * @param null|int     $vernr
     * @param null|string $verText
     * @param null|string|DateTime $verDatum  default 'now'
     * @return self
     */
    public static function factory(
        ? int    $vernr = null,
        ? string $verText = null,
        null|string|DateTime $verDatum = null
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
     * Updates VerDto with opt parentCorrelationId and, opt, IdData
     *
     * @param null|string $correlationId  for parent
     * @param null|IdDto  $idDto
     * @return void
     */
    public function setCorrIdDtoData( ? string $correlationId = null, ? IdDto $idDto = null ) : void
    {
        if( null !== $correlationId ) {
            $this->setParentCorrelationId( $correlationId );
        }
        if( null !== $idDto ) {
            if( $idDto->isFnrIdSet()) {
                $this->setFnrId( $idDto->getFnrId());
            }
            if( $idDto->isOrgnrSet()) {
                $this->setOrgnr( $idDto->getOrgnr());
                $this->setMultiple( $idDto->getMultiple());
            }
        } // end if
        $this->transDtos->setCorrIdDtoData( $this->correlationId, $this );
    }

    /**
     * Set vernr serie, will populate down to all transDtos
     *
     * @param int|string $serie
     * @return self
     */
    public function setSerie( int | string $serie ) : self
    {
        $this->serie = (string) $serie;
        $this->transDtos->setCorrIdDtoData( null, $this );
        return $this;
    }

    /**
     * Set vernr, will populate down to all transDtos
     *
     * @param int $vernr
     * @return self
     */
    public function setVernr( int $vernr ) : self
    {
        $this->vernr = $vernr;
        $this->transDtos->setCorrIdDtoData( null, $this );
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
     * @param string|DateTime $verdatum
     * @return VerDto
     * @throws InvalidArgumentException
     */
    public function setVerdatum( string|DateTime $verdatum ) : VerDto
    {
        if( is_string( $verdatum )) {
            try {
                $verdatum = new DateTime( $verdatum );
            }
            catch( Exception $e ) {
                throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
            }
        }
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
    public function getRegdatum() : ? DateTime
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
     * @param string|DateTime $regdatum
     * @return self
     * @throws InvalidArgumentException
     */
    public function setRegdatum( string|DateTime $regdatum ) : self
    {
        if( is_string( $regdatum )) {
            try {
                $regdatum = new DateTime( $regdatum );
            }
            catch( Exception $e ) {
                throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
            }
        }
        $this->regdatum = $regdatum;
        $this->transDtos->setCorrIdDtoData( null, $this );
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
        return $this->transDtos->tagCount( $transType ?? self::TRANS );
    }

    /**
     * Return array, TransDto[], #TRANS / #RTRANS / #BTRANS  (all default)
     *
     * @param null|string $transType
     * @return TransDtoList|TransDto[]|Traversable
     */
    public function getTransDtos( ? string $transType = null ) : TransDtoList|Traversable
    {
        return ( null === $transType )
            ? $this->transDtos
            : $this->transDtos->tagGet( $transType );
    }

    /**
     * Return bool true if transDto[] is 'i balans', if NOT false and dif in $balans
     *
     * Will NOT affect the internal counter
     *
     * @param null|float $balans
     * @return bool
     */
    public function iBalans( ? float & $balans = 0.00 ) : bool
    {
        return $this->transDtos->iBalans( $balans );
    }

    /**
     * Add single transDto using kontoNr, belopp, #TRANS (default)
     *
     * @param int|string  $kontoNr
     * @param float|int|string $belopp
     * @param null|string $transType
     * @param null|string $transText
     * @return self
     */
    public function addTransKontoNrBelopp(
        int|string $kontoNr,
        float|int|string $belopp,
        ? string $transType = null,
        ? string $transText = null
    ) : self
    {
        $this->addTransDto(
            TransDto::factory( $kontoNr, $belopp, $transType, $transText )
        );
        return $this;
    }

    /**
     * Add single transDto #TRANS (default) / #RTRANS / #BTRANS
     *
     * Populates down correlationId, fnrId, orgnr(+multiple), serie and vernr
     * If missing, transdat is set from regdatum (if set)
     *
     * @param TransDto $transDto
     * @return self
     * @since 1.8.4 20230925
     */
    public function addTransDto( TransDto $transDto ) : self
    {
        $transDto->setCorrIdDtoData( $this->getCorrelationId(), $this );
        $this->transDtos->append( $transDto )
            ->addCurrentTag( $transDto->getTransType())
            ->addCurrentTag( $transDto->getKontoNr());
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
        $this->transDtos->init();
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
        $this->transDtos->setCorrIdDtoData( null, $this );
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
        $this->transDtos->setCorrIdDtoData( null, $this );
        return $this;
    }

    /**
     * Set multiple in each transDto
     *
     * @param int $multiple
     * @return self
     */
    public function setMultiple( int $multiple ) : self
    {
        $this->multiple = $multiple;
        $this->transDtos->setCorrIdDtoData( null, $this );
        return $this;
    }
}
