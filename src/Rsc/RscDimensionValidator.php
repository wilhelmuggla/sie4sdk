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

use Kigkonsult\Sie4Sdk\Dto\DimensionNrInterface;
use Kigkonsult\Sie4Sdk\Dto\UnderDimDto;
use RuntimeException;

use function sprintf;

/**
 * Class RscDimensionValidator
 *
 * Validates DimensionNr using DimList, UnderDim using UnderDimLst
 */
class RscDimensionValidator
{
    /**
     * @var DimList|null
     */
    private ?DimList $dimList = null;

    /**
     * @var UnderDimList|null
     */
    private ?UnderDimList $underDimList = null;

    /**
     * Class factory method
     *
     * @param DimList $dimList
     * @param UnderDimList $underDimList
     * @return self
     */
    public static function factory( DimList $dimList, UnderDimList $underDimList ) : self
    {
        $instance = new self();
        $instance->setDimList( $dimList );
        $instance->setUnderDimList( $underDimList );
        return $instance;
    }

    /**
     * Return bool true if dimensionNr is a dimensionNr, false on not found
     *
     * @param int $dimensionNr
     * @return bool
     * @throws RuntimeException
     */
    public function dimNrExists( int $dimensionNr ) : bool
    {
        $this->assertDimList();
        return $this->dimList->isDimensionNrSet( $dimensionNr );
    }

    /**
     * Return bool true if dimensionNr is a underDimensionNr, false on not found
     *
     * @param int $dimensionNr
     * @return bool
     * @throws RuntimeException
     */
    public function underDimNrExists( int $dimensionNr ) : bool
    {
        $this->assertUnderDimList();
        return $this->underDimList->isDimensionNrSet( $dimensionNr );
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function assertDimList() : void
    {
        static $ERR = 'DimList NOT set';
        if( empty( $this->dimList )) {
            throw new RuntimeException( $ERR, 18801 );
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function assertUnderDimList() : void
    {
        static $ERR = 'UnderDimList NOT set';
        if( empty( $this->underDimList )) {
            throw new RuntimeException( $ERR, 18802 );
        }
    }

    /**
     * Assert each DimensionNrInterface[] instance dimNr, must exist in dimList (underDimList)
     *
     * May also be underDimDto[] instance dimNr, must exist in underDimList
     *
     * @param DimensionNrInterface[] $dimNrDtos
     * @return void
     * @throws RuntimeException
     */
    public function assertDimNrs( array $dimNrDtos ) : void
    {
        static $ERR = 'DimNr (#%d) %s NOT exists';
        $this->assertDimList();
        $this->assertUnderDimList();
        foreach( $dimNrDtos as $dx => $dimNrDto ) {
            $dimNr = $dimNrDto->getDimensionNr();
            if( ! $this->dimNrExists( $dimNr ) &&
                ! $this->underDimNrExists( $dimNr )) {
                throw new RuntimeException( sprintf( $ERR, $dx, $dimNr ), 18811 );
            }
        } // end foreach
    }

    /**
     * Assert each UnderDimDto[] instance dimNr+superDim, must exist in underDimList+dimList
     *
     * @param UnderDimDto[] $underDimDtos
     * @return void
     * @throws RuntimeException
     */
    public function assertUnderDimDto( array $underDimDtos ) : void
    {
        static $ERR1 = 'UnderDimNr (#%d) %s NOT exists';
        static $ERR2 = 'DimNr (#%d) %s NOT exists';
        $this->assertDimList();
        $this->assertUnderDimList();
        foreach( $underDimDtos as $dx => $underDimDto ) {
            $dimNr = $underDimDto->getDimensionNr();
            if( ! $this->underDimNrExists( $dimNr )) {
                throw new RuntimeException( sprintf( $ERR1, $dx, $dimNr ), 18821 );
            }
            $dimNr = $underDimDto->getSuperDimNr();
            if( ! $this->dimNrExists( $dimNr )) {
                throw new RuntimeException( sprintf( $ERR2, $dx, $dimNr ), 18822 );
            }
        } // end foreach
    }

    /**
     * @return null|DimList
     */
    public function getDimList() : ? DimList
    {
        return $this->dimList;
    }

    /**
     * @param DimList $dimList
     * @return RscDimensionValidator
     */
    public function setDimList( DimList $dimList ) : RscDimensionValidator
    {
        $this->dimList = $dimList;
        return $this;
    }

    /**
     * @return null|UnderDimList
     */
    public function getUnderDimList() : ? UnderDimList
    {
        return $this->underDimList;
    }

    /**
     * @param UnderDimList $underDimList
     * @return RscDimensionValidator
     */
    public function setUnderDimList( UnderDimList $underDimList ) : RscDimensionValidator
    {
        $this->underDimList = $underDimList;
        return $this;
    }
}
