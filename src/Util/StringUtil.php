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
namespace Kigkonsult\Sie4Sdk\Util;

use Exception;
use RuntimeException;

use function explode;
use function iconv;
use function sprintf;
use function strlen;
use function strpos;
use function strrev;
use function str_replace;
use function substr;
use function trim;

class StringUtil
{
    /**
     * @var string
     */
    public static string $ZERO = '0';

    /**
     * @var string
     */
    public static string $SP0  = '';

    /**
     * @var string
     */
    public static string $DOT  = '.';

    /**
     * @var string
     */
    public static string $SP1  = ' ';

    /**
     * @var string[]
     */
    public static array $CURLYBRACKETS = [ '{', '}' ];

    /**
     * @var string
     */
    public static string $QUOTE = '"';

    /**
     * @var string
     */
    public static string $DOUBLEQUOTE = '""';

    /**
     * Encoding charsets
     *
     * @var string
     */
    private static string $UTF8     = 'UTF-8';

    /**
     * @var string
     */
    private static string $CP437    = 'CP437';

    /**
     * @var string
     */
    // private static $TRANSLIT = '//TRANSLIT';

    /**
     * @var string
     */
    // private static $IGNORE   = '//IGNORE';

    /**
     * Error text
     *
     * @var string
     */
    private static string $FMT1  = 'Error (%s) converting from %s to %s : %s';

    /**
     * Return trimmed utf8 string in PC437
     *
     * @param string $string
     * @return string
     * @throws RuntimeException
     */
    public static function utf8toCP437( string $string ) : string
    {
        $string = trim( $string );
        if( empty( $string )) {
            return $string;
        }
        $msg = self::$SP0;
        try {
//          $output = iconv( self::$UTF8, self::$CP437 . self::$IGNORE, $string );
            $output = iconv( self::$UTF8, self::$CP437, $string );
        }
        catch( Exception $e ) {
            $output = false;
            $msg = $e->getMessage();
        }
        if( false === $output ) {
            throw new RuntimeException(
//              sprintf( self::$FMT1, $msg, self::$UTF8, self::$CP437 . self::$IGNORE, $string ),
                sprintf( self::$FMT1, $msg, self::$UTF8, self::$CP437, $string ),
                14111
            );
        }
        return $output;
    }

    /**
     * @param string $string
     * @return string
     * @throws RuntimeException
     */
    public static function cp437toUtf8( string $string ) : string
    {
        if( empty( $string )) {
            return $string;
        }
        $msg = self::$SP0;
        try {
//          $output = iconv( self::$CP437, self::$UTF8 . self::$IGNORE, $string );
            $output = iconv( self::$CP437, self::$UTF8, $string );
        }
        catch( Exception $e ) {
            $output = false;
            $msg = $e->getMessage();
        }
        if( false === $output ) {
            throw new RuntimeException(
//              sprintf( self::$FMT1, $msg, self::$CP437, self::$UTF8 . self::$IGNORE, $string ),
                sprintf( self::$FMT1, $msg, self::$CP437, self::$UTF8, $string ),
                14211
            );
        }
        return trim( $output );
    }

    /**
     * Convert all EOLs to PHP_EOL, alter double eols to single ones
     *
     * @param string $string
     * @return string
     */
    public static function convEolChar( string $string ) : string
    {
        static $CRLFs = [ "\r\n", "\n\r", "\n", "\r" ];
        static $EOL2  = PHP_EOL . PHP_EOL;
        /* fix eol chars */
        $string = str_replace( $CRLFs, PHP_EOL, $string );
        while( false !== strpos( $string, $EOL2 )) {
            $string = str_replace( $EOL2, PHP_EOL, $string );
        } // end while
        return $string;
    }

    /**
     * Explode string to array using PHP_EOL as separator
     *
     * @param string $string
     * @return string[]
     */
    public static function string2Arr( string $string ) : array
    {
        return explode( PHP_EOL, $string );
    }

    /**
     * Converts tab-char to space
     *
     * @param string $string
     * @return string
     */
    public static function tab2Space( string $string ) : string
    {
        static $TAB  = '\t';
        return str_replace( $TAB, self::$SP1, $string );
    }

    /**
     * Convert multi-space to single-space, return trimmed string
     *
     * @param string $string
     * @return string
     */
    public static function trimString( string $string ) : string
    {
        static $SP2 = '  ';
        while( self::isIn( $SP2, $string )) {
            $string = str_replace( $SP2, self::$SP1, $string );
        } // end while
        return trim( $string );
    }

    /**
     * Return quoted string, inline quotes prepended by backslash
     *
     * @param string $string
     * @return string
     */
    public static function quoteString( string $string ) : string
    {
        static $BSQ = '\\"';
        if( self::isIn( self::$QUOTE, $string )) {
            $string = str_replace( self::$QUOTE, $BSQ, $string );
        }
        return self::$QUOTE . $string . self::$QUOTE;
    }

    /**
     * Return string surrounded by curly brackets
     *
     * @param string $string
     * @return string
     */
    public static function curlyBacketsString( string $string ) : string
    {
        return self::$CURLYBRACKETS[0] . trim( $string ) . self::$CURLYBRACKETS[1];
    }

