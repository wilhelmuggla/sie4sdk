<?php
/**
 * Sie4Sdk   PHP Sie4 SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Sdk
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021-2023 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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

/**
 * Properties fnrid, orgnr(+multiple), used in BaseId (Sie4Dto, VerDto and TransDto) and IdDto
 *
 * Includes get and is-set methods
 * Set-methods implemented in used classes or usage of FnrIdOrgnr2Trait
 */
trait FnrIdOrgnrTrait
{
    /**
     * @var string|null
     */
    protected ?string $fnrId = null;

    /**
     * @var string|null
     */
    protected ?string $orgnr = null;

    /**
     * @var int  default 1
     */
    protected int $multiple = 1;

    /**
     * Return fnrId
     *
     * @return null|string
     */
    public function getFnrId() : ? string
    {
        return $this->fnrId;
    }

    /**
     * Return bool true if fnr (company id) is set
     *
     * @return bool
     */
    public function isFnrIdSet() : bool
    {
        return ( null !== $this->fnrId );
    }

    /**
     * Return orgnr
     *
     * @return null|string
     */
    public function getOrgnr() : ? string
    {
        return $this->orgnr;
    }

    /**
     * Return bool true if orgnr is set
     *
     * @return bool
     */
    public function isOrgnrSet() : bool
    {
        return ( null !== $this->orgnr );
    }

    /**
     * Return multiple (default 1)
     *
     * @return int
     */
    public function getMultiple() : int
    {
        return $this->multiple;
    }
}
