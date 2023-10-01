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
namespace Kigkonsult\Sie4Sdk\Dto;

use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\Traits\KontoNrTrait;
use Kigkonsult\Sie4Sdk\Util\Assert;
use Kigkonsult\Sie4Sdk\Util\StringUtil;

/**
 * Class SruDto
 */
class SruDto implements DtoInterface
{
    use KontoNrTrait;

    /**
     * @var int|null
     */
    private ?int $sruKod = null;

    /**
     * @var callable
     */
    public static $SORTER = [ self::class, 'sruSorter' ];

    /**
     * Sort SruDto[] on kontonr, sruKod
     *
     * @param SruDto $a
     * @param SruDto $b
     * @return int
     */
    public static function sruSorter( SruDto $a, SruDto $b ) : int
    {
        if( 0 !== ( $res = StringUtil::strSort((string) $a->getKontoNr(),(string) $b->getKontoNr()))) {
            return $res;
        }
        return StringUtil::strSort((string) $a->getSruKod(), (string) $b->getSruKod());
    }

    /**
     * Class factory method, kontoNr/Namn/Typ, enhet opt
     *
     * @param int|string $kontoNr
     * @param int|string $sruKod
     * @return self
     */
    public static function factory( int | string $kontoNr, int | string $sruKod ) : self
    {
        $instance = new self();
        $instance->setKontoNr( $kontoNr );
        $instance->setSruKod( $sruKod );
        return $instance;
    }

    /**
     * Return sruKod
     *
     * @return int|null
     */
    public function getSruKod() : ?int
    {
        return $this->sruKod;
    }

    /**
     * Return bool true if sruKod is set
     *
     * @return bool
     */
    public function isSruKodSet() : bool
    {
        return ( null !== $this->sruKod );
    }

    /**
     * Set sruKod
     *
     * @param int|string $sruKod
     * @return self
     * @throws InvalidArgumentException
     */
    public function setSruKod( int | string $sruKod ) : self
    {
        Assert::isIntegerish( self::SRU, $sruKod );
        $this->sruKod = (int) $sruKod;
        return $this;
    }
}
