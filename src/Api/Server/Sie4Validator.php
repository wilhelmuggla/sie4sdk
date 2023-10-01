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
namespace Kigkonsult\Sie4Sdk\Api\Server;

use Comet\Validator;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Rakit\Validation\Rule;

use function date_create;
use function filter_var;
use function is_array;
use function is_numeric;
use function sprintf;

/**
 * class Sie4Validator
 *
 * Validates (http) Sie4 array input, minimal VER (and TRANS)
 * FTGNAMN and ORGNRORGNR required on global level
 * VERDATUM and VERNR required for each VER
 * TRANSKONTONR and TRANSBELOPP required for each TRANS
 *
 * Custom rule classes at the end
 *
 * @since 1.8.4 20230927
 */
class Sie4Validator implements Sie4Interface
{
    /**
     * @var string[]
     */
    private static array $RULES = [
        self::FTGNAMN           => 'required',
        self::ORGNRORGNR        => 'required',
        self::VERDATUM          => 'required|date1',
        self::VERSERIE          => 'nullable|array',
        self::VERNR             => 'required|int1',
        self::VERTEXT           => 'nullable|array',
        self::REGDATUM          => 'nullable|array|date1',
        self::VERSIGN           => 'nullable|array',
        self::TRANSKONTONR      => 'required|int2',
        self::TRANSBELOPP       => 'required|numeric2',
        self::TRANSDIMENSIONNR  => 'nullable|array3int',
        self::TRANSOBJEKTNR     => 'nullable|array3',
        self::TRANSDAT          => 'nullable|date2',
        self::TRANSTEXT         => 'nullable|array2',
        self::TRANSKVANTITET    => 'nullable|numeric2',
        self::TRANSSIGN         => 'nullable|array2',
    ];

    /**
     * Validates an 'http' input Sie4 array
     *
     * @param string[] $input
     * @param string|null $msg
     * @return bool
     */
    public static function validateArray( array $input, ? string & $msg = null ) : bool
    {
        static $SP0 = '';
        static $SP1 = ' ';
        static $CLN = ',';
        $validator  = new Validator;
        $validator->addValidator('date1',     new Date1());
        $validator->addValidator('int1',      new Int1());
        $validator->addValidator('array2',    new Array2());
        $validator->addValidator('array3',    new Array3());
        $validator->addValidator('array3int', new Array3int());
        $validator->addValidator('int2',      new Int2());
        $validator->addValidator('date2',     new Date2());
        $validator->addValidator('numeric2',  new Numeric2());
        $validation = $validator->validate( $input, self::$RULES );
        $errors     = $validation->getErrors();
        if( empty( $errors )) {
            return true;
        }
        $msg     = $SP0;
        foreach( $errors as $field => $txt ) {
            if( is_array( $txt )) {
                $txt = implode( $CLN, $txt );
            }
            $msg = $field . $SP1 . $txt;
        } // end foreach
        return false;
    }
}

/**
 * class Array2
 *
 * Check value is a two-dim array
 */
