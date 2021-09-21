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
use Kigkonsult\Sie4Sdk\Sie4Validator;

trait ArsnrTrait
{
    /**
     * @var int
     */
    protected $arsnr = null;

    /**
     * @return int
     */
    public function getArsnr()
    {
        return $this->arsnr;
    }

    /**
     * @return bool
     */
    public function isArsnrSet() : bool
    {
        return ( null !== $this->arsnr );
    }

    /**
     * @param int|string $arsnr
     * @return self
     * @throws InvalidArgumentException
     */
    public function setArsnr( $arsnr ) : self
    {
        static $ARSNR = 'ASRNR';
        Sie4Validator::assertIntegerish( $ARSNR, $arsnr );
        $this->arsnr  = (int) $arsnr;
        return $this;
    }
}
