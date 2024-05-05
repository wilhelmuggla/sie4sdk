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
use Kigkonsult\Asit\AsittagList;
use Kigkonsult\Sie4Sdk\Dto\BalansObjektDto;
use Kigkonsult\Sie4Sdk\Dto\KontoNrInterface;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Kigkonsult\Sie4Sdk\Util\Assert;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Traversable;

/**
 * class BalansObjektDtoList
 *
 * Collection of BalansObjektDto[] with $arsNr+$kontoNr+$dimensionNr+$objektNr as primary key
 *
 * @since 1.8.15 2024-03-12
 */
class BalansObjektDtoList extends AsittagList implements Sie4Interface
{
    /**
     * @var callable
     */
    public static $SORTER = [ self::class, 'balansObjektSorter' ];

    /**
     * Sort BalansObjektDto[] on kontonr, arsnr, dimensionNr, objektNr
     *
     * @param BalansObjektDto $a
     * @param BalansObjektDto $b
     * @return int
     */
    public static function balansObjektSorter( BalansObjektDto $a, BalansObjektDto $b ) : int
    {
        if( 0 !== ( $res = BalansDtoList::balansSorter( $a, $b ))) {
            return $res;
        }
        if( 0 !== ( $res = StringUtil::strSort((string) $a->getDimensionNr(),(string) $b->getDimensionNr()))) {
            return $res;
        }
        return StringUtil::strSort((string) $a->getObjektNr(), (string)$b->getObjektNr());
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
        parent::__construct( BalansObjektDto::class );
    }

    /**
     * @param int    $arsNr
     * @param string $kontoNr
     * @param int    $dimensionNr
     * @param string $objektNr
     * @return string
     */
    public static function getPrimaryKey( int $arsNr, string $kontoNr, int $dimensionNr, string $objektNr ) : string
    {
        static $FORMAT = "%04d%10s%04d%20s";
        $darsNr        = 100 - $arsNr;
        return sprintf( $FORMAT, $darsNr, $kontoNr, $dimensionNr, $objektNr );
    }

    /**
     * Return bool true if kontoNr exists
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
        return $this->tagExists( $kontoNr );
    }

    /**
     * @override
     * @return BalansObjektDto
     */
    #[\ReturnTypeWillChange]
    public function current() : BalansObjektDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam    default on kontonr
     * @return BalansObjektDto[]
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
     * @return BalansObjektDto[]|Traversable   sorted on kontonr
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return new ArrayIterator( $this->get( self::$SORTER ));
    }
}
