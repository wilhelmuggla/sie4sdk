<?php
/**
 * Sie4Sdk   PHP Sie4 SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Sdk
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
namespace Kigkonsult\Sie4Sdk\Api;

use Exception;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\AdressDto;
use Kigkonsult\Sie4Sdk\Dto\BalansDto;
use Kigkonsult\Sie4Sdk\Dto\BalansObjektDto;
use Kigkonsult\Sie4Sdk\Dto\DimDto;
use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Dto\IdDto;
use Kigkonsult\Sie4Sdk\Dto\PeriodDto;
use Kigkonsult\Sie4Sdk\Dto\RarDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\SruDto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Dto\UnderDimDto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Util\DateTimeUtil;

use function array_keys;
use function in_array;
use function ksort;

/**
 * Class Array2Sie4Dto
 *
 * Transform (HTTP, $_REQUEST) input array to Sie4Dto (4E/4I)
 *
 * input format
 * [
 *     self::TIMESTAMP          => <microtime>
 *     self::GUID               => <guid>
 *
 *     self::FLAGGPOST          => <0/1>,
 *
 *     self::PROGRAMNAMN        => <programNamn>,
 *     self::PROGRAMVERSION     => <programVersion>,
 *     self::GENDATUM           => <SIE4YYYYMMDD-datum>,
 *     self::GENSIGN            => <sign>,
 *     self::PROSATEXT          => <kommentar>,
 *     self::FORETAGSTYP        => <företagstyp>
 *     self::FNRID              => <företagsid>,
 *     self::ORGNRORGNR         => <orgnr>,
 *     self::ORGNRFORNVR        => <förvnr>,
 *     self::SNIKOD             => <branch-kod>,
 *     self::ADRKONTAKT         => <kontakt<;
 *     self::UTDELNINGSADR      => <utdelningadr>;
 *     self::POSTADR            => <postadr>;
 *     self::TEL                => <telefon>;
 *     self::FTGNAMN            => <företagsnamn>,
 *
 *     // instance data share the same index
 *     self::RARARSNR           => [ *<årsnr> ],
 *     self::RARSTART           => [ *<SIE4YYYYMMDD-start-datum> ],
 *     self::RARSLUT            => [ *<SIE4YYYYMMDD-slut-datum> ],
 *
 *     self::TAXYEAR            => <taxeringsår>,
 *     self::OMFATTNDATUM       => <SIE4YYYYMMDD-datum>,
 *     self::KPTYPE             => <konoplanstyp>,
 *     self::VALUTAKOD          => <valutakod>,
 *
 *     // instance data share the same index
 *     self::KONTONR            => [ *<kontonr> ],
 *     self::KONTONAMN          => [ *<kontonamn> ],
 *     self::KONTOTYP           => [ *<kontoTyp> ],
 *     self::KONTOENHET         => [ *<enhet> ],
 *
 *     // instance data share the same index
 *     self::SRUKONTO           => [ *<konto> ],
 *     self::SRUKOD             => [ *<SRU-kod> ],
 *
 *     // instance data share the same index
 *     self::DIMENSIONNR        => [ *<dimId> ],
 *     self::DIMENSIONNAMN      => [ *<dimNamn> ],
 *
 *     // instance data share the same index
 *     self::UNDERDIMNR         => [ *<underDimId> ],
 *     self::UNDERDIMNAMN       => [ *<underDimNamn> ],
 *     self::UNDERDIMSUPER      => [ *<superDimId> ],
 *
 *     // instance data share the same index
 *     self::OBJEKTDIMENSIONNR  => [ *<dimId> ],
 *     self::OBJEKTNR           => [ *<objektNr> ],
 *     self::OBJEKTNAMN         => [ *<objektNamn> ],
 *
 *     // instance data share the same index
 *     self::IBARSNR            => [ *<årsnr> ];
 *     self::IBKONTONR          => [ *<kontoNr> ];
 *     self::IBSALDO            => [ *<saldo> ];
 *     self::IBKVANTITET        => [ *<kvantitet> ];
 *
 *     // instance data share the same index
 *     self::UBARSNR            => [ *<årsnr> ];
 *     self::UBKONTONR          => [ *<kontoNr> ];
 *     self::UBSALDO            => [ *<saldo> ];
 *     self::UBKVANTITET        => [ *<kvantitet> ];
 *
 *     // instance data share the same index
 *     self::UIBARSNR           => [ *<årsnr> ];
 *     self::UIBKONTONR         => [ *<kontoNr> ];
 *     self::UIBDIMENSIONNR     => [ *<dimId> ],
 *     self::UIBOBJEKTNR        => [ *<objektNr> ],
 *     self::UIBSALDO           => [ *<saldo> ];
 *     self::UIBKVANTITET       => [ *<kvantitet> ];
 *
 *     // instance data share the same index
 *     self::OIBARSNR           => [ *<årsnr> ];
 *     self::OIBKONTONR         => [ *<kontoNr> ];
 *     self::OIBDIMENSIONNR     => [ *<dimId> ],
 *     self::OIBOBJEKTNR        => [ *<objektNr> ],
 *     self::OIBSALDO           => [ *<saldo> ];
 *     self::OIBKVANTITET       => [ *<kvantitet> ];
 *
 *     // instance data share the same index
 *     self::RESARSNR           => [ *<årsnr> ];
 *     self::RESKONTONR         => [ *<kontoNr> ];
 *     self::RESSALDO           => [ *<saldo> ];
 *     self::RESKVANTITET       => [ *<kvantitet> ];
 *
 *     // instance data share the same index
 *     self::PSALDOARSNR        => [ *<årsnr> ];
 *     self::PSALDOPERIOD       => [ *<period> ];
 *     self::PSALDOKONTONR      => [ *<kontoNr> ];
 *     self::PSALDODIMENSIONNR  => [ *<dimId> ],
 *     self::PSALDOOBJEKTNR     => [ *<objektNr> ],
 *     self::PSALDOSALDO        => [ *<saldo> ];
 *     self::PSALDOKVANTITET    => [ *<kvantitet> ];
 *
 *     // instance data share the same index
 *     self::PBUDGETARSNR       => [ *<årsnr> ];
 *     self::PBUDGETPERIOD      => [ *<period> ];
 *     self::PBUDGETKONTONR     => [ *<kontoNr> ];
 *     self::PBUDGETDIMENSIONNR => [ *<dimId> ],
 *     self::PBUDGETOBJEKTNR    => [ *<objektNr> ],
 *     self::PBUDGETSALDO       => [ *<saldo> ];
 *     self::PBUDGETKVANTITET   => [ *<kvantitet> ];
 *
 *     // instance data share the same index
 *     self::VERTIMESTAMP       => [ *<microtime> ]
 *     self::VERGUID            => [ *<guid> ]
 *     self::VERDATUM           => [ *<SIE4YYYYMMDD-verdatum> ],
 *     self::VERSERIE           => [ *serie> ],
 *     self::VERNR              => [ *<vernr> ],
 *     self::VERTEXT            => [ *<vertext> ],
 *     self::REGDATUM           => [ *<SIE4YYYYMMDD-regdatum> ],
 *     self::VERSIGN            => [ *<sign> ],
 *
 *     // Ledger data instances within Journal entry data instance share same index
 *     // Journal entry data in index order
 *     self::TRANSTIMESTAMP     => [ *[ *<microtime> ] ]
 *     self::TRANSGUID          => [ *[ *<guid> ] ]
 *     self::TRANSKONTONR       => [ *[ *<kontonr> ] ]
 *     self::TRANSDIMENSIONNR   => [ *[ *[ *<dimId> ] ] ],
 *     self::TRANSOBJEKTNR      => [ *[ *[ *<objektnr> ] ] ],
 *     self::TRANSBELOPP        => [ *[ *<belopp> ] ]
 *     self::TRANSDAT           => [ *[ *<SIE4YYYYMMDD-transdat> ] ]
 *     self::TRANSTEXT          => [ *[ *<transText> ] ]
 *     self::TRANSKVANTITET     => [ *[ *<kvantitet> ] ]
 *
 *     self::RTRANSTIMESTAMP    => [ *[ *<microtime> ] ]
 *     self::RTRANSGUID         => [ *[ *<guid> ] ]
 *     self::RTRANSKONTONR      => [ *[ *<kontonr> ] ]
 *     self::RTRANSDIMENSIONNR  => [ *[ *[ *<dimId> ] ] ],
 *     self::RTRANSOBJEKTNR     => [ *[ *[ *<objektnr> ] ] ],
 *     self::RTRANSBELOPP       => [ *[ *<belopp> ] ]
 *     self::RTRANSDAT          => [ *[ *<SIE4YYYYMMDD-transdat> ] ]
 *     self::RTRANSTEXT         => [ *[ *<transText> ] ]
 *     self::RTRANSKVANTITET    => [ *[ *<kvantitet> ] ]
 *
 *     self::BTRANSTIMESTAMP    => [ *[ *<microtime> ] ]
 *     self::BTRANSGUID         => [ *[ *<guid> ] ]
 *     self::BTRANSKONTONR      => [ *[ *<kontonr> ] ]
 *     self::BTRANSDIMENSIONNR  => [ *[ *[ *<dimId> ] ] ],
 *     self::BTRANSOBJEKTNR     => [ *[ *[ *<objektnr> ] ] ],
 *     self::BTRANSBELOPP       => [ *[ *<belopp> ] ]
 *     self::BTRANSDAT          => [ *[ *<SIE4YYYYMMDD-transdat> ] ]
 *     self::BTRANSTEXT         => [ *[ *<transText> ] ]
 *     self::BRTRANSKVANTITET   => [ *[ *<kvantitet> ] ]
 *
 *     // crc32-value
 *     self::KSUMMAPOST         => <crc32-value>,
 * ]
 */