class Array2 extends Rule
{
    /**
     * The rule check
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool
    {
        static $err1   = " must be an array";
        static $err2   = "[%s] must be an array";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx => $value2 ) {
            if( ! is_array( $value2 )) {
                $this->message = sprintf( $err2, $vx );
                return false;
            }
        } // end foreach
        return true;
    }
}

/**
 * class Array3
 *
 * Check value is a three-dim array
 */
class Array3 extends Rule
{
    /**
     * The rule check
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool
    {
        static $err1   = " must be an array";
        static $err2   = "[%s] must be an array";
        static $err3   = "[%s][%s] must be an array";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx1 => $value2 ) {
            if( ! is_array( $value2 )) {
                $this->message = sprintf( $err2, $vx1 );
                return false;
            }
            foreach( $value2 as $vx2 => $value3 ) {
                if( ! is_array( $value3 )) {
                    $this->message = sprintf( $err3, $vx1, $vx2 );
                    return false;
                }
            } // end foreach
        } // end foreach
        return true;
    }
}

/**
 * class Array3int
 *
 * Check value is a three-dim array with int elements
 */
class Array3int extends Rule
{
    /**
     * The rule check
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool
    {
        static $err1   = " must be an array";
        static $err2   = "[%s] must be an array";
        static $err3   = "[%s][%s] must be an array";
        static $err4   = "[%s][%s][%s] must be an int";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx1 => $value2 ) {
            if( ! is_array( $value2 )) {
                $this->message = sprintf( $err2, $vx1 );
                return false;
            } // end foreach
            foreach( $value2 as $vx2 => $value3 ) {
                if( ! is_array( $value3 )) {
                    $this->message = sprintf( $err3, $vx1, $vx2 );
                    return false;
                }
                foreach( $value3 as $vx3 => $value4 ) {
                    if( false === filter_var( $value4, FILTER_VALIDATE_INT )) {
                        $this->message = sprintf( $err4, $vx1, $vx2, $vx3 );
                        return false;
                    }
                } // end foreach
            } // end foreach
        } // end foreach
        return true;
    }
}

/**
 * class Date1
 *
 * Check value is an array with any Ymd-date elements
 */
class Date1 extends Rule
{
    /**
     * The rule check
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool
    {
        static $err1   = " must be an array";
        static $err2   = "[%s] must be any Ymd-date";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx => $value2 ) {
            if( false === date_create( $value2 )) {
                $this->message = sprintf( $err2, $vx );
                return false;
            }
        } // end foreach
        return true;
    }
}
/**
 * class Int1
 *
 * Check value is an array with int elements
 */
class Int1 extends Rule
{
    /**
     * The rule check
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool
    {
        static $err1   = " must be an array";
        static $err2   = "[%s] must be an int";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx => $value2 ) {
            if( false === filter_var( $value2, FILTER_VALIDATE_INT )) {
                $this->message = sprintf( $err2, $vx );
                return false;
            }
        } // end foreach
        return true;
    }
}

/**
 * class int2
 *
 * Check value is a two-dim array with int elements
 */
class Int2 extends Rule
{
    /**
     * The rule check
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool
    {
        static $err1   = " must be an array";
        static $err2   = "[%s] must be an array";
        static $err3   = "[%s][%s] must be an int";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx1 => $value2 ) {
            if( ! is_array( $value2 )) {
                $this->message = sprintf( $err2, $vx1 );
                return false;
            }
            foreach( $value2 as $vx2 =>$value3 ) {
                if( false === filter_var( $value3, FILTER_VALIDATE_INT )) {
                    $this->message = sprintf( $err3, $vx1, $vx2 );
                    return false;
                }
            } // end foreach
        } // end foreach
        return true;
    }
}

/**
 * class Numeric2
 *
 * Check value is a two-dim array with numeric elements
 */
class Numeric2 extends Rule
{
    /**
     * The rule check
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool
    {
        static $err1   = " must be an array";
        static $err2   = "[%s] must be an array";
        static $err3   = "[%s][%s] must be numeric";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx1 => $value2 ) {
            if( ! is_array( $value2 )) {
                $this->message = sprintf( $err2, $vx1 );
                return false;
            }
            foreach( $value2 as $vx2 =>$value3 ) {
                if( ! is_numeric( $value3 )) {
                    $this->message = sprintf( $err3, $vx1, $vx2 );
                    return false;
                }
            } // end foreach
        } // end foreach
        return true;
    }
}

/**
 * class Date2
 *
 * Check value is a two-dim array with any Ymd-date elements
 */
class Date2 extends Rule
{
    /**
     * The rule check
     *
     * @param mixed $value
     * @return bool
     */
    public function check( mixed $value ): bool
    {
        static $err1   = " must be an array";
        static $err2   = "[%s] must be an array";
        static $err3   = "[%s][%s] must be any Ymd-date";
        if( ! is_array( $value )) {
            $this->message = $err1;
            return false;
        }
        foreach( $value as $vx1 =>$value2 ) {
            if( ! is_array( $value2 )) {
                $this->message = sprintf( $err2, $vx1 );
                return false;
            }
            foreach( $value2 as $vx2 => $value3 ) {
                if( false === date_create( $value3 )) {
                    $this->message = sprintf( $err3, $vx1, $vx2 );
                    return false;
                }
            } // end foreach
        } // end foreach
        return true;
    }
}
