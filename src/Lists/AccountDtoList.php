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
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Lists\Traits\KontoNrTrait;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Traversable;

/**
 * class AccountDtoList
 *
 * Collection of AccountDto[] with kontoNr as primary key, kontoTyp as tag
 *
 * @since 1.8.13 2024-03-07
 */
class AccountDtoList extends AsittagList implements Sie4Interface
{
    /**
     * @var callable
     */
    public static $SORTER = [ self::class, 'accountSorter' ];

    /**
     * Sort AccountDto[] on kontonr
     *
     * @param AccountDto $a
     * @param AccountDto $b
     * @return int
     */
    public static function accountSorter( AccountDto $a, AccountDto $b ) : int
    {
        return StringUtil::strSort((string) $a->getKontoNr(), (string) $b->getKontoNr());
    }

    /**
     * Extended constructor, NO param accepts
     *
     * @override AsittagList::__construct()
     * @param mixed|null $collection  not used here
     * @param string|null $valueType  not used here
     */
    public function __construct( mixed $collection = null, ? string $valueType = null )
    {
        parent::__construct( AccountDto::class );
    }

    use KontoNrTrait;

    /**
     * @override
     * @return AccountDto
     */
    #[\ReturnTypeWillChange]
    public function current() : AccountDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam    default on kontonr
     * @return AccountDto[]
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
     * @return AccountDto[]|Traversable   sorted on kontonr
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return new ArrayIterator( $this->get( self::$SORTER ));
    }
}
