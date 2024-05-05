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

use Kigkonsult\Asit\AsittagList;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Traversable;

use function number_format;

/**
 * class TransDtoList
 *
 * @since 1.8.13 2024-03-07
 */
class TransDtoList extends AsittagList implements Sie4Interface
{
    /**
     * Extended constructor, NO param accepts
     *
     * @override AsittagList::__construct()
     * @param mixed|null $collection  not used here
     * @param string|null $valueType  not used here
     */
    public function __construct( mixed $collection = null, ? string $valueType = null )
    {
        parent::__construct( TransDto::class );
    }

    /**
     * @override
     * @return TransDto
     */
    #[\ReturnTypeWillChange]
    public function current() : TransDto
    {
        return parent::current();
    }

    /**
     * @override
     * @param callable|int|null $sortParam
     * @return TransDto[]
     */
    #[\ReturnTypeWillChange]
    public function get( null|callable|int $sortParam = null ) : array
    {
        return parent::get( $sortParam );
    }

    /**
     * @override
     * @return TransDto[]|Traversable
     */
    #[\ReturnTypeWillChange]
    public function getIterator() : Traversable
    {
        return parent::getIterator();
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
        $save    = $this->key();
        $balans  = 0.00;
        for( $this->rewind(); $this->valid(); $this->next()) {
            $balans += $this->current()->getBelopp();
        }
        $balans2 = (float) number_format( $balans, 2, StringUtil::$DOT, StringUtil::$SP0 );
        $return  = ( 0.00 === $balans2 );
        if( $this->exists( $save )) {
            $this->seek( $save );
        }
        return $return;
    }

    /**
     * Updates all TransDto with opt parentCorrelationId and, opt, IdData
     *
     * @param null|string $correlationId  for parent
     * @param null|VerDto $verDto
     * @return void
     */
    public function setCorrIdDtoData( ? string $correlationId = null, ? VerDto $verDto = null ) : void
    {
        for( $this->rewind(); $this->valid(); $this->next()) {
            $this->current()->setCorrIdDtoData( $correlationId, $verDto );
        } // end for
    }
}
