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
namespace Kigkonsult\Sie4Sdk\Dto\Traits;

use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Kigkonsult\Sie4Sdk\Util\Assert;

trait KontoNrTrait
{
    /**
     * @var string|null
     */
    protected ?string $kontoNr = null;

    /**
     * Return kontoNr
     *
     * @return null|string
     */
    public function getKontoNr() : ? string
    {
        return $this->kontoNr;
    }

    /**
     * Return bool true if kontoNr is set
     *
     * @return bool
     */
    public function isKontoNrSet() : bool
    {
        return ( null !== $this->kontoNr );
    }

    /**
     * Set kontoNr
     *
     * @param int|string $kontoNr
     * @return static
     * @throws InvalidArgumentException
     */
    public function setKontoNr( int|string $kontoNr ) : static
    {
        Assert::isIntegerish( Sie4Interface::KONTONR, $kontoNr );
        $this->kontoNr = (string) $kontoNr;
        return $this;
    }
}
