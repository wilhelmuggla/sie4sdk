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
use Kigkonsult\Sie4Sdk\Dto\IdDto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Traversable;

/**
 * class VerDtoList
 *
 * @since 1.8.13 2024-03-07
 */
class VerDtoList extends AsitList implements Sie4Interface
{
    /**
     * @var callable
     */
    public static $SORTER = [ self::class, 'verDtoSorter'];

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
     * Extended constructor, NO param accepts
     *
     * @override AsittagList::__construct()
     * @param mixed|null $collection  not used here
     * @param string|null $valueType  not used here
     */
    public function __construct( mixed $collection = null, ? string $valueType = null )
    {
        parent::__construct( VerDto::class );
    }

    /**
     * @param VerDTo $verDto
     * @return string
     */
    public static function getPrimaryKey( VerDTo $verDto ) : string
    {
        static $FORMAT = '%04d%012d';
        $serie = $verDto->isSerieSet() ? $verDto->getSerie() : 0;
        $verNr = $verDto->isVernrSet() ? $verDto->getVernr() : (int)( microtime( true ) * 100000 );
        return sprintf( $FORMAT, $serie, $verNr );
    }

    /**
     * @override
     * @return VerDto
     */
    #[\ReturnTypeWillChange]
    public function current() : VerDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam    default on kontonr
     * @return VerDto[]
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
     * @return VerDto[]|Traversable   sorted on serie+vernr
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return new ArrayIterator( $this->get());
    }

    /**
     * Updates all VerDto with opt parentCorrelationId and, opt, IdData
     *
     * @param null|string $correlationId  for parent
     * @param null|IdDto  $idDto
     * @return void
     */
    public function setCorrIdDtoData( ? string $correlationId = null, ? IdDto $idDto = null ) : void
    {
        for( $this->rewind(); $this->valid(); $this->next()) {
            $this->current()->setCorrIdDtoData( $correlationId, $idDto );
        }
    }
}
