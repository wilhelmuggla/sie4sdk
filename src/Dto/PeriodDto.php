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

use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Sie4Validator;

/**
 * Class BalansDto
 */
class PeriodDto extends BalansObjektDto
{
    /**
     * @var null|string  ÅÅÅÅMM
     */
    private $period = null;

    /**
     * @var string[]
     */
    public static $SORTER = [ PeriodDto::class, 'periodSorter' ];

    /**
     * Sort PeriodDto[] on kontonr, arsnr, dimensionNr, objektNr
     *
     * @param PeriodDto $a
     * @param PeriodDto $b
     * @return int
     */
    public static function periodSorter( PeriodDto $a, PeriodDto $b ) : int
    {
        return parent::balansObjektSorter( $a, $b );
    }

    /**
     * Return bool true is period is set
     *
     * @return null|string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @return bool
     */
    public function isPeriodSet() : bool
    {
        return ( null !== $this->period );
    }

    /**
     * Set period ÅÅÅÅMM
     *
     * @param int|string $period
     * @return self
     * @throws InvalidArgumentException
     */
    public function setPeriod( $period ) : self
    {
        static $PERIOD = 'period';
        Sie4Validator::assertIntegerish( $PERIOD, $period );
        Sie4Validator::assertYYYYMMDate( $PERIOD, $period );
        $this->period = (string) $period;
        return $this;
    }
}
