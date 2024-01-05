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
namespace Kigkonsult\Sie4Sdk\Dto\Traits;

trait KvantitetTrait
{
    /**
     * @var float|null
     */
    protected ?float $kvantitet = null;

    /**
     * Return kvantitet
     *
     * @return null|float
     */
    public function getKvantitet() : ? float
    {
        return $this->kvantitet;
    }

    /**
     * Return bool true if kvantitet is set
     *
     * @return bool
     */
    public function isKvantitetSet() : bool
    {
        return ( null !== $this->kvantitet );
    }

    /**
     * Set kvantitet
     *
     * @param int|float|string $kvantitet
     * @return static
     */
    public function setKvantitet( int|float|string $kvantitet ) : static
    {
        $this->kvantitet = (float) $kvantitet;
        return $this;
    }
}
