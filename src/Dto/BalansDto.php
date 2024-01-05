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

use Kigkonsult\Sie4Sdk\Dto\Traits\ArsnrTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\KontoNrTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\KvantitetTrait;
use Kigkonsult\Sie4Sdk\Util\StringUtil;

/**
 * Class BalansDto
 */
class BalansDto implements KontoNrInterface
{

    use ArsnrTrait;

    use KontoNrTrait;

    /**
     * @var float|null
     */
    protected ?float $saldo = null;

    use KvantitetTrait;

    /**
     * @var callable
     */
    static public $SORTER = [ self::class, 'balansSorter' ];

    /**
     * Sort BalansDto[] on kontonr, arsnr
     *
     * @param BalansDto $a
     * @param BalansDto $b
     * @return int
     */
    public static function balansSorter( BalansDto $a, BalansDto $b ) : int
    {
        if( 0 !== ( $res = StringUtil::strSort((string) $a->getKontoNr(),(string) $b->getKontoNr() ))) {
            return $res;
        }
        return StringUtil::strSort((string) $a->getArsnr(), (string) $b->getArsnr());
    }

    /**
     * Class factory method, arsnr, kontoNr, saldo, kvantitet
     *
     * @param int|string $arsnr
     * @param int|string $kontoNr
     * @param float|int|string $saldo
     * @param float|null $kvantitet
     * @return self
     * @since 1.8.2 2023-09-20
     */
    public static function factory(
        int | string         $arsnr,
        int | string         $kontoNr,
        float | int | string $saldo,
        float                $kvantitet = null
    ) : self
    {
        $class    = static::class;
        $instance = new $class();
        $instance->setArsnr( $arsnr );
        $instance->setKontoNr( $kontoNr );
        $instance->setSaldo( $saldo );
        if( null !== $kvantitet ) {
            $instance->setKvantitet( $kvantitet );
        }
        return $instance;
    }

    /**
     * Return saldo
     *
     * @return float|null
     */
    public function getSaldo() : ?float
    {
        return $this->saldo;
    }

    /**
     * Return bool true if saldo is set
     *
     * @return bool
     */
    public function isSaldoSet() : bool
    {
        return ( null !== $this->saldo );
    }

    /**
     * Set saldo
     *
     * @param float|int|string $saldo    saved as float
     * @return self
     */
    public function setSaldo( float | int | string $saldo ) : self
    {
        $this->saldo = (float) $saldo;
        return $this;
    }
}
