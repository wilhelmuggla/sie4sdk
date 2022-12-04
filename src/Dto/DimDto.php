<?php
/**
 * Sie4Sdk   PHP Sie4 SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Sdk
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
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

use Kigkonsult\Sie4Sdk\Dto\Traits\DimensionNrTrait;
use Kigkonsult\Sie4Sdk\Util\StringUtil;

class DimDto implements DimensionNrInterface
{
    use DimensionNrTrait;

    /**
     * @var string|null
     */
    protected ?string $dimensionsNamn = null;

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
     * Class factory method, set dimensionNr and dimensionsNamn
     *
     * @param int|string $dimensionsNr
     * @param string $dimensionsNamn
     * @return static
     */
    public static function factoryDim( int | string $dimensionsNr, string $dimensionsNamn ) : static
    {
        $instance = new self();
        $instance->setDimensionNr( $dimensionsNr );
        $instance->setDimensionsNamn( $dimensionsNamn );
        return $instance;
    }

    /**
     * Return dimensionsNamn
     *
     * @return string|null
     */
    public function getDimensionsNamn() : ?string
    {
        return $this->dimensionsNamn;
    }

    /**
     * Return bool true if dimensionsNamn is set
     *
     * @return bool
     */
    public function isDimensionsNamnSet() : bool
    {
        return ( null !== $this->dimensionsNamn );
    }

    /**
     * Set dimensionsNamn
     *
     * @param string $dimensionsNamn
     * @return static
     */
    public function setDimensionsNamn( string $dimensionsNamn ) : static
    {
        $this->dimensionsNamn = $dimensionsNamn;
        return $this;
    }
}
