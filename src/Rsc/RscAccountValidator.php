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
namespace Kigkonsult\Sie4Sdk\Rsc;

use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\KontoNrInterface;
use RuntimeException;

use function sprintf;

/**
 * Class RscAccountValidator
 *
 * Validates Kontonr using AccountList
 */
class RscAccountValidator
{
    /**
     * @var AccountList
     */
    private AccountList $accountList;

    /**
     * Class constructor
     *
     * @param null|AccountList $accountList
     */
    public function __construct( ? AccountList $accountList = null )
    {
        $this->accountList = $accountList ?? new AccountList();
    }

    /**
     * Class factory method
     *
     * @param AccountList $accountList
     * @return self
     */
    public static function factory( AccountList $accountList ) : self
    {
        return new self( $accountList );
    }

    /**
     * Return bool true if kontoNr Exists
     *
     * @param string $kontoNr
     * @return bool
     * @throws RuntimeException
     */
    public function kontoNrExists( string $kontoNr ) : bool
    {
        $this->assertAccuntList();
        return $this->accountList->isKontoNrSet( $kontoNr );
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function assertAccuntList() : void
    {
        static $ERR = 'AccountList NOT set';
        if( empty( $this->accountList )) {
            throw new RuntimeException( $ERR, 18901 );
        }
    }

    /**
     * Assert kontoNrs in Trans, Balans, BalansObjektDto, PeriodDto and SruDto
     *
     * Must exist in accountList
     *
     * @param KontoNrInterface[] $kontoNrDtos
     * @return void
     * @throws RuntimeException
     */
    public function assertKontoNrs( array $kontoNrDtos ) : void
    {
        static $FMTERR = 'KontoNr (#%d %s) %s NOT found';
        $this->assertAccuntList();
        foreach( $kontoNrDtos as $tx => $kontoNrDto ) {
            $kontoNr = $kontoNrDto->getKontoNr();
            if( ! $this->kontoNrExists( $kontoNr )) {
                throw new RuntimeException( sprintf( $FMTERR, $tx, 18911, $kontoNr ));
            }
        } // end foreach
    }

    /**
     * Append AccountList from AccountDto[], skip existing kontoNrs
     *
     * @param AccountDto[] $accountDtos
     * @return RscAccountValidator
     */
    public function appendAccounts( array $accountDtos ) : RscAccountValidator
    {
        foreach( $accountDtos as $accountDto ) {
            $kontoNr = $accountDto->getKontoNr();
            if( $this->kontoNrExists( $kontoNr )) {
                continue;
            }
            $this->accountList->append(
                $accountDto,                  // list element
                $kontoNr,                     // primary key
                $accountDto->getKontoTyp()    // tag
            );
        } // end foreach
        return $this;
    }

    /**
     * @return null|AccountList
     */
    public function getAccountList() : ? AccountList
    {
        return $this->accountList;
    }

    /**
     * @param AccountList $accountList
     * @return RscAccountValidator
     */
    public function setAccountList( AccountList $accountList ) : RscAccountValidator
    {
        $this->accountList = $accountList;
        return $this;
    }
}
