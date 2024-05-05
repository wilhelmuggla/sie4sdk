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

use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\Traits\ArsnrTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\KontoNrTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\KvantitetTrait;
use Kigkonsult\Sie4Sdk\Util\Assert;

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
    protected ? float $saldo = null;

    use KvantitetTrait;

    /**
     * Class factory method, arsnr, kontoNr, saldo, kvantitet
     *
     * @param int|string $arsnr
     * @param int|string $kontoNr
     * @param float|int|string $saldo
     * @param null|int|float|string $kvantitet
     * @return static
     * @since 1.8.2 2023-09-20
     */
    public static function factory(
        int|string       $arsnr,
        int|string       $kontoNr,
        float|int|string $saldo,
        null|int|float|string $kvantitet = null
    ) : static
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
     * @param int|float|string $saldo    saved as float
     * @return static
     * @throws InvalidArgumentException
     */
    public function setSaldo( int|float|string $saldo ) : static
    {
        Assert::isfloatish( __FUNCTION__ , $saldo );
        $this->saldo = (float) $saldo;
        return $this;
    }
}
