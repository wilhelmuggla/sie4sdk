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
namespace Kigkonsult\Sie4Sdk\Rsc;

use Exception;
use Kigkonsult\Asit\AsittagList;
use Kigkonsult\Sie4Sdk\Dto\UnderDimDto;
use RuntimeException;

class UnderDimList extends AsittagList
{
    /**
     * @override
     * @param mixed|null  $collection
     * @param string|null $valueType
     */
    public function __construct( mixed $collection = null, ? string $valueType = null )
    {
        parent::__construct( null, UnderDimDto::class );
    }

    /**
     * @param null|UnderDimDto[] $collection
     * @return self
     */
    public static function UnderDimListFactory( ? array $collection = null ) : self
    {
        $instance = new self();
        if( null !== $collection ) {
            foreach( $collection as $underDimDto ) {
                $instance->append(
                    $underDimDto,                   // list element
                    $underDimDto->getDimensionNr(), // primary key
                    $underDimDto->getSuperDimNr()   // tag
                );

            }
        }
        return $instance;
    }

    /**
     * @param int $underDimensionNr
     * @return string
     * @throws RuntimeException
     */
    public function getNamn( int $underDimensionNr ) : string
    {
        try {
            return $this->pKeySeek( $underDimensionNr )->current()->getNamn();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18321, $e );
        }
    }

    /**
     * @param int $underDimensionNr
     * @return int
     * @throws RuntimeException
     */
    public function getSuperDimension( int $underDimensionNr ) : int
    {
        try {
            return $this->pKeySeek( $underDimensionNr )->current()->getSupenDimNr();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18322, $e );
        }
    }

    /**
     * @param int $underDimensionNr
     * @return bool
     */
    public function isDimensionNrSet( int $underDimensionNr ) : bool
    {
        return $this->pKeyExists( $underDimensionNr );
    }

    /**
     * @param int $underDimensionNr
     * @return UnderDimDto
     * @throws RuntimeException
     */
    public function getUnderDimDto( int $underDimensionNr ) : UnderDimDto
    {
        try {
            return $this->pKeySeek( $underDimensionNr )->current();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18329, $e );
        }
    }

    /**
     * @param int $superDimNr
     * @return UnderDimDto[]
     * @throws RuntimeException
     */
    public function getUnderDimDtosForSuper( int $superDimNr ) : array
    {
        return $this->tagGet( $superDimNr );
    }
}
