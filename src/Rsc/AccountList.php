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

use Exception;
use Kigkonsult\Asit\AsittagList;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use RuntimeException;
class AccountList extends AsittagList
{
    /**
     * @override
     */
    public function __construct()
    {
        parent::__construct( null, AccountDto::class );
    }

    /**
     * @param null|AccountDto[] $collection
     * @return self
     */
    public static function AccountListFactory( ? array $collection = null ) : self
    {
        $instance = new self();
        if( null !== $collection ) {
            foreach( $collection as $accountDto ) {
                $instance->append(
                    $accountDto,                  // list element
                    $accountDto->getKontoNr(),    // primary key
                    $accountDto->getKontoTyp()    // tag
                );
            }
        }
        return $instance;
    }

    /**
     * @param string $kontoNr
     * @return bool
     */
    public function isKontoNrSet( string $kontoNr ) : bool
    {
        return $this->pKeyExists( $kontoNr );
    }

    /**
     * @param string $kontoNr
     * @return AccountDto
     * @throws RuntimeException
     */
    public function getAccountDto( string $kontoNr ) : AccountDto
    {
        try {
            return $this->pKeySeek( $kontoNr )->current();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18221, $e );
        }
    }

    /**
     * @param string $kontoNr
     * @return string
     * @throws RuntimeException
     */
    public function getKontoNamn( string $kontoNr ) : string
    {
        try {
            return $this->pKeySeek( $kontoNr )->current()->getKontoNamn();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18231, $e );
        }
    }

    /**
     * @param string $kontoNr
     * @return string
     * @throws RuntimeException
     */
    public function getKontoTyp( string $kontoNr ) : string
    {
        try {
            return $this->pKeySeek( $kontoNr )->current()->getKontpTyp();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18241, $e );
        }
    }
}
