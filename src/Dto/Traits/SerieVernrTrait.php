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

/**
 * Properties serie and vernr, used in verDto and TransDto classes
 *
 * Also include get and is-set methods
 * Set-methods implemented in used classes
 */
trait SerieVernrTrait
{
    /**
     * @var string|null
     */
    protected ? string $serie = null;

    /**
     * @var int|null
     */
    protected ? int $vernr = null;

    /**
     * Return serie
     *
     * @return null|string
     */
    public function getSerie() : ? string
    {
        return $this->serie;
    }

    /**
     * Return bool true if serie is set
     *
     * @return bool
     */
    public function isSerieSet() : bool
    {
        return ( null !== $this->serie );
    }

    /**
     * Return vernr
     *
     * @return null|int
     */
    public function getVernr() : ? int
    {
        return $this->vernr;
    }

    /**
     * Return bool true if vernr is set
     *
     * @return bool
     */
    public function isVernrSet() : bool
    {
        return ( null !== $this->vernr );
    }
}
