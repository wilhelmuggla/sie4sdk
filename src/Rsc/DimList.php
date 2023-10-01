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
use Kigkonsult\Sie4Sdk\Dto\DimDto;
use RuntimeException;

class DimList extends AsittagList
{
    /**
     * @override
     */
    public function __construct()
    {
        parent::__construct( null, DimDto::class );
    }

    /**
     * @param null|DimDto[] $collection
     * @return self
     */
    public static function DimListFactory( ? array $collection = null ) : self
    {
        $instance = new self();
        if( null !== $collection ) {
            foreach( $collection as $dimDto ) {
                $instance->append(
                    $dimDto,                  // list element
                    $dimDto->getDimensionNr() // primary key
                );
            }
        }
        return $instance;
    }

    /**
     * @param int $dimensionNr
     * @return string
     */
    public function getNamn( int $dimensionNr ) : string
    {
        try {
            return $this->pKeySeek( $dimensionNr )->current()->getNamn();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18321, $e );
        }
    }

    /**
     * @param int $dimensionNr
     * @return bool
     */
    public function isDimensionNrSet( int $dimensionNr ) : bool
    {
        return $this->pKeyExists( $dimensionNr );
    }

    /**
     * @param int $dimensionNr
     * @return DimDto
     * @throws RuntimeException
     */
    public function getDimDto( int $dimensionNr ) : DimDto
    {
        try {
            return $this->pKeySeek( $dimensionNr )->current();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18329, $e );
        }
    }
}
