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

use Kigkonsult\Sie4Sdk\Dto\Traits\DimensionNrTrait;

/**
 * Class DimDto
 *
 * Följande dimensionsnummer är reserverade:
 * 1     = Kostnadsställe / resultatenhet.
 * 2     = Kostnadsbärare (skall vara underdimension till 1).
 * 3-5   = Reserverade för framtida utökning av standarden.
 * 6     = Projekt.
 * 7     = Anställd.
 * 8     = Kund.
 * 9     = Leverantör.
 * 10    = Faktura.
 * 11-19 = Reserverade för framtida utökning av standarden.
 * 20-   = Fritt disponibla.
 */
class DimDto implements DimensionNrInterface
{
    use DimensionNrTrait;

    /**
     * @var string|null
     */
    protected ? string $dimensionNamn = null;

    /**
     * Class factory method, set dimensionNr and dimensionsNamn
     *
     * @param int|string $dimensionsNr
     * @param string $dimensionsNamn
     * @return static
     */
    public static function factoryDim( int | string $dimensionsNr, string $dimensionsNamn ) : static
    {
        $class    = static::class;
        $instance = new $class();
        $instance->setDimensionNr( $dimensionsNr );
        $instance->setDimensionNamn( $dimensionsNamn );
        return $instance;
    }

    /**
     * Return dimensionsNamn
     *
     * @return string|null
     */
    public function getDimensionNamn() : ?string
    {
        return $this->dimensionNamn;
    }

    /**
     * Return bool true if dimensionsNamn is set
     *
     * @return bool
     */
    public function isDimensionsNamnSet() : bool
    {
        return ( null !== $this->dimensionNamn );
    }

    /**
     * Set dimensionsNamn
     *
     * @param string $dimensionNamn
     * @return static
     */
    public function setDimensionNamn( string $dimensionNamn ) : static
    {
        $this->dimensionNamn = $dimensionNamn;
        return $this;
    }
}
