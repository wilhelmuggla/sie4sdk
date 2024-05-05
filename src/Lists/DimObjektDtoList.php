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
use Kigkonsult\Asit\AsittagList;
use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Traversable;

use function sprintf;

/**
 * class DimObjektDtoList
 *
 * Collection of DimObjektDto[] with dimensionNr+ObjektNr as primary key
 *
 * @since 1.8.13 2024-03-07
 */
class DimObjektDtoList extends AsittagList
{
    /**
     * @var callable
     */
    public static $SORTER = [ self::class, 'dimObjektSorter' ];

    /**
     * Sort DimObjektDto[] on kontonr
     *
     * @param DimObjektDto $a
     * @param DimObjektDto $b
     * @return int
     */
    public static function dimObjektSorter( DimObjektDto $a, DimObjektDto $b ) : int
    {
        if( 0 !== ( $result = DimDtoList::dimSorter( $a, $b ))) {
            return $result;
        }
        return StringUtil::strSort( $a->getObjektNr(), $b->getObjektNr());
    }

    /**
     * @override
     * @param mixed       $collection
     * @param null|string $valueType
     */
    public function __construct( mixed $collection = null, ?string $valueType = null )
    {
        parent::__construct( null, DimObjektDto::class );
    }

    /**
     * @param int $dimensionNr
     * @param string $objektNr
     * @return string
     */
    public static function getPrimaryKey( int $dimensionNr, string $objektNr ) : string
    {
        static $FORMAT = '%04d%20s';
        return sprintf( $FORMAT, $dimensionNr, $objektNr );
    }

    /**
     * Return bool true if dimensionNr + objektNr is set
     *
     * @param int $dimensionNr
     * @param string $objektNr
     * @return bool
     */
    public function isObjektNrSet( int $dimensionNr, string $objektNr ) : bool
    {
        return $this->pKeyExists( self::getPrimaryKey( $dimensionNr, $objektNr ));
    }

    /**
     * @override
     * @return DimObjektDto
     */
    #[\ReturnTypeWillChange]
    public function current() : DimObjektDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam    default on kontonr
     * @return DimObjektDto[]
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
     * @return DimObjektDto[]|Traversable   sorted on kontonr
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return new ArrayIterator( $this->get( self::$SORTER ));
    }
}