    /**
     * Rtrim trailing ' ""' from (output) Sie4-row
     *
     * @param string $string
     * @return string
     */
    public static function d2qRtrim( string $string ) : string
    {
        static $BSQQ = '\\""';
        while(( self::$DOUBLEQUOTE === substr( $string, -2 )) &&
            ( $BSQQ !== substr( $string, -3 ))) {
            $string = substr( $string, 0, -3 );
        }
        return $string;
    }

    /**
     * Split post on label and label data, on missing leading #, all is content
     *
     * @param string $post
     * @return array       [ string, string[] ]  i.e. [ label, contentParts[] ]
     */
    public static function splitPost( string $post ) : array
    {
        static $HASH = '#';
        if( ! self::startsWith( $post, $HASH )) {
            return [ null, self::splitContent( $post ) ];
        }
        if( ! self::isIn( self::$SP1, $post )) {
            return [ $post, [] ];
        }
        $label   = self::before( self::$SP1, $post );
        $content = self::splitContent( self::after( $label . self::$SP1, $post ));
        return [ $label, $content ];
    }

    /**
     * @var int
     */
    private static int $SEP = PHP_INT_MAX;

    /**
     * Split content on space as separator, text within quotes is maintained
     *
     * Content is trimmed, i.e. leading character is NOT space
     *
     * @param string $content
     * @return string[]
     */
    private static function splitContent( string $content ) : array
    {
        $content = self::prePrepInput( $content );
        if( empty( $content )) {
            return [];
        }
        if( in_array( $content, self::$CURLYBRACKETS )) {
            return [ $content ];
        }
        $output        = [];
        $len           = strlen( $content );
        $bracketsFound = false;
        $quoteFound    = false;
        $current       = self::$SP0;
        for( $x = 0; $x < $len; $x++ ) {
            switch( true ) {
                case ( ! $bracketsFound &&
                    self::isEmptyCurlyBracketField( $content, $x )) :
                    // empty 'field' within curly brackets
                    $output[] = self::$SP0;
                    $current  = self::$SP0;
                    ++$x;
                    break;
                case in_array( $content[$x], self::$CURLYBRACKETS ) :
                    // 'field' within curly brackets start/end
                    $bracketsFound = ! $bracketsFound;
                    break;
                case ( ! $quoteFound && self::isEmptyQuotedField( $content, $x )) :
                    // empty quoted field
                    $output[] = self::$SP0;
                    $current  = self::$SP0;
                    ++$x;
                    break;
                case ( self::$QUOTE === $content[$x] ) :
                    // quoted field start/end
                    $quoteFound = ! $quoteFound;
                    break;
                case ( $bracketsFound || $quoteFound ) :
                    // any character in field, within quotes or curly brackets
                    $current .= $content[$x];
                    break;
                case ( self::$SP1 === $content[$x] ) :
                    // new field, separated by one or more spaces but not within curly brackets
                    if( ! empty( $current ) || ( self::$ZERO === $current )) {
                        // 'close' previous field
                        $output[] = trim( $current );
                        $current  = self::$SP0;
                    }
                    break;
                default :
                    $current .= $content[$x];
                    break;
            } // end switch
        } // end for
        // get last field
        if( ! empty( $current ) || ( self::$ZERO === $current )) {
            $output[] = trim( $current );
        }
        // skip leading/trailing quotes, 'restore' \" to "
        foreach( $output as & $element ) {
            if (self::isIn( self::$QUOTE, $element )) {
                $element = trim( $element, self::$QUOTE );
            }
            $element = str_replace((string) self::$SEP, self::$QUOTE, $element );
        }
        return $output;
    }

    /**
     * Skip skip control characters and alter '\"' to SEP
     *
     * @param string $input
     * @return string
     */
    private static function prePrepInput( string $input ) : string
    {
        static $BS = "\\";
        $input  = trim( $input );
        $output = self::$SP0;
        $len    = strlen( $input );
        for ($x = 0; $x < $len; $x++) {
            $byteInt = ord( $input[$x] );
            if (( $byteInt < 32 ) || ( 127 === $byteInt )) {
                // skip control characters
                continue;
            }
            if( $BS === $input[$x] ) {
                $x2 = $x + 1;
                if( isset( $input[$x2] ) && ( self::$QUOTE === $input[$x2] )) {
                    $output .= self::$SEP;
                    ++$x;
                    continue;
                }
            }
            $output .= $input[$x];
        } // end for
        return $output;
    }

    /**
     * Return bool true if string is an empty string surrounded with brackets
     *
     * @param string $content
     * @param int $x
     * @return bool
     */
    private static function isEmptyCurlyBracketField( string $content, int $x ) : bool
    {
        if( $content[$x] !== self::$CURLYBRACKETS[0] ) {
            return false;
        }
        $len     = strlen( $content );
        for( $x2 = ( $x + 1 ); $x2 < $len; $x2++ ) {
            if( ! isset( $content[$x2] )) {
                break; // ??
            }
            if( self::$CURLYBRACKETS[1] === $content[$x2] ) {
                return true;
            }
            if( self::$SP1 !== $content[$x2] ) {
                break;
            }
        } // end for
        return false;
    }

