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
namespace Kigkonsult\Sie4Sdk\Dto;

use Kigkonsult\Sie4Sdk\Dto\Traits\ObjektNrTrait;

use function strcmp;

/**
 * Class DimObjektDto
 *
 * As (lable) #OBJEKT in Sie4, $dimensionNr and $objektNr/namn required
 * In (lable) #TRANS in Sie4 and (array) objektlista, $dimensionNr and $objektNr required
 */
class DimObjektDto extends DimDto
{
    use ObjektNrTrait;

    /**
     * @var string
     */
    private $objektNamn = null;

    /**
     * @var callable
     */
    public static $SORTER = [ DimObjektDto::class, 'dimObjektSorter' ];

    /**
     * Sort DimObjektDto[] on dimensionsnr
     *
     * @override
     * @param DimObjektDto $a
     * @param DimObjektDto $b
     * @return int
     */
    public static function dimObjektSorter( DimObjektDto $a, DimObjektDto $b ) : int
    {
        if( 0 === ( $dimCmp = parent::dimSorter( $a, $b ))) {
            return strcmp((string) $a->getObjektNr(), (string) $b->getObjektNr());
        }
        return $dimCmp;
    }

    /**
     * Class factory method, set dimensionNr and objektNr, objektName opt
     *
     * @param int|string $dimensionsNr
     * @param string $objektNr
     * @param null|string $objektNamn
     * @return self
     */
    public static function factoryDimObject( $dimensionsNr, string $objektNr, $objektNamn = null ) : self
    {
        $instance = new self();
        $instance->setDimensionNr( $dimensionsNr );
        $instance->setObjektNr((string) $objektNr );
        if( ! empty( $objektNamn )) {
            $instance->setObjektNamn( $objektNamn );
        }
        return $instance;
    }

    /**
     * Return objektNamn
     *
     * @return string
     */
    public function getObjektNamn()
    {
        return $this->objektNamn;
    }

    /**
     * Return bool true if objektNamn is set
     *
     * @return bool
     */
    public function isObjektNamnSet() : bool
    {
        return ( null !== $this->objektNamn );
    }

    /**
     * Set objektNamn
     *
     * @param string $objektNamn
     * @return self
     */
    public function setObjektNamn( string $objektNamn ) : self
    {
        $this->objektNamn = $objektNamn;
        return $this;
    }
}
