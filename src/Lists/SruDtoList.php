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
use Kigkonsult\Sie4Sdk\Dto\SruDto;
use Kigkonsult\Sie4Sdk\Lists\Traits\KontoNrTrait;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Kigkonsult\Sie4Sdk\Util\Assert;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Traversable;

/**
 * class SruDtoList
 *
 * Collection of SruDto[] with kontoNr as primary key
 *
 * @since 1.8.15 2024-03-12
 */
class SruDtoList extends AsitList implements Sie4Interface
{
    /**
     * @var callable
     */
    public static $SORTER = [ self::class, 'sruSorter' ];

    /**
     * Sort SruDto[] on kontonr
     *
     * @param SruDto $a
     * @param SruDto $b
     * @return int
     */
    public static function sruSorter( SruDto $a, SruDto $b ) : int
    {
        return StringUtil::strSort((string) $a->getKontoNr(), (string) $b->getKontoNr());
    }

    /**
     * Extended constructor, NO param accepts
     *
     * @override ItList::__construct()
     * @param mixed|null $collection  not used here
     * @param string|null $valueType  not used here
     */
    public function __construct( mixed $collection = null, ? string $valueType = null )
    {
        parent::__construct( SruDto::class );
    }

    use KontoNrTrait;

    /**
     * Return int sruKod for kontoNr
     *
     * @param int|string $kontoNr
     * @return int
     */
    public function getSruKod( int|string $kontoNr ) : int
    {
        Assert::isIntegerish( Sie4Interface::KONTONR, $kontoNr );
        return $this->pKeySeek( $kontoNr )->current()->getSruKod();
    }

    /**
     * @override
     * @return SruDto
     */
    #[\ReturnTypeWillChange]
    public function current() : SruDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam    default on kontonr
     * @return SruDto[]
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
     * @return SruDto[]|Traversable   sorted on kontonr
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return new ArrayIterator( $this->get( self::$SORTER ));
    }
}