    /**
     * Return bool true if string is an empty quoted string
     *
     * @param string $content
     * @param int $x
     * @return bool
     */
    private static function isEmptyQuotedField( string $content, int $x ) : bool
    {
        if( self::$QUOTE !== $content[$x] ) {
            return false;
        }
        $len     = strlen( $content );
        for( $x2 = ( $x + 1 ); $x2 < $len; $x2++ ) {
            if( ! isset( $content[$x2] )) {
                break; // ??
            }
            if( self::$QUOTE === $content[$x2] ) {
                return true;
            }
            if( self::$SP1 !== $content[$x2] ) {
                break;
            }
        } // end for
        return false;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function trimBrackets( string $string ) : string
    {
        static $EXCL = '{}';
        return trim( $string, $EXCL );
    }

    /**
     * @link https://php.net/manual/en/function.substr.php#112707
     */

    /**
     * Return bool true if needle is in haystack
     *
     * Case-sensitive search for needle in haystack
     *
     * @param string $needle
     * @param string $haystack
     * @return bool
     */
    public static function isIn( string $needle, string $haystack ) : bool
    {
        return ( false !== strpos( $haystack, $needle ));
    }

    /**
     * Return substring after first found needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function after( string $needle, string $haystack ) : string
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        $pos = strpos( $haystack, $needle );
        return substr( $haystack, $pos + strlen( $needle ));
    }

    /**
     * Return substring after last found  needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function afterLast( string $needle, string $haystack ) : string
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        $pos = self::strrevpos( $haystack, $needle );
        return substr( $haystack, $pos + strlen( $needle ));
    }

    /**
     * Return substring before first found needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function before( string $needle, string $haystack ) : string
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        return substr( $haystack, 0, (int) strpos( $haystack, $needle ));
    }

    /**
     * Return substring before last needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function beforeLast( string $needle, string $haystack ) : string
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        return substr( $haystack, 0, (int) self::strrevpos( $haystack, $needle ));
    }

    /**
     * Return substring between needles in haystack
     *
     * Case-sensitive search for needles in haystack
     * If no needles found in haystack, '' is returned
     * If only needle1 found, substring after is returned
     * If only needle2 found, substring before is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle1
     * @param string $needle2
     * @param string $haystack
     * @return string
     */
    public static function between( string $needle1, string $needle2, string $haystack ) : string
    {
        $exists1 = self::isIn( $needle1, $haystack );
        $exists2 = self::isIn( $needle2, $haystack );
        switch( true ) {
            case ( ! $exists1 && ! $exists2 ) :
                return self::$SP0;
            case ( $exists1  && ! $exists2 ) :
                return self::after( $needle1, $haystack );
            case ( ! $exists1 && $exists2 ) :
                return self::before( $needle2, $haystack );
            default :
                return self::before( $needle2, self::after( $needle1, $haystack ));
        } // end switch
    }

    /**
     * Return substring between last two needles in haystack
     *
     * Case-sensitive search for needles in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle1
     * @param string $needle2
     * @param string $haystack
     * @return string
     */
    public static function betweenLast( string $needle1, string $needle2, string $haystack ) : string
    {
        return self::afterLast( $needle1, self::beforeLast( $needle2, $haystack ));
    }

    /**
     * Return int for length from start to last needle in haystack, false on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $haystack
     * @param string $needle
     * @return int|bool
     */
    public static function strrevpos( string $haystack, string $needle )
    {
        return ( false !== ( $rev_pos = strpos( strrev( $haystack ), strrev( $needle ))))
            ? ( strlen( $haystack ) - $rev_pos - strlen( $needle ))
            : false;
    }

    /**
     * Return bool true if haystack starts with needle, false on not found or to large
     *
     * Case-sensitive search for needle (first) in haystack
     *
     * @param string $haystack
     * @param string $needle
     * @param null|int $len       if found contains length of needle
     * @return bool
     */
    public static function startsWith( string $haystack, string $needle, ? int & $len = null ) : bool
    {
        $len       = null;
        $needleLen = strlen( $needle );
        if( $needleLen > strlen( $haystack )) {
            return false;
        }
        if( 0 === strpos( $haystack, $needle )) {
            $len = $needleLen;
            return true;
        }
        return false;
    }

    /**
     * Return bool true if haystack ends with needle, false on not found or to large
     *
     * Case-sensitive search for needle in haystack
     *
     * @param string    $haystack
     * @param string    $needle
     * @param null|int  $len       if found contains length of needle
     * @return bool
     */
    public static function endsWith( string $haystack, string $needle, ? int & $len = null ) : bool
    {
        $len       = null;
        $needleLen = strlen( $needle );
        if( $needleLen > strlen( $haystack )) {
            return false;
        }
        if( $needle === substr( $haystack, ( 0 - $needleLen ))) {
            $len = $needleLen;
            return true;
        }
        return false;
    }
}
