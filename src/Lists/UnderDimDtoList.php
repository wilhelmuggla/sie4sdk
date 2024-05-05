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
use Kigkonsult\Asit\Exceptions\SortException;
use Kigkonsult\Sie4Sdk\Dto\UnderDimDto;
use Kigkonsult\Sie4Sdk\Lists\Traits\DimensionTrait;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Traversable;

/**
 * class UnderDimDtoList
 *
 * Collection of UnderDimDto[] with dimensionNr+superDim as primary key, superDim as tag
 *
 * @since 1.8.13 2024-03-07
 */
class UnderDimDtoList extends AsittagList
{
    /**
     * @var string
     */
    private static string $ERRTXT = 'DimensionNr %s NOT found';

    /**
     * @var callable
     */
    public static $SORTER = [ self::class, 'underDimSorter' ];

    /**
     * Sort UnderDimDto[] on (super-)dimensionsnr and (under-)dimensionsnr
     *
     * @param UnderDimDto $a
     * @param UnderDimDto $b
     * @return int
     */
    public static function underDimSorter( UnderDimDto $a, UnderDimDto $b ) : int
    {
        if( 0 !== ( $res = StringUtil::strSort((string) $a->getSuperDimNr(),(string) $b->getSuperDimNr()))) {
            return $res;
        }
        return StringUtil::strSort((string) $a->getDimensionNr(), (string) $b->getDimensionNr());
    }

    /**
     * @override
     * @param mixed       $collection
     * @param null|string $valueType
     */
    public function __construct( mixed $collection = null, ?string $valueType = null )
    {
        parent::__construct( null, UnderDimDto::class );
    }

    /**
     * @param int $dimensionNr
     * @param int $superDimNr
     * @return string
     */
    public static function getPrimaryKey( int $dimensionNr, int $superDimNr ) : string
    {
        static $FORMAT = '%04d%04d';
        return sprintf( $FORMAT, $superDimNr, $dimensionNr );
    }

    use DimensionTrait;

    /**
     * @param int $underDimensionNr
     * @return int
     * @throws InvalidArgumentException
     */
    public function getSuperDimensionNr( int $underDimensionNr ) : int
    {
        for( $this->rewind(); $this->valid(); $this->next()) {
            if( $underDimensionNr === $this->current()->getDimensionNr()) {
                return $this->current()->getSuperDimNr();
            }
        }
        throw new InvalidArgumentException( sprintf( self::$ERRTXT, $underDimensionNr ), 18322 );
    }

    /**
     * Return UnderDimDto[] for superDimNr
     *
     * @param int $superDimNr
     * @return UnderDimDto[]
     * @throws InvalidArgumentException
     */
    public function getUnderDimDtosForSuper( int $superDimNr ) : array
    {
        try {
            return $this->tagGet( $superDimNr );
        }
        catch( SortException $e ) {
            throw new InvalidArgumentException( $e->getMessage(), 18323, $e );
        }
    }

    /**
     * @override
     * @return UnderDimDto
     */
    #[\ReturnTypeWillChange]
    public function current() : UnderDimDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam    default on dimensionNr
     * @return UnderDimDto[]
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
     * @return UnderDimDto[]|Traversable   sorted on (super-)dimensionsnr and (under-)dimensionsnr
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return new ArrayIterator( $this->get( self::$SORTER ));
    }
}
