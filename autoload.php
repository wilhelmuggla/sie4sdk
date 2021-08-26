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
 *            along with SieSdk. If not, see <https://www.gnu.org/licenses/>.
 */
/**
 * Kigkonsult\Sie4Sdk autoloader
 */
spl_autoload_register(
    function( $class ) {
        static $PREFIX   = 'Kigkonsult\\Sie4Sdk\\';
        static $BS       = '\\';
        static $PATHSRC  = null;
        static $SRC      = 'src';
        static $PATHTEST = null;
        static $TEST     = 'test';
        static $FMT      = '%s%s.php';
        if( empty( $PATHSRC )) {
            $PATHSRC  = __DIR__ . DIRECTORY_SEPARATOR . $SRC . DIRECTORY_SEPARATOR;
            $PATHTEST = __DIR__ . DIRECTORY_SEPARATOR . $TEST . DIRECTORY_SEPARATOR;
        }
        if( 0 != strncmp( $PREFIX, $class, 19 )) {
            return;
        }
        $class = substr( $class, 19 );
        if( false !== strpos( $class, $BS )) {
            $class = str_replace( $BS, DIRECTORY_SEPARATOR, $class );
        }
        $file = sprintf( $FMT, $PATHSRC, $class );
        if( file_exists( $file )) {
            include $file;
        }
        else {
            $file = sprintf( $FMT, $PATHTEST, $class );
            if( file_exists( $file )) {
                include $file;
            }
        }
    }
);
