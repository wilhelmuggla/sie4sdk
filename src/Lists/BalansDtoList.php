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
use Kigkonsult\Asit\AsitList;
use Kigkonsult\Sie4Sdk\Dto\BalansDto;
use Kigkonsult\Sie4Sdk\Lists\Traits\KontoNrTrait;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Traversable;

/**
 * class BalansDtoList
 *
 * Collection of BalansDto[] with kontoNr as primary key
 *
 * @since 1.8.15 2024-03-12
 */
class BalansDtoList extends AsitList implements Sie4Interface
{
    /**
     * @var callable
     */
    static public $SORTER = [ self::class, 'balansSorter' ];

    /**
     * Sort BalansDto[] on kontonr, arsnr
     *
     * @param BalansDto $a
     * @param BalansDto $b
     * @return int
     */
    public static function balansSorter( BalansDto $a, BalansDto $b ) : int
    {
        if( 0 !== ( $res = StringUtil::strSort((string) $a->getKontoNr(),(string) $b->getKontoNr() ))) {
            return $res;
        }
        return StringUtil::strSort((string) $a->getArsnr(), (string) $b->getArsnr());
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
        parent::__construct( BalansDto::class );
    }

    use KontoNrTrait;

    /**
     * @override
     * @return BalansDto
     */
    #[\ReturnTypeWillChange]
    public function current() : BalansDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam    default on kontonr
     * @return BalansDto[]
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
     * @return BalansDto[]|Traversable   sorted on kontonr
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return new ArrayIterator( $this->get( self::$SORTER ));
    }
}
