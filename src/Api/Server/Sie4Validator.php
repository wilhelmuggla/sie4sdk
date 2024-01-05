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
namespace Kigkonsult\Sie4Sdk\Api\Server;

use Comet\Validator;
use Kigkonsult\Sie4Sdk\Api\Server\ValidatorRules\Array2;
use Kigkonsult\Sie4Sdk\Api\Server\ValidatorRules\Array3;
use Kigkonsult\Sie4Sdk\Api\Server\ValidatorRules\Array3int;
use Kigkonsult\Sie4Sdk\Api\Server\ValidatorRules\Date1;
use Kigkonsult\Sie4Sdk\Api\Server\ValidatorRules\Date2;
use Kigkonsult\Sie4Sdk\Api\Server\ValidatorRules\Int1;
use Kigkonsult\Sie4Sdk\Api\Server\ValidatorRules\Int2;
use Kigkonsult\Sie4Sdk\Api\Server\ValidatorRules\Numeric2;
use Kigkonsult\Sie4Sdk\Sie4Interface;
use Rakit\Validation\RuleQuashException;
use RuntimeException;

use function is_array;

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
     * @throws RuntimeException
     */
    public static function validateArray( array $input, ? string & $msg = null ) : bool
    {
        static $SP0 = '';
        static $SP1 = ' ';
        static $CLN = ',';
        $validator  = new Validator;
        try {
            $validator->addValidator( 'date1', new Date1() );
            $validator->addValidator( 'int1', new Int1() );
            $validator->addValidator( 'array2', new Array2() );
            $validator->addValidator( 'array3', new Array3() );
            $validator->addValidator( 'array3int', new Array3int() );
            $validator->addValidator( 'int2', new Int2() );
            $validator->addValidator( 'date2', new Date2() );
            $validator->addValidator( 'numeric2', new Numeric2() );
        }
        catch( RuleQuashException $e ) {
            throw new RuntimeException( $e->getMessage(), $e->getCode(), $e );
        }
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
