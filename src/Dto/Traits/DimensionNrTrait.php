<?php
/**
 * Sie4Sdk   PHP Sie4 SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Sdk
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
namespace Kigkonsult\Sie4Sdk\Dto\Traits;

use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Kigkonsult\Sie4Sdk\Sie4Validator;

trait DimensionNrTrait
{
    /**
     * @var int
     */
    protected $dimensionNr = null;

    /**
     * Return dimensionNr
     *
     * @return null|int
     */
    public function getDimensionNr()
    {
        return $this->dimensionNr;
    }

    /**
     * Return bool true if dimensionNr is set
     *
     * @return bool
     */
    public function isDimensionsNrSet() : bool
    {
        return ( null !== $this->dimensionNr );
    }

    /**
     * Set dimensionNr
     *
     * @param int|string $dimensionNr
     * @return self
     * @throws InvalidArgumentException
     */
    public function setDimensionNr( $dimensionNr ) : self
    {
        Sie4Validator::assertIntegerish( Sie4Interface::DIMENSIONNR, $dimensionNr );
        $this->dimensionNr = (int) $dimensionNr;
        return $this;
    }
}