class Array2Sie4Dto extends ArrayBase
{
    /**
     * @var array
     */
    private array $input = [];

    /**
     * @var Sie4Dto|null
     */
    private ?Sie4Dto $sie4Dto = null;

    /**
     * Transform Sie4 array to SieDto, factory method
     *
     * @param array $input
     * @return Sie4Dto
     * @throws Exception
     */
    public static function process( array $input ) : Sie4Dto
    {
        $instance          = new self();
        $instance->input   = array_change_key_case( $input, CASE_UPPER );
        $idDto             = $instance->loadIdDto();
        $instance->sie4Dto = new Sie4Dto( $idDto );
        $instance->readBasic();

        $instance->readAccountData();
        $instance->readSruData();
        $instance->readDimData();
        $instance->readUnderDimData();
        $instance->readDimObjektData();

        $instance->readIbData();
        $instance->readUbData();
        $instance->readOibData();
        $instance->readOubData();
        $instance->readResData();
        $instance->readPsaldoData();
        $instance->readPbudgetData();
        $instance->readVerTransData();

        if( isset( $instance->input[self::KSUMMAPOST] )) {
            $instance->sie4Dto->setKsumma((int) $instance->input[self::KSUMMAPOST] );
        }

        return $instance->sie4Dto;
    }

    /**
     * Process identifikationsposter in order
     *
     * @return IdDto
     */
    private function loadIdDto() : IdDto
    {
        $idDto = new IdDto();
        /**
         * Vilket program som genererat filen
         * Obligatorisk
         * #PROGRAM programnamn version
         * expected as
         * [
         *     ....
         *     self::PROGRAMNAMN    => <programNamn>,
         *     self::PROGRAMVERSION => <programVersion>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::PROGRAMNAMN] )) {
            $idDto->setProgramnamn( $this->input[self::PROGRAMNAMN] );
        }
        if( isset( $this->input[self::PROGRAMVERSION] )) {
            $idDto->setVersion( $this->input[self::PROGRAMVERSION] );
        }
        /**
         * När och av vem som filen genererats
         * #GEN datum sign
         * Obligatorisk (sign opt) Sie4, båda obl. Sie5 SieEntry
         * expected as
         * [
         *     ....
         *     self::GENDATUM  => <SIE4YYYYMMDD-datum>,
         *     self::GENSIGN   => <sign>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::GENDATUM] )) {
            $idDto->setGenDate(
                DateTimeUtil::getDateTime( $this->input[self::GENDATUM], self::GEN, 3511 )
            );
        }
        if( isset( $this->input[self::GENSIGN] )) {
            $idDto->setSign( $this->input[self::GENSIGN] );
        }
        /**
         * Fri kommentartext kring filens innehåll
         *
         * #PROSA text
         * valfri
         */
        if( isset( $this->input[self::PROSATEXT] )) {
            $idDto->setProsa( $this->input[self::PROSATEXT] );
        }
        /**
         * Företagstyp
         *
         * #FTYP Företagstyp
         * valfri
         */
        if( isset( $this->input[self::FORETAGSTYP] )) {
            $idDto->setFtyp( $this->input[self::FORETAGSTYP] );
        }
        /**
         * Redovisningsprogrammets internkod för exporterat företag
         *
         * #FNR företagsid
         * valfri
         * expected as
         * [
         *     ....
         *     self::FNRID    => <företagsid>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::FNRID] )) {
            $idDto->setFnrId( $this->input[self::FNRID] );
        }
        /**
         * Organisationsnummer för det företag som exporterats
         *
         * #ORGNR orgnr förvnr verknr
         * förvnr : anv då ensk. person driver flera ensk. firmor (ordningsnr)
         * verknr : anv ej
         * valfri, MEN orgnr obligatoriskt i sie4Dto (FileInfoTypeEntry/CompanyTypeEntry)
         * expected as
         * [
         *     ....
         *     self::ORGNRORGNR  => <orgnr>,
         *     self::ORGNRFORNVR => <förvnr>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::ORGNRORGNR] )) {
            $idDto->setOrgnr( $this->input[self::ORGNRORGNR] );
        }
        if( isset( $this->input[self::ORGNRFORNVR] )) {
            $idDto->setMultiple( $this->input[self::ORGNRFORNVR] );
        }
        /**
         * Branschtillhörighet för det exporterade företaget, Sie4E only
         *
         * #BKOD SNI-kod
         */
        if( isset( $this->input[self::SNIKOD] )) {
            $idDto->setBkod( $this->input[self::SNIKOD] );
        }
        $this->readIdDtoAdrData( $idDto );

        /**
         * Fullständigt namn för det företag som exporterats
         *
         * #FNAMN företagsnamn
         * Obligatorisk men valfri i sie4Dto (FileInfoTypeEntry/CompanyTypeEntry)
         * expected as
         * [
         *     ....
         *     self::FTGNAMN => <företagsnamn>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::FTGNAMN] )) {
            $idDto->setFnamn( $this->input[self::FTGNAMN] );
        }
        $this->readIdDtoRarData( $idDto );
        /**
         * Taxeringsår för deklarations- information (SRU-koder)
         *
         * #TAXAR år
         * valfri
         */
        if( isset( $this->input[self::TAXYEAR] )) {
            $idDto->setTaxar( $this->input[self::TAXYEAR] );
        }
        /**
         * Datum för periodsaldons omfattning
         *
         * #OMFATTN datum
         * valfri, Sie4E only
         */
        if( isset( $this->input[self::OMFATTNDATUM] )) {
            $idDto->setOmfattn(
                DateTimeUtil::getDateTime( $this->input[self::OMFATTNDATUM], self::OMFATTN, 3519 )
            );
        }
        /**
         * Kontoplanstyp
         *
         * #KPTYP typ
         * valfri
         */
        if( isset( $this->input[self::KPTYPE] )) {
            $idDto->setKptyp( $this->input[self::KPTYPE] );
        }

        /**
         * Redovisningsvaluta
         *
         * #VALUTA valutakod
         * valfri
         * expected as
         * [
         *     ....
         *     self::VALUTAKOD => <valutakod>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::VALUTAKOD] )) {
            $idDto->setValutakod( $this->input[self::VALUTAKOD] );
        }
        return $idDto;
    }

    /**
     * Load basic/mandatory parts
     */
    private function readBasic() : void
    {
        if( isset( $this->input[self::TIMESTAMP] )) {
            $this->sie4Dto->setTimestamp((float) $this->input[self::TIMESTAMP] );
        }
        if( isset( $this->input[self::GUID] )) {
            $this->sie4Dto->setCorrelationId( $this->input[self::GUID] );
        }
        if( isset( $this->input[self::FLAGGPOST] )) {
            $this->sie4Dto->setFlagga((int) $this->input[self::FLAGGPOST] );
        }
        if( isset( $this->input[self::FNRID] )) {
            $this->sie4Dto->setFnrId( $this->input[self::FNRID] );
        }
        if( isset( $this->input[self::ORGNRORGNR] )) {
            $this->sie4Dto->setOrgnr( $this->input[self::ORGNRORGNR] );
        }
        if( isset( $this->input[self::ORGNRFORNVR] )) {
            $this->sie4Dto->setMultiple((int) $this->input[self::ORGNRFORNVR] );
        }
    }

    /**
     * Adressuppgifter för det aktuella företaget
     *
     * #ADRESS kontakt utdelningsadr postadr tel
     * valfri
     *
     * @param IdDto $idDto
     */
    private function readIdDtoAdrData( IdDto $idDto ) : void
    {
        static $ADRKEYS = [ self::ADRKONTAKT, self::UTDELNINGSADR, self::POSTADR, self::TEL ];
        $found = false;
        foreach( $ADRKEYS as $adrKey ) {
            if( isset( $this->input[$adrKey] )) {
                $found = true;
                break;
            }
        }
        if( ! $found ) {
            return;
        }
        $adressDto = new AdressDto();
        if( isset( $this->input[self::ADRKONTAKT] )) {
            $adressDto->setKontakt( $this->input[self::ADRKONTAKT] );
        }
        if( isset( $this->input[self::UTDELNINGSADR] )) {
            $adressDto->setUtdelningsadr( $this->input[self::UTDELNINGSADR] );
        }
        if( isset( $this->input[self::POSTADR] )) {
            $adressDto->setPostadr( $this->input[self::POSTADR] );
        }
        if( isset( $this->input[self::TEL] )) {
            $adressDto->setTel( $this->input[self::TEL] );
        }
        $idDto->setAdress( $adressDto );
    }

    /**
     * Räkenskapsår från vilket exporterade data hämtats
     *
     * #RAR årsnr start slut
     * valfri
     *
     * @param IdDto $idDto
     */
    private function readIdDtoRarData( IdDto $idDto ) : void
    {
        if( ! isset( $this->input[self::RARARSNR] )) {
            return;
        }
        foreach( array_keys( $this->input[self::RARARSNR] ) as $x ) {
            $rarDto = new RarDto();
            $rarDto->setArsnr( $this->input[self::RARARSNR][$x] );
            if( isset( $this->input[self::RARSTART][$x] )) {
                $rarDto->setStart(
                    DateTimeUtil::getDateTime( $this->input[self::RARSTART][$x], self::RAR, 6788 )
                );
            } // end if
            if( isset( $this->input[self::RARSLUT][$x] )) {
                $rarDto->setSlut(
                   DateTimeUtil::getDateTime( $this->input[self::RARSLUT][$x], self::RAR, 6789 )
                );
            } // end if
            $idDto->addRarDto( $rarDto );
        } // end foreach
    }


    /**
     * Manage Sie4  'Kontoplansuppgifter', #KONTO, #KTYP, #ENHET
     *
     * expected as
     * [
     *     ....
     *     self::KONTONR    => [ *<kontonr> ],
     *     self::KONTONAMN  => [ *<kontonamn> ],
     *     self::KONTOTYP   => [ *<kontoTyp> ],
     *     self::KONTOENHET => [ *<enhet> ],
     *     ....
     * ]
     *
     * @return void
     */
    private function readAccountData() : void
    {
        if( ! isset( $this->input[self::KONTONR] )) {
            return;
        }
        foreach( array_keys( $this->input[self::KONTONR] ) as $x ) {
            $accountDto = new AccountDto();
            $accountDto->setKontoNr( $this->input[self::KONTONR][$x] );
            if( isset( $this->input[self::KONTONAMN][$x] )) {
                $accountDto->setKontoNamn( $this->input[self::KONTONAMN][$x] );
            }
            if( isset( $this->input[self::KONTOTYP][$x] )) {
                $accountDto->setKontoTyp( $this->input[self::KONTOTYP][$x] );
            }
            if( isset( $this->input[self::KONTOENHET][$x] )) {
                $accountDto->setEnhet( $this->input[self::KONTOENHET][$x] );
            }
            $this->sie4Dto->addAccountDto( $accountDto );
        } // end foreach
    }

    /**
     * Manage Sie4  'Kontoplansuppgifter', #SRU
     *
     * expected as
     * [
     *     ....
     *     self::SRUKONTO          => [ *<konto> ],
     *     self::SRUKOD            => [ *<SRU-kod> ],
     *     ....
     * ]
     *
     * @return void
     */
    private function readSruData() : void
    {
        if( ! isset( $this->input[self::SRUKONTO] )) {
            return;
        }
        foreach( array_keys( $this->input[self::SRUKONTO] ) as $x ) {
            $sruDto = new SruDto();
            $sruDto->setKontoNr( $this->input[self::SRUKONTO][$x] );
            if( isset( $this->input[self::SRUKOD][$x] )) {
                $sruDto->setSruKod( $this->input[self::SRUKOD][$x] );
            }
            $this->sie4Dto->addSruDto( $sruDto );
        } // end foreach
    }

    /**
     * Manage Sie4  'Kontoplansuppgifter', #DIM
     *
     * expected as
     * [
     *     ....
     *     self::DIMENSIONNR    => [ *<dimId> ],
     *     self::DIMENSIONNAMN  => [ *<dimNamn> ],
     *     ....
     * ]
     *
     * @return void
     */
    private function readDimData() : void
    {
        if( ! isset( $this->input[self::DIMENSIONNR] )) {
            return;
        }
        foreach( array_keys( $this->input[self::DIMENSIONNR] ) as $x ) {
            $dimDto = new DimDto();
            $dimDto->setDimensionNr( $this->input[self::DIMENSIONNR][$x] );
            if( isset( $this->input[self::DIMENSIONNAMN][$x] )) {
                $dimDto->setDimensionsNamn( $this->input[self::DIMENSIONNAMN][$x] );
            }
            $this->sie4Dto->addDimDto( $dimDto );
        } // end foreach
    }

    /**
     * Manage Sie4  'Kontoplansuppgifter', #UNDERDIM
     *
     * expected as
     * [
     *     ....
     *     self::UNDERDIMNR     => [ *<underDimId> ],
     *     self::UNDERDIMNAMN   => [ *<underDimNamn> ],
     *     self::UNDERDIMSUPER  => [ *<superDimId> ],
     *     ....
     * ]
     *
     * @return void
     */
    private function readUnderDimData() : void
    {
        if( ! isset( $this->input[self::UNDERDIMNR] )) {
            return;
        }
        foreach( array_keys( $this->input[self::UNDERDIMNR] ) as $x ) {
            $underDimDto = new UnderDimDto();
            $underDimDto->setDimensionNr( $this->input[self::UNDERDIMNR][$x] );
            if( isset( $this->input[self::UNDERDIMNAMN][$x] )) {
                $underDimDto->setDimensionsNamn( $this->input[self::UNDERDIMNAMN][$x] );
            }
            if( isset( $this->input[self::UNDERDIMSUPER][$x] )) {
                $underDimDto->setSuperDimNr((int) $this->input[self::UNDERDIMSUPER][$x] );
            }
            $this->sie4Dto->addUnderDimDto( $underDimDto );
        } // end foreach
    }

    /**
     * Manage Sie4  'Kontoplansuppgifter', #OBJEKT
     *
     * expected as
     * [
     *     ....
     *     self::OBJEKTDIMENSIONNR => [ *<dimId> ],
     *     self::OBJEKTNR          => [ *<objektNr> ],
     *     self::OBJEKTNAMN        => [ *<objektNamn> ],
     *     ....
     * ]
     *
     * @return void
     */
    private function readDimObjektData() : void
    {
        if( ! isset( $this->input[self::OBJEKTDIMENSIONNR] )) {
            return;
        }
        foreach( array_keys( $this->input[self::OBJEKTDIMENSIONNR] ) as $x ) {
            $dimObjektDto = new DimObjektDto();
            $dimObjektDto->setDimensionNr( $this->input[self::OBJEKTDIMENSIONNR][$x] );
            if( isset( $this->input[self::OBJEKTNR][$x] )) {
                $dimObjektDto->setObjektNr( $this->input[self::OBJEKTNR][$x] );
            }
            if( isset( $this->input[self::OBJEKTNAMN][$x] )) {
                $dimObjektDto->setObjektNamn( $this->input[self::OBJEKTNAMN][$x] );
            }
            $this->sie4Dto->addDimObjektDto( $dimObjektDto );
        } // end foreach
    }

    /**
     * Manage Sie4  'Saldoposter', #IB
     *
     * expected as
     * [
     *     ....
     *     // instance data share the same index
     *     self::IBARSNR            => [ *<årsnr> ];
     *     self::IBKONTONR          => [ *<kontoNr> ];
     *     self::IBSALDO            => [ *<saldo> ];
     *     self::IBKVANTITET        => [ *<kvantitet> ];
     *     ....
     * ]
     *
     * @return void
     */
    private function readIbData() : void
    {
        static $KEYS = [ self::IBARSNR, self::IBKONTONR, self::IBSALDO, self::IBKVANTITET ];
        if( isset( $this->input[self::IBARSNR] )) {
            foreach( array_keys( $this->input[self::IBARSNR] ) as $x ) {
                $this->sie4Dto->addIbDto( $this->loadBalansDto( $KEYS, $x ));
            }
        }
    }

    /**
     * Manage Sie4  'Saldoposter', #UB
     *
     * expected as
     * [
     *     ....
     *     // instance data share the same index
     *     self::UBARSNR            => [ *<årsnr> ];
     *     self::UBKONTONR          => [ *<kontoNr> ];
     *     self::UBSALDO            => [ *<saldo> ];
     *     self::UBKVANTITET        => [ *<kvantitet> ];
     *     ....
     * ]
     *
     * @return void
     */
    private function readUbData() : void
    {
        static $KEYS = [ self::UBARSNR, self::UBKONTONR, self::UBSALDO, self::UBKVANTITET ];
        if( isset( $this->input[self::UBARSNR] )) {
            foreach( array_keys( $this->input[self::UBARSNR] ) as $x ) {
                $this->sie4Dto->addUbDto( $this->loadBalansDto( $KEYS, $x ));
            }
        }
    }

    /**
     * @param string[] $keys
     * @param int|string $x
     * @return BalansDto
     */
    private function loadBalansDto( array $keys, int|string $x ) : BalansDto
    {
        $dto = new BalansDto();
        $dto->setArsnr( $this->input[$keys[0]][$x] );
        if( isset( $this->input[$keys[1]][$x] )) {
            $dto->setKontoNr( $this->input[$keys[1]][$x] );
        }
        if( isset( $this->input[$keys[2]][$x] )) {
            $dto->setSaldo( $this->input[$keys[2]][$x] );
        }
        if( isset( $this->input[$keys[3]][$x] )) {
            $dto->setKvantitet( $this->input[$keys[3]][$x] );
        }
        return $dto;
    }


    /**
     * Manage Sie4  'Saldoposter', #OIB
     *
     * expected as
     * [
     *     ....
     *     // instance data share the same index
     *     self::OIBARSNR            => [ *<årsnr> ];
     *     self::OIBKONTONR          => [ *<kontoNr> ];
     *     self::OIBDIMENSIONNR      => [ *<dimId> ];
     *     self::OIBOBJEKTNR         => [ *<objektNr> ];
     *     self::OIBSALDO            => [ *<saldo> ];
     *     self::OIBKVANTITET        => [ *<kvantitet> ];
     *     ....
     * ]
     *
     * @return void
     */
    private function readOibData() : void
    {
        static $KEYS = [
            self::OIBARSNR, self::OIBKONTONR, self::OIBDIMENSIONNR,
            self::OIBOBJEKTNR, self::OIBSALDO, self::OIBKVANTITET
        ];
        if( isset( $this->input[self::OIBARSNR] )) {
            foreach( array_keys( $this->input[self::OIBARSNR] ) as $x ) {
                $this->sie4Dto->addOibDto( $this->loadBalansObjektDto( $KEYS, $x ) );
            }
        }
    }

    /**
     * Manage Sie4  'Saldoposter', #OUB
     *
     * expected as
     * [
     *     ....
     *     // instance data share the same index
     *     self::OUBARSNR            => [ *<årsnr> ];
     *     self::OUBKONTONR          => [ *<kontoNr> ];
     *     self::OUBDIMENSIONNR      => [ *<dimId> ];
     *     self::OUBOBJEKTNR         => [ *<objektNr> ];
     *     self::OUBSALDO            => [ *<saldo> ];
     *     self::OUBKVANTITET        => [ *<kvantitet> ];
     *     ....
     * ]
     *
     * @return void
     */
    private function readOubData() : void
    {
        static $KEYS = [
            self::OUBARSNR, self::OUBKONTONR, self::OUBDIMENSIONNR,
            self::OUBOBJEKTNR, self::OUBSALDO, self::OUBKVANTITET
        ];
        if( isset( $this->input[self::OUBARSNR] )) {
            foreach( array_keys( $this->input[self::OUBARSNR] ) as $x ) {
                $this->sie4Dto->addOubDto( $this->loadBalansObjektDto( $KEYS, $x ));
            }
        }
    }

    /**
     * @param string[] $keys
     * @param int|string $x
     * @return BalansObjektDto
     */
    private function loadBalansObjektDto( array $keys, int|string $x ) : BalansObjektDto
    {
        $dto = new BalansObjektDto();
        $dto->setArsnr( $this->input[$keys[0]][$x] );
        if( isset( $this->input[$keys[1]][$x] )) {
            $dto->setKontoNr( $this->input[$keys[1]][$x] );
        }
        if( isset( $this->input[$keys[2]][$x] )) {
            $dto->setDimensionNr( $this->input[$keys[2]][$x] );
        }
        if( isset( $this->input[$keys[3]][$x] )) {
            $dto->setObjektNr( $this->input[$keys[3]][$x] );
        }
        if( isset( $this->input[$keys[4]][$x] )) {
            $dto->setSaldo( $this->input[$keys[4]][$x] );
        }
        if( isset( $this->input[$keys[5]][$x] )) {
            $dto->setKvantitet( $this->input[$keys[5]][$x] );
        }
        return $dto;
    }

    /**
     * Manage Sie4  'Saldoposter', #RES
     *
     * expected as
     * [
     *     ....
     *     // instance data share the same index
     *     self::RESARSNR            => [ *<årsnr> ];
     *     self::RESKONTONR          => [ *<kontoNr> ];
     *     self::RESSALDO            => [ *<saldo> ];
     *     self::RESKVANTITET        => [ *<kvantitet> ];
     *     ....
     * ]
     *
     * @return void
     */
    private function readResData() : void
    {
        static $KEYS = [ self::RESARSNR, self::RESKONTONR, self::RESSALDO, self::RESKVANTITET ];
        if( isset( $this->input[self::RESARSNR] )) {
            foreach( array_keys( $this->input[self::RESARSNR] ) as $x ) {
                $this->sie4Dto->addSaldoDto( $this->loadBalansDto( $KEYS, $x ) );
            }
        }
    }

    /**
     * Manage Sie4  'Saldoposter', #PSALDO
     *
     * expected as
     * [
     *     ....
     *     // instance data share the same index
     *     self::PSALDOARSNR            => [ *<årsnr> ];
     *     self::PSALDOPERIOD           => [ *<period> ];
     *     self::PSALDOKONTONR          => [ *<kontoNr> ];
     *     self::PSALDODIMENSIONNR      => [ *<dimId> ];
     *     self::PSALDOOBJEKTNR         => [ *<objektNr> ];
     *     self::PSALDOSALDO            => [ *<saldo> ];
     *     self::PSALDOKVANTITET        => [ *<kvantitet> ];
     *     ....
     * ]
     *
     * @return void
     */
    private function readPsaldoData() : void
    {
        static $KEYS = [
            self::PSALDOARSNR, self::PSALDOPERIOD, self::PSALDOKONTONR, self::PSALDODIMENSIONNR,
            self::PSALDOOBJEKTNR, self::PSALDOSALDO, self::PSALDOKVANTITET
        ];
        if( isset( $this->input[self::PSALDOARSNR] )) {
            foreach( array_keys( $this->input[self::PSALDOARSNR] ) as $x ) {
                $this->sie4Dto->addPsaldoDto( $this->loadPeriodDto( $KEYS, $x ) );
            }
        }
    }

    /**
     * Manage Sie4  'Saldoposter', #PBUDGET
     *
     * expected as
     * [
     *     ....
     *     // instance data share the same index
     *     self::PBUDGETARSNR            => [ *<årsnr> ];
     *     self::PBUDGETPERIOD           => [ *<period> ];
     *     self::PBUDGETKONTONR          => [ *<kontoNr> ];
     *     self::PBUDGETDIMENSIONNR      => [ *<dimId> ];
     *     self::PPBUDGETOBJEKTNR         => [ *<objektNr> ];
     *     self::PBUDGETSALDO            => [ *<saldo> ];
     *     self::PBUDGETKVANTITET        => [ *<kvantitet> ];
     *     ....
     * ]
     *
     * @return void
     */
    private function readPbudgetData() : void
    {
        static $KEYS = [
            self::PBUDGETARSNR, self::PBUDGETPERIOD, self::PBUDGETKONTONR, self::PBUDGETDIMENSIONNR,
            self::PBUDGETOBJEKTNR, self::PBUDGETSALDO, self::PBUDGETKVANTITET
        ];
        if( isset( $this->input[self::PBUDGETARSNR] )) {
            foreach( array_keys( $this->input[self::PBUDGETARSNR] ) as $x ) {
                $this->sie4Dto->addPbudgetDto( $this->loadPeriodDto( $KEYS, $x ));
            }
        }
    }

    /**
     * @param string[] $keys
     * @param int|string $x
     * @return PeriodDto
     */
    private function loadPeriodDto( array $keys, int|string $x ) : PeriodDto
    {
        $dto = new PeriodDto();
        $dto->setArsnr( $this->input[$keys[0]][$x] );
        if( isset( $this->input[$keys[1]][$x] )) {
            $dto->setPeriod( $this->input[$keys[1]][$x] );
        }
        if( isset( $this->input[$keys[2]][$x] )) {
            $dto->setKontoNr( $this->input[$keys[2]][$x] );
        }
        if( isset( $this->input[$keys[3]][$x] )) {
            $dto->setDimensionNr( $this->input[$keys[3]][$x] );
        }
        if( isset( $this->input[$keys[4]][$x] )) {
            $dto->setObjektNr( $this->input[$keys[4]][$x] );
        }
        if( isset( $this->input[$keys[5]][$x] )) {
            $dto->setSaldo( $this->input[$keys[5]][$x] );
        }
        if( isset( $this->input[$keys[6]][$x] )) {
            $dto->setKvantitet( $this->input[$keys[6]][$x] );
        }
        return $dto;
    }


    /**
     * Manage Sie4  'Verifikationsposter' with  #TRANS data
     *
     * #VER serie vernr verdatum vertext regdatum sign
     *
     * verdatum mandatory in array input
     * expected as
     * [
     *     ....
     *     self::VERDATUM => [ *<SIE4YYYYMMDD-verdatum> ],
     *     self::VERSERIE => [ *serie> ],
     *     self::VERNR    => [ *<vernr> ],
     *     self::VERTEXT  => [ *<vertext> ],
     *     self::REGDATUM => [ *<SIE4YYYYMMDD-regdatum> ],
     *     self::VERSIGN  => [ *<sign> ],
     *     .... // trans below
     * ]
     *
     * @return void
     */
    private function readVerTransData() : void
    {
        if( ! isset( $this->input[self::VERDATUM] )) {
            return;
        }
        foreach( array_keys( $this->input[self::VERDATUM] ) as $verX ) {
            $verDto = new VerDto();
            if( isset( $this->input[self::VERTIMESTAMP][$verX] )) { // accepts empty
                $verDto->setTimestamp((float) $this->input[self::VERTIMESTAMP][$verX] );
            }
            if( isset( $this->input[self::VERGUID][$verX] ) &&
                ! empty( $this->input[self::VERGUID][$verX] )) {
                $verDto->setCorrelationId( $this->input[self::VERGUID][$verX] );
            }
            $verDto->setVerdatum(
                DateTimeUtil::getDateTime(
                    $this->input[self::VERDATUM][$verX],
                    self::VER,
                    3711
                )
            );
            if( isset( $this->input[self::VERSERIE][$verX] ) &&
                ! empty( $this->input[self::VERSERIE][$verX] )) {
                $verDto->setSerie( $this->input[self::VERSERIE][$verX] );
            }
            if( isset( $this->input[self::VERNR][$verX] ) &&
                ! empty( $this->input[self::VERNR][$verX] )) {
                $verDto->setVernr( $this->input[self::VERNR][$verX] );
            }
            if( isset( $this->input[self::VERTEXT][$verX] ) &&
                ! empty( $this->input[self::VERTEXT][$verX] )) {
                $verDto->setVertext( $this->input[self::VERTEXT][$verX] );
            }
            if( isset( $this->input[self::REGDATUM][$verX] ) &&
                ! empty( $this->input[self::REGDATUM][$verX] )) {
                $verDto->setRegdatum(
                    DateTimeUtil::getDateTime( $this->input[self::REGDATUM][$verX], self::VER, 3712 )
                );
            }
            else {
                $verDto->setRegdatum( $verDto->getVerdatum());
            }
            if( isset( $this->input[self::VERSIGN][$verX] ) &&
                ! empty( $this->input[self::VERSIGN][$verX] )) {
                $verDto->setSign( $this->input[self::VERSIGN][$verX] );
            }
            if( isset( $this->input[self::TRANSKONTONR][$verX] )) {
                $this->readTransData((int) $verX, $verDto );
            }
            $this->sie4Dto->addVerDto( $verDto );
        } // end foreach
    }

    /**
     * expected as (for #TRANS, #RTRANS has self::RTRANSKONTONR etc, #BTRANS has self::BTRANSKONTONR etc)
     * [
     *     self::TRANSKONTONR     => [ *[ *<kontonr> ] ]
     *     self::TRANSDIMENSIONNR => [ *[ *[ *<dimId> ] ] ],
     *     self::TRANSOBJEKTNR    => [ *[ *[ *<objektnr> ] ] ],
     *     self::TRANSBELOPP      => [ *[ *<belopp> ] ],
     *     self::TRANSDAT         => [ *[ *<SIE4YYYYMMDD-transdat> ] ],
     *     self::TRANSTEXT        => [ *[ *<transText> ] ],
     *     self::TRANSKVANTITET   => [ *[ *<kvantitet> ] ],
     *     self::TRANSSIGN        => [ *[ *<sign> ] ],
     * ]
     *
     * @param int    $verX
     * @param VerDto $verDto
     * @return void
     */
    private function readTransData( int $verX, VerDto $verDto ) : void
    {
        static $leadKeys = [ self::TRANSKONTONR, self::RTRANSKONTONR, self::BTRANSKONTONR ];
        $labels = [];
        // preserve order of #TRANS, #RTRANS and #BTRANS
        foreach( array_keys( $this->input ) as $transKey ) {
            if( ! in_array( $transKey, $leadKeys, true )) {
                continue;
            }
            $found = null;
            switch( $transKey ) {
                case ( ! isset( $this->input[$transKey][$verX] )) :
                    break;
                case self::RTRANSKONTONR :
                    $found = self::RTRANS;
                    break;
                case self::BTRANSKONTONR :
                    $found = self::BTRANS;
                    break;
                default :
                    $found = self::TRANS;
                    break;
            } // end switch
            if( ! empty( $found )) {
                foreach( array_keys( $this->input[$transKey][$verX] ) as $tx ) {
                    $labels[$tx] = $found;
                }
            }
        } // end foreach
        ksort( $labels, SORT_NUMERIC );
        $transDtos = [];
        foreach( $labels as $lx => $label ) {
            $keyArr     = self::$TRANSKEYS[$label];
            $keyKontoNr = $keyArr[self::TRANSKONTONR];
            foreach( array_keys( $this->input[$keyKontoNr][$verX] ) as $transX ) {
                if( $lx !== $transX ) {
                    continue;
                }
                $transDto = new TransDto();
                $transDto->setTransType( $label );
                $transDto->setKontoNr( $this->input[$keyKontoNr][$verX][$transX] );
                $this->processTransData( $transDto, $keyArr, $verX, (int) $transX, $label );
                $transDtos[$transX] = $transDto;
                break; // lx found
            } // end foreach
        } // end foreach leadKey
        ksort( $transDtos, SORT_NUMERIC );
        $verDto->setTransDtos( $transDtos );
    }

    /**
     * Process single #TRANS/#RTRANS/#BTRANS
     *
     * @param TransDto $transDto
     * @param array    $keyArr
     * @param int      $verX
     * @param int      $transX
     * @param string   $label
     * @return void
     */
    private function processTransData(
        TransDto  $transDto,
        array  $keyArr,
        int    $verX,
        int    $transX,
        string $label
    ) : void
    {
        $keyTimestamp = $keyArr[self::TRANSTIMESTAMP];
        if( isset( $this->input[$keyTimestamp][$verX][$transX] )) { // accepts empty
            $transDto->setTimestamp((float) $this->input[$keyTimestamp][$verX][$transX] );
        }
        $keyGuid      = $keyArr[self::TRANSGUID];
        if( isset( $this->input[$keyGuid][$verX][$transX] ) &&
            ! empty( $this->input[$keyGuid][$verX][$transX] )) {
            $transDto->setCorrelationId( $this->input[$keyGuid][$verX][$transX] );
        }
        $this->loadObjektlista(
            $keyArr[self::TRANSDIMENSIONNR],
            $keyArr[self::TRANSOBJEKTNR],
            $verX,
            $transX,
            $transDto
        );
        $keyBelopp = $keyArr[self::TRANSBELOPP];
        if( isset( $this->input[$keyBelopp][$verX][$transX] )) { // accepts empty
            $transDto->setBelopp( $this->input[$keyBelopp][$verX][$transX] );
        }
        $keyDatum = $keyArr[self::TRANSDAT];
        if( isset( $this->input[$keyDatum][$verX][$transX] ) &&
            ! empty( $this->input[$keyDatum][$verX][$transX] )) {
            $transDto->setTransdat(
                DateTimeUtil::getDateTime( $this->input[$keyDatum][$verX][$transX], $label, 3713 )
            );
        }
        $keyText = $keyArr[self::TRANSTEXT];
        if( isset( $this->input[$keyText][$verX][$transX] ) &&
            ! empty( $this->input[$keyText][$verX][$transX] )) {
            $transDto->setTranstext( $this->input[$keyText][$verX][$transX] );
        }
        $keyKvantitet = $keyArr[self::TRANSKVANTITET];
        if( isset( $this->input[$keyKvantitet][$verX][$transX] )) { // accepts empty
            $transDto->setKvantitet( $this->input[$keyKvantitet][$verX][$transX] );
        }
        $keySign = $keyArr[self::TRANSSIGN];
        if( isset( $this->input[$keySign][$verX][$transX] ) &&
            ! empty( $this->input[$keySign][$verX][$transX] )) {
            $transDto->setSign( $this->input[$keySign][$verX][$transX] );
        }
    }

    /**
     * @param string $keyDimNr
     * @param string $keyObjektNr
     * @param int $verX
     * @param int $transX
     * @param TransDto $transDto
     */
    private function loadObjektlista(
        string $keyDimNr,
        string $keyObjektNr,
        int    $verX,
        int    $transX,
        TransDto $transDto
    ) : void
    {
        if( ! isset( $this->input[$keyDimNr][$verX][$transX] )) {
            return;
        }
        foreach( array_keys( $this->input[$keyDimNr][$verX][$transX] ) as $doX ) {
            $dimObjektDto = new DimObjektDto();
            $dimObjektDto->setDimensionNr(
                $this->input[$keyDimNr][$verX][$transX][$doX]
            );
            if( isset( $this->input[$keyObjektNr][$verX][$transX][$doX] )) {
                $dimObjektDto->setObjektNr(
                    $this->input[$keyObjektNr][$verX][$transX][$doX]
                );
            }
            $transDto->addObjektlista( $dimObjektDto );
        } // end foreach
    }
}
