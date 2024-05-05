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
use Kigkonsult\Sie4Sdk\Dto\Traits\FnrIdOrgnrTrait;
use Kigkonsult\Sie4Sdk\Util\GuidUtil;
use Kigkonsult\Sie4Sdk\Util\StringUtil;

use function microtime;

/**
 * Baseclass for Sie4Dto, VerDto and TransDto, provides unique timestamp and guid
 * as well as FnrId and orgnr, used with timestamp and guid to uniquely identify instance
 */
abstract class BaseId implements DtoInterface
{
    /**
     * Current Unix timestamp with microseconds, default 'microtime( true)' at instance create
     *
     * @var float
     */
    protected float $timestamp;

    /**
     * Unique (random) guid, default set at instance create
     *
     * Autoloaded guid without surrounding brackets
     * using GuidUtil::getGuid()
     *
     * @var string
     */
    protected string $correlationId;

    /**
     * FnrId and orgnr(+multiple), used with timestamp and guid to uniquely identify instance
     */
    use FnrIdOrgnrTrait;

    /**
     * Class constructor
     *
     * @param mixed|null $arg   used in child classs
     * @throws InvalidArgumentException
     */
    public function __construct( mixed $arg = null )
    {
        $this->setTimestamp( microtime( true ));
        try {
            $this->setCorrelationId( GuidUtil::getGuid() );
        }
        catch( Exception $e ) {
            throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
        }
    }

    /**
     * @return float
     */
    public function getTimestamp() : float
    {
        return $this->timestamp;
    }

    /**
     * @param float $timestamp
     * @return static
     */
    public function setTimestamp( float $timestamp ) : static
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getCorrelationId() : string
    {
        return $this->correlationId;
    }

    /**
     * Load guid (without validation)
     *
     * Use GuidUtil::assertGuid() for validation
     *
     * @param string $correlationId
     * @return static
     */
    public function setCorrelationId( string $correlationId ) : static
    {
        $this->correlationId = StringUtil::trimBrackets( $correlationId );
        return $this;
    }
}
