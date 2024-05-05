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
use Exception;
use Kigkonsult\Asit\AsitList;
use Kigkonsult\Sie4Sdk\Dto\DimDto;
use Kigkonsult\Sie4Sdk\Lists\Traits\DimensionTrait;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use RuntimeException;
use Traversable;

/**
 * class DimDtoList
 *
 * Collection of DimDto[] with dimensionNr as primary key
 *
 * @since 1.8.13 2024-03-07
 */
class DimDtoList extends AsitList
{
    /**
     * @var callable
     */
    public static $SORTER = [ self::class, 'dimSorter' ];

    /**
     * Sort DimDto[] on dimensionsnr
     *
     * @param DimDto $a
     * @param DimDto $b
     * @return int
     */
    public static function dimSorter( DimDto $a, DimDto $b ) : int
    {
        return StringUtil::strSort((string) $a->getDimensionNr(), (string) $b->getDimensionNr());
    }

    /**
     * @override
     * @param mixed $collection
     * @param null|string $valueType
     */
    public function __construct( mixed $collection = null, ?string $valueType = null )
    {
        parent::__construct( null, DimDto::class );
    }

    use DimensionTrait;

    /**
     * @param int $dimensionNr
     * @return DimDto
     * @throws RuntimeException
     */
    public function getDimDto( int $dimensionNr ) : DimDto
    {
        try {
            return $this->pKeySeek( $dimensionNr )->current();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18329, $e );
        }
    }

    /**
     * @override
     * @return DimDto
     */
    #[\ReturnTypeWillChange]
    public function current() : DimDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam    default on dimensionNr
     * @return DimDto[]
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
     * @return DimDto[]|Traversable   sorted on dimensionNr
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return new ArrayIterator( $this->get( self::$SORTER ));
    }
}
