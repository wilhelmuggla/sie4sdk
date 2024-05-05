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

use Exception;
use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Util\Assert;

/**
 * Class PeriodDto
 */
class PeriodDto extends BalansObjektDto
{
    /**
     * @var string|null  ÅÅÅÅMM
     */
    private ?string $period = null;

    /**
     * Return period
     *
     * @return string|null
     */
    public function getPeriod() : ?string
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
    public function setPeriod( int|string $period ) : self
    {
        static $PERIOD = 'period';
        try {
            Assert::isIntegerish( $PERIOD, $period );
            Assert::isYYYYMMDate( $PERIOD, $period );
        }
        catch( Exception $e ) {
            throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
        }
        $this->period = (string) $period;
        return $this;
    }
}
