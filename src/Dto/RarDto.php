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
namespace Kigkonsult\Sie4Sdk\Dto;

use DateTime;
use Kigkonsult\Sie4Sdk\Dto\Traits\ArsnrTrait;

use function strcmp;

class RarDto implements DtoInterface
{
    use ArsnrTrait;

    /**
     * @var null|DateTime
     */
    private $start = null;

    /**
     * @var null|DateTime
     */
    private $slut = null;

    /**
     * Sort RarDto[] on arsnr, descending
     *
     * @param RarDto $a
     * @param RarDto $b
     * @return int
     */
    public static function sorter( RarDto $a, RarDto $b ) : int
    {
        return strcmp((string) $b->getArsnr(), (string) $a->getArsnr());
    }

    /**
     * Class factory method, arsnr, start, slut
     *
     * @param int|string $arsnr
     * @param DateTime $start
     * @param DateTime $slut
     * @return self
     */
    public static function factory( $arsnr, DateTime $start, DateTime $slut ) : self
    {
        $instance = new self();
        $instance->setArsnr( $arsnr );
        $instance->setStart( $start );
        $instance->setSlut( $slut );
        return $instance;

    }

    /**
     * @return null|DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return bool
     */
    public function isStartSet() : bool
    {
        return ( null !== $this->start );
    }

    /**
     * @param DateTime $start
     * @return self
     */
    public function setStart( DateTime $start ) : self
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return null|DateTime
     */
    public function getSlut()
    {
        return $this->slut;
    }

    /**
     * @return bool
     */
    public function isSlutSet() : bool
    {
        return ( null !== $this->slut );
    }

    /**
     * @param DateTime $slut
     * @return self
     */
    public function setSlut( DateTime $slut ) : self
    {
        $this->slut = $slut;
        return $this;
    }
}
