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
namespace Kigkonsult\Sie4Sdk\Dto;

use Kigkonsult\Sie4Sdk\Util\StringUtil;

class UnderDimDto extends DimDto
{
    /**
     * @var int|null
     */
    protected ?int $superDimNr = null;

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
     * Class factory method, set dimensionNr, dimensionsNamn and superDimNr
     *
     * @param int|string $dimensionsNr
     * @param string $dimensionsNamn
     * @param int|string $superDimNr
     * @return self
     */
    public static function factoryUnderDim(
        int | string $dimensionsNr,
        string $dimensionsNamn,
        int | string $superDimNr
    ) : self
    {
        $instance = new self();
        $instance->setDimensionNr( $dimensionsNr );
        $instance->setDimensionsNamn( $dimensionsNamn );
        $instance->setSuperDimNr((int) $superDimNr );
        return $instance;
    }

    /**
     * @return null|int
     */
    public function getSuperDimNr() : ? int
    {
        return $this->superDimNr;
    }

    /**
     * @return bool
     */
    public function isSuperDimNrSet() : bool
    {
        return ( null !== $this->superDimNr );
    }

    /**
     * @param int $superDimNr
     * @return UnderDimDto
     */
    public function setSuperDimNr( int $superDimNr ) : UnderDimDto
    {
        $this->superDimNr = $superDimNr;
        return $this;
    }
}
