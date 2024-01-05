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
namespace Kigkonsult\Sie4Sdk\Rsc;

use Exception;
use Kigkonsult\Asit\AsittagList;
use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use RuntimeException;

use function sprintf;

class DimObjektList extends AsittagList
{
    /**
     * @override
     */
    public function __construct()
    {
        parent::__construct( null, DimObjektDto::class );
    }

    /**
     * @param null|DimObjektDto[] $collection
     * @return self
     */
    public static function DimObjektListFactory( ? array $collection = null ) : self
    {
        $instance = new self();
        if( null !== $collection ) {
            foreach ( $collection as $dimObjektDto ) {
                $dimensionNr = $dimObjektDto->getDimensionNr();
                $objektNr    = $dimObjektDto->getObjektNr();
                $instance->append(
                    $dimObjektDto,                                  // list element
                    self::getPrimaryKey( $dimensionNr, $objektNr ), // primary key
                    [ $dimensionNr, $objektNr ]                     // tags
                );
            } // end foreach
        }
        return $instance;
    }

    /**
     * @param int $dimensionNr
     * @param string $objektNr
     * @return string
     */
    public static function getPrimaryKey( int $dimensionNr, string $objektNr ) : string
    {
        static $FORMAT = '%04d%20s';
        return sprintf( $FORMAT, $dimensionNr, $objektNr );
    }

    /**
     * @param int $dimensionNr
     * @param string $objektNr
     * @return bool
     */
    public function isObjektNrSet( int $dimensionNr, string $objektNr ) : bool
    {
        return $this->pKeyExists( self::getPrimaryKey( $dimensionNr, $objektNr ));
    }

    /**
     * @param int $dimensionNr
     * @param string $objektNr
     * @return DimObjektDto
     * @throws RuntimeException
     */
    public function getDimObjektDto( int $dimensionNr, string $objektNr ) : DimObjektDto
    {
        try {
            return $this->pKeySeek( self::getPrimaryKey( $dimensionNr, $objektNr ))->current();
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), 18521, $e );
        }
    }
}
