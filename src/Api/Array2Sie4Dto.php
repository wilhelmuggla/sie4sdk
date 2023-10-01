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
namespace Kigkonsult\Sie4Sdk\Api;

use Exception;
use InvalidArgumentException;
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
use function array_merge;
use function array_unique;
use function is_array;
use function ksort;

/**
 * Class Array2Sie4Dto
 *
 * Transform (HTTP, $_REQUEST) input array to Sie4Dto (4E/4I)
 *
 * input format
 * [
 *     self::FLAGGPOST          => <0/1>,
 *
 *     self::TIMESTAMP          => <microtime>
 *     self::GUID               => <guid>
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
 *     self::VERSERIE           => [ *<serie> ],
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
    private static string $ERR3  = '%s missing';
    private static string $ERR3part  = '%s[%s][%s] missing';
    private static string $ERR10     = ' must be an array';
    private static string $ERR10part = '%s[%s][%s] must be an array';

    /**
     * @var array
     */
    private array $input = [];

    /**
     * @var Sie4Dto
     */
    private Sie4Dto $sie4Dto;

    /**
     * @var VerDto
     */
    private VerDto $currentVerDto;

    /**
     * @var TransDto
     */
    private TransDto $currentTransDto;

    /**
     * Transform Sie4 array to SieDto, factory method
     *
     * @param array $input
     * @return Sie4Dto
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
            try {
                $idDto->setGenDate(
                    DateTimeUtil::getDateTime( $this->input[self::GENDATUM], self::GEN, 3511 )
                );
            }
            catch( Exception $e ) {
                throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
            }
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
         * Obligatorisk, valfri i sie4Dto (FileInfoTypeEntry/CompanyTypeEntry)
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
        else {
            throw new InvalidArgumentException( self::FTGNAMN . self::$ERR3, 3517 );
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
            try {
                $idDto->setOmfattn(
                    DateTimeUtil::getDateTime( $this->input[self::OMFATTNDATUM], self::OMFATTN, 3519 )
                );
            }
            catch( Exception $e ) {
                throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
            }
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
        if( isset( $this->input[self::FLAGGPOST] )) {
            $this->sie4Dto->setFlagga((int) $this->input[self::FLAGGPOST] );
        }
        if( isset( $this->input[self::TIMESTAMP] )) {
            $this->sie4Dto->setTimestamp((float) $this->input[self::TIMESTAMP] );
        }
        if( isset( $this->input[self::GUID] )) {
            $this->sie4Dto->setCorrelationId( $this->input[self::GUID] );
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
     * @throws InvalidArgumentException
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
                try {
                    $rarDto->setStart(
                        DateTimeUtil::getDateTime( $this->input[self::RARSTART][$x], self::RAR, 6788 )
                    );
                }
                catch( Exception $e ) {
                    throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
                }
            } // end if
            if( isset( $this->input[self::RARSLUT][$x] )) {
                try {
                    $rarDto->setSlut(
                        DateTimeUtil::getDateTime( $this->input[self::RARSLUT][$x], self::RAR, 6789 )
                    );
                }
                catch( Exception $e ) {
                    throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
                }
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * Return populated PeriodDto
     *
     * The period property (YYYYmm) value is asserted in the Dto::setPeriod() method
     *
     * @param string[] $keys
     * @param int|string $x
     * @return PeriodDto
     * @throws InvalidArgumentException
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
     * Timestamp/guid autocreated if missing, parentGuid auto from SieDto
     *
     * VERDATUM mandatory in array input
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
     * @since 1.8.4 20230926
     */
    private function readVerTransData() : void
    {
        $key = self::VERDATUM;
        if( ! isset( $this->input[$key] )) {
            return;
        }
        if( ! is_array( $this->input[$key] )) {
            throw new InvalidArgumentException( $key . self::$ERR10, 3710 );
        }
        foreach( array_keys( $this->input[$key] ) as $verX ) {
            $this->currentVerDto = new VerDto();
            $key    = self::VERTIMESTAMP;
            if( $this->verKeyExists( $key, $verX )) {
                $this->currentVerDto->setTimestamp((float) $this->input[$key][$verX] );
            }
            $key    = self::VERGUID;
            if( $this->verKeyExists( $key, $verX )) {
                $this->currentVerDto->setCorrelationId( $this->input[$key][$verX] );
            }
            try {
                $this->currentVerDto->setVerdatum(
                    DateTimeUtil::getDateTime(
                        $this->input[self::VERDATUM][$verX],
                        self::VER,
                        3711
                    )
                );
            }
            catch( Exception $e ) {
                throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
            }
            $key    = self::VERSERIE;
            if( $this->verKeyExists( $key, $verX )) {
                $this->currentVerDto->setSerie( $this->input[$key][$verX] );
            }
            $key    = self::VERNR;
            if( $this->verKeyExists( $key, $verX )) {
                $this->currentVerDto->setVernr((int) $this->input[$key][$verX] );
            }
            $key    = self::VERTEXT;
            if( $this->verKeyExists( $key, $verX )) {
                $this->currentVerDto->setVertext( $this->input[$key][$verX] );
            }
            $key    = self::REGDATUM;
            if( $this->verKeyExists( $key, $verX )) {
                try {
                    $this->currentVerDto->setRegdatum(
                        DateTimeUtil::getDateTime( $this->input[$key][$verX], self::VER, 3712 )
                    );
                }
                catch( Exception $e ) {
                    throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
                }
            }
            else {
                $this->currentVerDto->setRegdatum( $this->currentVerDto->getVerdatum());
            }
            $key    = self::VERSIGN;
            if( $this->verKeyExists( $key, $verX )) {
                $this->currentVerDto->setSign( $this->input[$key][$verX] );
            }
            $this->readTransData((int) $verX );
            $this->sie4Dto->addVerDto( $this->currentVerDto );
        } // end foreach
    }

    /**
     * Return bol true if $this->input[<verKey>] exists and is array and element verX not empty
     *
     * @param string $key
     * @param int $verX
     * @return bool
     */
    private function verKeyExists( string $key, int $verX ) : bool
    {
        return ( isset( $this->input[$key] ) &&
            is_array( $this->input[$key] ) &&
            ! empty( $this->input[$key][$verX] ));
    }

    /**
     * Timestamp/guid autocreated if missing, parentGuid auto from VerDto
     *
     * #TRANS kontonr {objektlista} belopp transdat transtext kvantitet sign
     *
     * KONTNR/BELOPP mandatory in array input
     *
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
     * @return void
     * @throws InvalidArgumentException
     * @since 1.8.4 20230926
     */
    private function readTransData( int $verX ) : void
    {
        $labels         = $this->transDataValidation( $verX );
        $transDtos      = [];
        foreach( $labels as $lx => $label ) {
            $keyArr     = self::$TRANSKEYS[$label];
            $keyKontoNr = $keyArr[self::TRANSKONTONR];
            $keybelopp  = $keyArr[self::TRANSBELOPP];
            foreach( array_keys( $this->input[$keyKontoNr][$verX] ) as $transX ) {
                if( $lx !== $transX ) {
                    continue;
                }
                $this->currentTransDto = TransDto::factory(
                    $this->input[$keyKontoNr][$verX][$transX],
                    (float) $this->input[$keybelopp][$verX][$transX],
                    $label
                );
                $this->processTransData( $keyArr, $verX, (int) $transX, $label );
                $transDtos[$transX] = $this->currentTransDto;
                break; // lx found
            } // end foreach
        } // end foreach leadKey
        ksort( $transDtos, SORT_NUMERIC );
        $this->currentVerDto->setTransDtos( $transDtos );
    }

    /**
     * Validates (each) trans is ok, kontoNr/belopp MUST exist, return string[]
     *
     * Preserve order of #TRANS, #RTRANS and #BTRANS
     *
     * @param int $verX
     * @return string[]
     * @throws InvalidArgumentException
     * @since 1.8.4 20230926
     */
    private function transDataValidation( int $verX ) : array
    {
        static $leadKeys0 = [ self::TRANSKONTONR, self::RTRANSKONTONR, self::BTRANSKONTONR ];
        static $leadKeys1 = [ self::TRANSBELOPP, self::RTRANSBELOPP, self::BTRANSBELOPP ];
        static $leadKeys2 = [ self::TRANS, self::RTRANS, self::BTRANS ];
        $labels = [];
        foreach( [ 0, 1, 2 ] as $tx1 ) {
            $keyKontoNr = $leadKeys0[$tx1];
            $keybelopp  = $leadKeys1[$tx1];
            if( ! isset( $this->input[$keyKontoNr][$verX] ) && ! isset( $this->input[$keybelopp][$verX] )) {
                continue;
            }
            if( isset( $this->input[$keyKontoNr][$verX] ) and ! isset( $this->input[$keybelopp][$verX] )) {
                throw new InvalidArgumentException( $keybelopp . self::$ERR10, 3902 );
            }
            if( ! isset( $this->input[$keyKontoNr][$verX] ) and isset( $this->input[$keybelopp][$verX] )) {
                throw new InvalidArgumentException( $keyKontoNr . self::$ERR10, 4003 );
            }
            $transKeys = array_unique(
                array_merge(
                    array_keys( $this->input[$keyKontoNr][$verX] ?? [] ),
                    array_keys( $this->input[$keybelopp][$verX] ?? [] )
                )
            );
            foreach( $transKeys as $tx2 ) {
                if ( ! isset( $this->input[$keyKontoNr][$verX][$tx2] )) {
                    throw new InvalidArgumentException(
                        sprintf( self::$ERR3part, $keyKontoNr, $verX, $tx2 ),
                        ( 4000 + (int) $tx2 )
                    );
                }
                if ( ! isset( $this->input[$keybelopp][$verX][$tx2] )) {
                    throw new InvalidArgumentException(
                        sprintf( self::$ERR3part, $keybelopp, $verX, $tx2 ),
                        ( 4100 + (int) $tx2 )
                    );
                }
                $labels[$tx2] = $leadKeys2[$tx1];
            } // end foreach
        } // end foreach
        if( empty( $labels )) {
            throw new InvalidArgumentException(
                implode( DIRECTORY_SEPARATOR, $leadKeys2 ) . self::$ERR10,
                4209
            );
        }
        ksort( $labels, SORT_NUMERIC );
        return $labels;
    }

    /**
     * Process single #TRANS/#RTRANS/#BTRANS
     *
     * Sets all but kontoNr/belopp
     *
     * @param string[] $keyArr
     * @param int      $verX
     * @param int      $transX
     * @param string   $label
     * @return void
     * @throws InvalidArgumentException
     */
    private function processTransData(
        array  $keyArr,
        int    $verX,
        int    $transX,
        string $label
    ) : void
    {
        $key = $keyArr[self::TRANSTIMESTAMP];
        if( $this->transKeyExists( $key, $verX, $transX )) { // accepts empty
            $this->currentTransDto->setTimestamp((float) $this->input[$key][$verX][$transX] );
        }
        $key = $keyArr[self::TRANSGUID];
        if( $this->transKeyExists( $key, $verX, $transX ) &&
            ! empty( $this->input[$key][$verX][$transX] )) {
            $this->currentTransDto->setCorrelationId( $this->input[$key][$verX][$transX] );
        }
        $keyDimNr    = $keyArr[self::TRANSDIMENSIONNR];
        $keyObjektNr = $keyArr[self::TRANSOBJEKTNR];
        if( isset( $this->input[$keyDimNr][$verX][$transX] )) {
            $this->assertObjektlista( $keyDimNr, $keyObjektNr, $verX, $transX );
            $this->loadObjektlista( $keyDimNr, $keyObjektNr, $verX, $transX );
        }
        $key = $keyArr[self::TRANSDAT];
        if( $this->transKeyExists( $key, $verX, $transX ) &&
            ! empty( $this->input[$key][$verX][$transX] )) {
            try {
                $this->currentTransDto->setTransdat(
                    DateTimeUtil::getDateTime( $this->input[$key][$verX][$transX], $label, 4301 )
                );
            }
            catch( Exception $e ) {
                throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
            }
        }

        $key = $keyArr[self::TRANSTEXT];
        if( $this->transKeyExists( $key, $verX, $transX ) &&
            ! empty( $this->input[$key][$verX][$transX] )) {
            $this->currentTransDto->setTranstext( $this->input[$key][$verX][$transX] );
        }
        $key = $keyArr[self::TRANSKVANTITET];
        if( $this->transKeyExists( $key, $verX, $transX )) { // accepts empty
            $this->currentTransDto->setKvantitet( $this->input[$key][$verX][$transX] );
        }
        $key = $keyArr[self::TRANSSIGN];
        if( $this->transKeyExists( $key, $verX, $transX ) &&
            ! empty( $this->input[$key][$verX][$transX] )) {
            $this->currentTransDto->setSign( $this->input[$key][$verX][$transX] );
        }
    }

    /**
     * Return bol true if $this->input[<transKey>][verX] exists and is array and element transX exists
     *
     * @param string $key
     * @param int $verX
     * @param int $transX
     * @return bool
     */
    private function transKeyExists( string $key, int $verX, int $transX ) : bool
    {
        return ( isset( $this->input[$key] ) && is_array( $this->input[$key] ) &&
            isset( $this->input[$key][$verX] ) && is_array( $this->input[$key][$verX] ) &&
            isset( $this->input[$key][$verX][$transX] ));
    }

    /**
     * @param string $keyDimNr
     * @param string $keyObjektNr
     * @param int $verX
     * @param int $transX
     * @throws InvalidArgumentException
     */
    private function assertObjektlista( string $keyDimNr, string $keyObjektNr, int $verX, int $transX ) : void
    {
        static $ERR4part  = '%s[%s][%s][%s] missing';
        if( ! is_array( $this->input[$keyDimNr][$verX][$transX] )) {
            throw new InvalidArgumentException(
                sprintf( self::$ERR10part, $keyDimNr, $verX, $transX ),
                4411
            );
        }
        if( ! isset( $this->input[$keyObjektNr][$verX][$transX] ) ||
            ! is_array( $this->input[$keyObjektNr][$verX][$transX] )) {
            throw new InvalidArgumentException(
                sprintf( self::$ERR10part, $keyObjektNr, $verX, $transX ),
                4412
            );
        }
        foreach( array_keys( $this->input[$keyDimNr][$verX][$transX] ) as $doX ) {
            if ( empty( $this->input[$keyDimNr][$verX][$transX][$doX] )) {
                throw new InvalidArgumentException(
                    sprintf( $ERR4part, $keyDimNr, $verX, $transX, $doX ),
                    4413
                );
            }
            if ( empty( $this->input[$keyObjektNr][$verX][$transX][$doX] )) {
                throw new InvalidArgumentException(
                    sprintf( $ERR4part, $keyObjektNr, $verX, $transX, $doX ),
                    4414
                );
            }
        } // end foreach
    }

    /**
     * @param string $keyDimNr
     * @param string $keyObjektNr
     * @param int $verX
     * @param int $transX
     * @throws InvalidArgumentException
     */
    private function loadObjektlista( string $keyDimNr, string $keyObjektNr, int $verX, int $transX ) : void
    {
        foreach( array_keys( $this->input[$keyDimNr][$verX][$transX] ) as $doX ) {
            if( empty( $this->input[$keyObjektNr][$verX][$transX][$doX] )) {
                throw new InvalidArgumentException(
                    sprintf( self::$ERR10part, $keyObjektNr, $verX, $transX ),
                    4414
                );
            }
            $this->currentTransDto->addObjektlista(
                DimObjektDto::factoryDimObject(
                    $this->input[$keyDimNr][$verX][$transX][$doX],
                    $this->input[$keyObjektNr][$verX][$transX][$doX]
                )
            );
        } // end foreach
    }
}
