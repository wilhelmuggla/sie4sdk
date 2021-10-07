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

use Kigkonsult\Sie4Sdk\Dto\Traits\ArsnrTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\KontoNrTrait;
use Kigkonsult\Sie4Sdk\Dto\Traits\KvantitetTrait;

use Kigkonsult\Sie4Sdk\Util\Assert;
use function get_called_class;
use function strcmp;

/**
 * Class BalansDto
 */
class BalansDto implements KontoNrInterface
{

    use ArsnrTrait;

    use KontoNrTrait;

    /**
     * @var float
     */
    protected $saldo = null;

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
        $kontoNrA = $a->getKontoNr();
        $kontoNrB = $b->getKontoNr();
        if( $kontoNrA < $kontoNrB ) {
            return -1;
        }
        if( $kontoNrA > $kontoNrB ) {
            return 1;
        }
        return strcmp((string) $a->getArsnr(), (string) $b->getArsnr());
    }

    /**
     * Class factory method, arsnr, kontoNr, saldo, kvantitet
     *
     * @param int|string  $arsnr
     * @param int|string  $kontoNr
     * @param int|float|string $saldo
     * @param null|float  $kvantitet
     * @return self
     */
    public static function factory(
        $arsnr,
        $kontoNr,
        $saldo,
        $kvantitet = null
    ) : self
    {
        $class    = get_called_class();
        $instance = new $class();
        $instance->setArsnr( $arsnr );
        Assert::isIntOrString( self::KONTONR, $kontoNr );
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
     * @return float
     */
    public function getSaldo()
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
     * @param int|float|string $saldo
     * @return self
     */
    public function setSaldo( $saldo ) : self
    {
        $this->saldo = (float) $saldo;
        return $this;
    }
}
