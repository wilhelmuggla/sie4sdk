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
namespace Kigkonsult\Sie4Sdk\Dto;

class AdressDto implements DtoInterface
{
    /**
     * @var string|null
     */
    private ?string $kontakt = null;

    /**
     * @var string|null
     */
    private ?string $utdelningsadr = null;

    /**
     * @var string|null
     */
    private ?string $postadr = null;

    /**
     * @var string|null
     */
    private ?string $tel = null;

    /**
     * Class factory : kontakt, utdelningsadr, postadr, tel
     *
     * Alla obligatoriska
     *
     * @param string $kontakt
     * @param string $utdelningsadr
     * @param string $postadr
     * @param string $tel
     * @return self
     */
    public static function factory(
        string $kontakt,
        string $utdelningsadr,
        string $postadr,
        string $tel
    ) : self
    {
        $instance = new self();
        $instance->setKontakt( $kontakt );
        $instance->setUtdelningsadr( $utdelningsadr );
        $instance->setPostadr( $postadr );
        $instance->setTel( $tel );
        return $instance;
    }
    /**
     * @return null|string
     */
    public function getKontakt() : ? string
    {
        return $this->kontakt;
    }

    /**
     * Return bool true if kontakt is set
     *
     * @return bool
     */
    public function isKontaktSet() : bool
    {
        return ( null !== $this->kontakt );
    }

    /**
     * @param string $kontakt
     * @return self
     */
    public function setKontakt( string $kontakt ) : self
    {
        $this->kontakt = $kontakt;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getUtdelningsadr() : ? string
    {
        return $this->utdelningsadr;
    }

    /**
     * Return bool true if utdelningsadr is set
     *
     * @return bool
     */
    public function isUtdelningsadrSet() : bool
    {
        return ( null !== $this->utdelningsadr );
    }

    /**
     * @param string $utdelningsadr
     * @return self
     */
    public function setUtdelningsadr( string $utdelningsadr ) : self
    {
        $this->utdelningsadr = $utdelningsadr;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPostadr() : ? string
    {
        return $this->postadr;
    }

    /**
     * Return bool true if postadr is set
     *
     * @return bool
     */
    public function isPostadrSet() : bool
    {
        return ( null !== $this->postadr );
    }

    /**
     * @param string $postadr
     * @return self
     */
    public function setPostadr( string $postadr ) : self
    {
        $this->postadr = $postadr;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTel() : ? string
    {
        return $this->tel;
    }

    /**
     * Return bool true if tel is set
     *
     * @return bool
     */
    public function isTelSet() : bool
    {
        return ( null !== $this->tel );
    }

    /**
     * @param string $tel
     * @return self
     */
    public function setTel( string $tel ) : self
    {
        $this->tel = $tel;
        return $this;
    }
}
