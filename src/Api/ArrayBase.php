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
namespace Kigkonsult\Sie4Sdk\Api;

use Kigkonsult\Sie4Sdk\Sie4Interface;

abstract class ArrayBase implements Sie4Interface
{
    /**
     * @var string[][]
     */
    protected static array $TRANSKEYS = [
        self::TRANS => [
            self::TRANSTIMESTAMP   => self::TRANSTIMESTAMP,
            self::TRANSGUID        => self::TRANSGUID,
            self::TRANSPARENTGUID  => self::TRANSPARENTGUID,
            self::TRANSKONTONR     => self::TRANSKONTONR,
            self::TRANSDIMENSIONNR => self::TRANSDIMENSIONNR,
            self::TRANSOBJEKTNR    => self::TRANSOBJEKTNR,
            self::TRANSBELOPP      => self::TRANSBELOPP,
            self::TRANSDAT         => self::TRANSDAT,
            self::TRANSTEXT        => self::TRANSTEXT,
            self::TRANSKVANTITET   => self::TRANSKVANTITET,
            self::TRANSSIGN        => self::TRANSSIGN,
        ],
        self::RTRANS => [
            self::TRANSTIMESTAMP   => self::RTRANSTIMESTAMP,
            self::TRANSGUID        => self::RTRANSGUID,
            self::TRANSPARENTGUID  => self::RTRANSPARENTGUID,
            self::TRANSKONTONR     => self::RTRANSKONTONR,
            self::TRANSDIMENSIONNR => self::RTRANSDIMENSIONNR,
            self::TRANSOBJEKTNR    => self::RTRANSOBJEKTNR,
            self::TRANSBELOPP      => self::RTRANSBELOPP,
            self::TRANSDAT         => self::RTRANSDAT,
            self::TRANSTEXT        => self::RTRANSTEXT,
            self::TRANSKVANTITET   => self::RTRANSKVANTITET,
            self::TRANSSIGN        => self::RTRANSSIGN,
        ],
        self::BTRANS => [
            self::TRANSTIMESTAMP   => self::BTRANSTIMESTAMP,
            self::TRANSGUID        => self::BTRANSGUID,
            self::TRANSPARENTGUID  => self::BTRANSPARENTGUID,
            self::TRANSKONTONR     => self::BTRANSKONTONR,
            self::TRANSDIMENSIONNR => self::BTRANSDIMENSIONNR,
            self::TRANSOBJEKTNR    => self::BTRANSOBJEKTNR,
            self::TRANSBELOPP      => self::BTRANSBELOPP,
            self::TRANSDAT         => self::BTRANSDAT,
            self::TRANSTEXT        => self::BTRANSTEXT,
            self::TRANSKVANTITET   => self::BTRANSKVANTITET,
            self::TRANSSIGN        => self::BTRANSSIGN,
        ],
    ];
}
