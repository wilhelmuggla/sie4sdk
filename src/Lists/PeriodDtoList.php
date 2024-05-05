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
namespace Kigkonsult\Sie4Sdk\Lists;

use ArrayIterator;
use InvalidArgumentException;
use Kigkonsult\Asit\AsitList;
use Kigkonsult\Sie4Sdk\Dto\KontoNrInterface;
use Kigkonsult\Sie4Sdk\Dto\PeriodDto;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Kigkonsult\Sie4Sdk\Util\Assert;
use Traversable;

/**
 * class PeriodDtoList
 *
 * Collection of PeriodDto[] with arsNr+kontoNr+dimensionNr+objektNr+period as primary key
 *
 * @since 1.8.15 2024-03-12
 */
class PeriodDtoList extends AsitList implements Sie4Interface
{
    /**
     * @var callable
     */
    public static $SORTER = [ PeriodDtoList::class, 'periodSorter' ];

    /**
     * Sort BalansObjektDto[] on kontonr, arsnr, dimensionNr, objektNr
     *
     * @param PeriodDto $a
     * @param PeriodDto $b
     * @return int
     */
    public static function periodSorter( PeriodDto $a, PeriodDto $b ) : int
    {
        return BalansObjektDtoList::balansObjektSorter( $a, $b );
    }

    /**
     * Extended constructor, NO param accepts
     *
     * @override AsitList::__construct()
     * @param mixed|null $collection  not used here
     * @param string|null $valueType  not used here
     */
    public function __construct( mixed $collection = null, ? string $valueType = null )
    {
        parent::__construct( PeriodDto::class );
    }

    /**
     * @param int    $arsNr
     * @param string $kontoNr
     * @param int    $dimensionNr
     * @param string $objektNr
     * @param int|string $period
     * @return string
     */
    public static function getPrimaryKey(
        int $arsNr,
        string $kontoNr,
        int $dimensionNr,
        string $objektNr,
        int|string $period
    ) : string
    {
        static $FORMAT = '%04d%10s%04d%20s%06s';
        $darsNr        = 100 - $arsNr;
        return sprintf( $FORMAT, $darsNr, $kontoNr, $dimensionNr, $objektNr, $period );
    }

    /**
     * Return bool true if kontoNr exists ( and arsnr === 0)
     *
     * @param int|string|KontoNrInterface $kontoNr
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isKontoNrSet( int|string|KontoNrInterface $kontoNr ) : bool
    {
        if( $kontoNr instanceof KontoNrInterface ) {
            $kontoNr = $kontoNr->getKontoNr();
        }
        Assert::isIntegerish( KontoNrInterface::KONTONR, $kontoNr );
        $save   = $this->position;
        $return = false;
        for( $this->rewind(); $this->valid(); $this->next()) {
            if(( $kontoNr == $this->current()->getKontoNr()) && // note ==
                ( 0 === $this->current()->getArsnr())) {
                $return = true;
            }
        }
        if( $this->exists( $save )) {
            $this->seek( $save );
        }
        return $return;
    }

    /**
     * Return PeriodDto for kontoNr (and arsnr === 0)
     *
     * @param int|string|KontoNrInterface $kontoNr
     * @return PeriodDto
     * @throws InvalidArgumentException
     */
    public function getPeriodDto( int|string|KontoNrInterface $kontoNr ) : PeriodDto
    {
        static $ERR = '%s : PeriodDto NOT found for %s';
        if( $kontoNr instanceof KontoNrInterface ) {
            $kontoNr = $kontoNr->getKontoNr();
        }
        Assert::isIntegerish( KontoNrInterface::KONTONR, $kontoNr );
        for( $this->rewind(); $this->valid(); $this->next()) {
            if(( $kontoNr == $this->current()->getKontoNr()) && // note ==
                ( 0 === $this->current()->getArsnr())) {
                return $this->current();
            }
        }
        throw new InvalidArgumentException( sprintf( $ERR, __FUNCTION__, $kontoNr ));
    }

    /**
     * @override
     * @return PeriodDto
     */
    #[\ReturnTypeWillChange]
    public function current() : PeriodDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam    default on kontonr
     * @return PeriodDto[]
     */
    #[\ReturnTypeWillChange]
    public function get( null|callable|int $sortParam = null ) : array
    {
        if( null === $sortParam ) {
            $sortParam = self::$SORTER;
        }
        return parent::get( $sortParam );
    }

    /**
     * @override
     * @return PeriodDto[]|Traversable   sorted on kontonr
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return new ArrayIterator( $this->get( self::$SORTER ));
    }
}
