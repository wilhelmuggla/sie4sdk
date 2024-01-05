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
namespace Kigkonsult\Sie4Sdk\Dto;

use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Dto\Traits\KontoNrTrait;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Dto\AccountTypeEntry;

use function array_flip;
use function strtoupper;

/**
 * Class AccountDto
 *
 * Kontonr/name required, typ/enhet opt
 *
 * @since 1.8.3 2023-09-20
 */
class AccountDto implements DtoInterface
{
    /**
     * Sie4 konto types
     */
    public const T = 'T';
    public const S = 'S';
    public const K = 'K';
    public const I = 'I';

    /**
     * Return Sie5 kontoTyp for Sie4 kontoTyp and v.v., false if not found
     *
     * @param string  $type
     * @param bool    $forSie4
     * @return false|string
     */
    public static function getKontoType( string $type, bool $forSie4 ) : bool | string
    {
        static $KONTOTYPER = [
            self::T => AccountTypeEntry::ASSET,     // Tillgång
            self::S => AccountTypeEntry::LIABILITY, // Skuld
            self::K => AccountTypeEntry::COST,      // kostnad
            self::I => AccountTypeEntry::INCOME,    // Intäkt
        ];
        static $KONTOTYPER2 = [];
        if( $forSie4 ) {
            if( empty( $KONTOTYPER2 )) {
                $KONTOTYPER2 = array_flip( $KONTOTYPER );
            }
            return $KONTOTYPER2[$type] ?? false;
        }
        return $KONTOTYPER[$type] ?? false;
    }

    /**
     * In case of missing kontoTyp...
     *
     * @param int|string $kontoNr
     * @return string
     * @since 1.8.3 2023-09-20
     */
    public static function findOutKontoType( int|string $kontoNr ) : string
    {
        return match((int) trim((string) $kontoNr )[0] ) {
            1       => self::T,
            2       => self::S,
            3       => self::I,
            default => self::K,
        };
    }

    use KontoNrTrait;

    /**
     * @var string|null
     */
    private ?string $kontoNamn = null;

    /**
     * @var string|null
     */
    private ?string $kontoTyp = null;

    /**
     * @var string|null
     */
    private ?string $enhet = null;

    /**
     * @var callable
     */
    public static $SORTER = [ self::class, 'accountSorter' ];

    /**
     * Sort AccountDto[] on kontonr
     *
     * @param AccountDto $a
     * @param AccountDto $b
     * @return int
     */
    public static function accountSorter( AccountDto $a, AccountDto $b ) : int
    {
        return StringUtil::strSort((string) $a->getKontoNr(), (string) $b->getKontoNr());
    }

    /**
     * Class factory method, kontoNr/Namn/Typ, enhet opt
     *
     * @param int|string $kontoNr
     * @param string $kontoNamn
     * @param null|string $kontoTyp
     * @param null|string $enhet
     * @return self
     * @since 1.8.3 2023-09-20
     */
    public static function factory(
        int | string $kontoNr,
        string       $kontoNamn,
        ? string     $kontoTyp = null,
        ? string     $enhet = null
    ) : self
    {
        $instance = new self();
        $instance->setKontoNr( $kontoNr );
        $instance->setKontoNamn( $kontoNamn );
        if( ! empty( $kontoTyp )) {
        $instance->setKontoTyp( $kontoTyp );
        }
        if( ! empty( $enhet )) {
            $instance->setEnhet( $enhet );
        }
        return $instance;
    }

    /**
     * Return kontoNamn
     *
     * @return string|null
     */
    public function getKontoNamn() : ?string
    {
        return $this->kontoNamn;
    }

    /**
     * Return bool true if kontoNamn is set
     *
     * @return bool
     */
    public function isKontonamnSet() : bool
    {
        return ( null !== $this->kontoNamn );
    }

    /**
     * Set kontoNamn
     *
     * @param string $kontoNamn
     * @return self
     */
    public function setKontoNamn( string $kontoNamn ) : self
    {
        $this->kontoNamn = $kontoNamn;
        return $this;
    }

    /**
     * Return kontoTyp
     *
     * @return string|null
     */
    public function getKontoTyp() : ?string
    {
        return $this->kontoTyp;
    }

    /**
     * Return bool true if kontoType is set
     *
     * @return bool
     */
    public function isKontotypSet() : bool
    {
        return ( null !== $this->kontoTyp );
    }

    /**
     * Set kontoTyp
     *
     * @param null|string $kontoTyp
     * @return self
     * @throws InvalidArgumentException
     */
    public function setKontoTyp( ? string $kontoTyp = null ) : self
    {
        static $FMT = 'Ogiltig kontoTyp : ';
        if( null !== $kontoTyp ) {
        $kontoTyp = strtoupper( $kontoTyp );
        if( false === self::getKontoType( $kontoTyp, false )) {
            throw new InvalidArgumentException( $FMT . $kontoTyp );
        }
        }
        $this->kontoTyp = $kontoTyp;
        return $this;
    }

    /**
     * Return enhet
     *
     * @return string|null
     */
    public function getEnhet() : ?string
    {
        return $this->enhet;
    }

    /**
     * Return bool true if enhet is set
     *
     * @return bool
     */
    public function isEnhetSet() : bool
    {
        return ( null !== $this->enhet );
    }

    /**
     * Set enhet
     *
     * @param string $enhet
     * @return self
     */
    public function setEnhet( string $enhet ) : AccountDto
    {
        $this->enhet = $enhet;
        return $this;
    }
}
