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

use Kigkonsult\Sie4Sdk\Dto\BalansDto;
use Kigkonsult\Sie4Sdk\Dto\BalansObjektDto;
use Kigkonsult\Sie4Sdk\Dto\PeriodDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Util\ArrayUtil;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Impl\CommonFactory;

use function array_keys;
use function number_format;

/**
 * Class Sie4Dto2Array
 *
 * Transform any Sie4Dto (4E/4I) to array
 *
 * output format
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
class Sie4Dto2Array extends ArrayBase
{
    /**
     * @var Sie4Dto
     */
    private Sie4Dto $sie4Dto;

    /**
     * @var array
     */
    private array $output = [];

    /**
     * Transform Sie4Dto to array, factory method
     *
     * @param Sie4Dto $sie4Dto
     * @return array
     */
    public static function process( Sie4Dto $sie4Dto ) : array
    {
        $instance          = new self();
        $instance->sie4Dto = $sie4Dto;
        $instance->output  = [];

        $instance->output[self::FLAGGPOST] = $instance->sie4Dto->getFlagga();
        $instance->output[self::TIMESTAMP] = number_format(
            $instance->sie4Dto->getTimestamp(), 6, StringUtil::$DOT, StringUtil::$SP0
        );
        $instance->output[self::GUID]      = $instance->sie4Dto->getCorrelationId();

        $instance->processIdDto();

        $instance->processAccountDtos();
        $instance->processSruDtos();
        $instance->processDimDtos();
        $instance->processUnderDimDtos();
        $instance->processDimObjektDtos();

        $instance->processIbDtos();
        $instance->processUbDtos();
        $instance->processOibDtos();
        $instance->processOubDtos();
        $instance->processResDtos();
        $instance->processPsaldoDtos();
        $instance->processPbudgetDtos();

        $instance->processVerDtos();

        if( $instance->sie4Dto->isKsummaSet()) {
            $instance->output[self::KSUMMAPOST] = $instance->sie4Dto->getKsumma();
        }
        return $instance->getOutput();
    }

    /**
     * Process Sie4 id data
     *
     * @return void
     */
    private function processIdDto() : void
    {
        $idDto = $this->sie4Dto->getIdDto();
        $this->output[self::PROGRAMNAMN]    = $idDto->getProgramnamn();
        $this->output[self::PROGRAMVERSION] = $idDto->getVersion();
        $this->output[self::GENDATUM] = $idDto->getGenDate()->format( self::SIE4YYYYMMDD );
        $this->output[self::GENSIGN]  = $idDto->isSignSet()
            ? $idDto->getSign()
            : self::PRODUCTNAME;
        if( $idDto->isProsaSet()) {
            $this->output[self::PROSATEXT] = $idDto->getProsa();
        }
        if( $idDto->isFtypSet()) {
            $this->output[self::FORETAGSTYP] = $idDto->getFtyp();
        }
        if( $idDto->isFnrIdSet()) {
            $this->output[self::FNRID] = $idDto->getFnrId();
        }
        if( $idDto->isOrgnrSet()) {
            $this->output[self::ORGNRORGNR]  = $idDto->getOrgnr();
            $this->output[self::ORGNRFORNVR] = $idDto->getMultiple();
        }
        if( $idDto->isBkodSet()) {
            $this->output[self::SNIKOD] = $idDto->getBkod();
        }
        if( $idDto->isAdressSet()) {
            $adressDto = $idDto->getAdress();
            $this->output[self::ADRKONTAKT]    = $adressDto->getKontakt();
            $this->output[self::UTDELNINGSADR] = $adressDto->getUtdelningsadr();
            $this->output[self::POSTADR]       = $adressDto->getPostadr();
            $this->output[self::TEL]           = $adressDto->getTel();
        }
        if( $idDto->isFnamnSet()) {
            $this->output[self::FTGNAMN] = $idDto->getFnamn();
        }
        if( 0 < $idDto->countRarDtos()) {
            foreach( $idDto->getRarDtos() as $x => $rarDto ) {
                $this->output[self::RARARSNR][$x] = $rarDto->getArsnr();
                $this->output[self::RARSTART][$x] = $rarDto->getStart()->format( self::SIE4YYYYMMDD );
                $this->output[self::RARSLUT][$x]  = $rarDto->getSlut()->format( self::SIE4YYYYMMDD );
            } // end foreach
        }
        if( $idDto->isTaxarSet()) {
            $this->output[self::TAXYEAR] = $idDto->getTaxar();
        }
        if( $idDto->isOmfattnSet()) {
            $this->output[self::OMFATTNDATUM] = $idDto->getOmfattn()->format( self::SIE4YYYYMMDD );
        }
        if( $idDto->isKptypSet()) {
            $this->output[self::KPTYPE] = $idDto->getKptyp();
        }
        if( $idDto->isValutakodSet()) {
            $this->output[self::VALUTAKOD] = $idDto->getValutakod();
        }
    }

    /**
     * Process Sie4 KONTO data
     *
     * @return void
     */
    private function processAccountDtos() : void
    {
        static $KEYS = [
            self::KONTONR,
            self::KONTONAMN,
            self::KONTOTYP,
            self::KONTOENHET
        ];
        if( empty( $this->sie4Dto->countAccountDtos())) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        foreach( $this->sie4Dto->getAccountDtos() as $x => $accountDto ) {
            if( $accountDto->isKontoNrSet()) {
                $this->output[self::KONTONR][$x]    = $accountDto->getKontoNr();
            }
            if( $accountDto->isKontonamnSet()) {
                $this->output[self::KONTONAMN][$x]  = $accountDto->getKontoNamn();
            }
            if( $accountDto->isKontotypSet()) {
                $this->output[self::KONTOTYP][$x]   = $accountDto->getKontoTyp();
            }
            if( $accountDto->isEnhetSet()) {
                $this->output[self::KONTOENHET][$x] = $accountDto->getEnhet();
            }
        } // end foreach
    }

    /**
     * Process Sie4 SRU data
     *
     * @return void
     */
    private function processSruDtos() : void
    {
        static $KEYS = [
            self::SRUKONTO,
            self::SRUKOD
        ];
        if( empty( $this->sie4Dto->countSruDtos())) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        foreach( $KEYS as $key ) {
            ArrayUtil::assureIsArray( $this->output, $key );
        }
        foreach( $this->sie4Dto->getSruDtos() as $x => $sruDto ) {
            if( $sruDto->isKontoNrSet()) {
                $this->output[self::SRUKONTO][$x] = $sruDto->getKontoNr();
            }
            if( $sruDto->isSruKodSet()) {
                $this->output[self::SRUKOD][$x]   = $sruDto->getSruKod();
            }
        } // end foreach
    }

    /**
     * Process Sie4 DIM data
     *
     * @return void
     */
    private function processDimDtos() : void
    {
        static $KEYS = [
            self::DIMENSIONNR,
            self::DIMENSIONNAMN
        ];
        if( empty( $this->sie4Dto->countDimDtos())) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        foreach( $this->sie4Dto->getDimDtos() as $x => $dimDto ) {
            if( $dimDto->isDimensionsNrSet()) {
                $this->output[self::DIMENSIONNR][$x]   = $dimDto->getDimensionNr();
            }
            if( $dimDto->isDimensionsNamnSet()) {
                $this->output[self::DIMENSIONNAMN][$x] = $dimDto->getDimensionsNamn();
            }
        } // end foreach
    }

    /**
     * Process Sie4 UNDERDIM data
     *
     * @return void
     */
    private function processUnderDimDtos() : void
    {
        static $KEYS = [
            self::UNDERDIMNR,
            self::UNDERDIMNAMN,
            self::UNDERDIMSUPER
        ];
        if( empty( $this->sie4Dto->countUnderDimDtos())) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        foreach( $this->sie4Dto->getUnderDimDtos() as $x => $underDimDto ) {
            if( $underDimDto->isDimensionsNrSet()) {
                $this->output[self::UNDERDIMNR][$x]   = $underDimDto->getDimensionNr();
            }
            if( $underDimDto->isDimensionsNamnSet()) {
                $this->output[self::UNDERDIMNAMN][$x] = $underDimDto->getDimensionsNamn();
            }
            if( $underDimDto->isSuperDimNrSet()) {
                $this->output[self::UNDERDIMSUPER][$x] = $underDimDto->getSuperDimNr();
            }
        } // end foreach
    }

    /**
     * Process Sie4 OBJEKT data
     *
     * @return void
     */
    private function processDimObjektDtos() : void
    {
        static $KEYS = [
            self::OBJEKTDIMENSIONNR,
            self::OBJEKTNR,
            self::OBJEKTNAMN
        ];
        $dimObjektDtos = $this->sie4Dto->getDimObjektDtos();
        if( empty( $dimObjektDtos )) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        foreach( $this->sie4Dto->getDimObjektDtos() as $x => $dimObjektDto ) {
            if( $dimObjektDto->isDimensionsNrSet()) {
                $this->output[self::OBJEKTDIMENSIONNR][$x] =
                    $dimObjektDto->getDimensionNr();
            }
            if( $dimObjektDto->isObjektNrSet()) {
                $this->output[self::OBJEKTNR][$x]   = $dimObjektDto->getObjektNr();
            }
            if( $dimObjektDto->isObjektNamnSet()) {
                $this->output[self::OBJEKTNAMN][$x] = $dimObjektDto->getObjektNamn();
            }
        } // end foreach
    }

    /**
     * Process Sie4 IB data
     *
     * @return void
     */
    private function processIbDtos() : void
    {
        static $KEYS = [
            self::IBARSNR,
            self::IBKONTONR,
            self::IBSALDO,
            self::IBKVANTITET
        ];
        if( ! empty( $this->sie4Dto->countIbDtos())) {
            ArrayUtil::assureIsArray( $this->output, $KEYS );
            foreach( $this->sie4Dto->getIbDtos() as $x => $ibDto ) {
                $this->loadFromBalansDto( $KEYS, $x, $ibDto );
            }
        }
    }

    /**
     * Process Sie4 UB data
     *
     * @return void
     */
    private function processUbDtos() : void
    {
        static $KEYS = [
            self::UBARSNR,
            self::UBKONTONR,
            self::UBSALDO,
            self::UBKVANTITET
        ];
        if( ! empty( $this->sie4Dto->countUbDtos())) {
            ArrayUtil::assureIsArray( $this->output, $KEYS );
            foreach( $this->sie4Dto->getUbDtos() as $x => $ubDto ) {
                $this->loadFromBalansDto( $KEYS, $x, $ubDto );
            }
        }
    }

    /**
     * @param string[] $keys
     * @param int|string $x
     * @param BalansDto $dto
     */
    private function loadFromBalansDto( array $keys, int|string $x, BalansDto $dto ) : void
    {
        if( $dto->isArsnrSet()) {
            $this->output[$keys[0]][$x] = $dto->getArsnr();
        }
        if( $dto->isKontoNrSet()) {
            $this->output[$keys[1]][$x] = $dto->getKontoNr();
        }
        if( $dto->isSaldoSet()) {
            $this->output[$keys[2]][$x] = $dto->getSaldo();
        }
        if( $dto->isKvantitetSet()) {
            $this->output[$keys[3]][$x] = $dto->getKvantitet();
        }
    }

    /**
     * Process Sie4 OIB data
     *
     * @return void
     */
    private function processOibDtos() : void
    {
        static $KEYS = [
            self::OIBARSNR,
            self::OIBKONTONR,
            self::OIBDIMENSIONNR,
            self::OIBOBJEKTNR,
            self::OIBSALDO,
            self::OIBKVANTITET
        ];
        if( ! empty( $this->sie4Dto->countOibDtos())) {
            ArrayUtil::assureIsArray( $this->output, $KEYS );
            foreach( $this->sie4Dto->getOibDtos() as $x => $oibDto ) {
                $this->loadFromBalansObjektDto( $KEYS, $x, $oibDto );
            }
        }
    }

    /**
     * Process Sie4 OUB data
     *
     * @return void
     */
    private function processOubDtos() : void
    {
        static $KEYS = [
            self::OUBARSNR,
            self::OUBKONTONR,
            self::OUBDIMENSIONNR,
            self::OUBOBJEKTNR,
            self::OUBSALDO,
            self::OUBKVANTITET
        ];
        if( ! empty( $this->sie4Dto->countOubDtos())) {
            ArrayUtil::assureIsArray( $this->output, $KEYS );
            foreach( $this->sie4Dto->getOubDtos() as $x => $oubDto ) {
                $this->loadFromBalansObjektDto( $KEYS, $x, $oubDto );
            }
        }
    }

    /**
     * @param string[] $keys
     * @param int|string $x
     * @param BalansObjektDto $dto
     */
    private function loadFromBalansObjektDto( array $keys, int|string $x, BalansObjektDto $dto ) : void
    {
        if( $dto->isArsnrSet()) {
            $this->output[$keys[0]][$x] = $dto->getArsnr();
        }
        if( $dto->isKontoNrSet()) {
            $this->output[$keys[1]][$x] = $dto->getKontoNr();
        }
        if( $dto->isDimensionsNrSet()) {
            $this->output[$keys[2]][$x] = $dto->getDimensionNr();
        }
        if( $dto->isObjektNrSet()) {
            $this->output[$keys[3]][$x] = $dto->getObjektNr();
        }
        if( $dto->isSaldoSet()) {
            $this->output[$keys[4]][$x] = $dto->getSaldo();
        }
        if( $dto->isKvantitetSet()) {
            $this->output[$keys[5]][$x] = $dto->getKvantitet();
        }
    }

    /**
     * Process Sie4 RES data
     *
     * @return void
     */
    private function processResDtos() : void
    {
        static $KEYS = [
            self::RESARSNR,
            self::RESKONTONR,
            self::RESSALDO,
            self::RESKVANTITET
        ];
        if( ! empty( $this->sie4Dto->countSaldoDtos())) {
            ArrayUtil::assureIsArray( $this->output, $KEYS );
            foreach( $this->sie4Dto->getSaldoDtos() as $x => $resDto ) {
                $this->loadFromBalansDto( $KEYS, $x, $resDto );
            }
        }
    }

    /**
     * Process Sie4 PSALDO data
     *
     * @return void
     */
    private function processPsaldoDtos() : void
    {
        static $KEYS = [
            self::PSALDOARSNR,
            self::PSALDOPERIOD,
            self::PSALDOKONTONR,
            self::PSALDODIMENSIONNR,
            self::PSALDOOBJEKTNR,
            self::PSALDOSALDO,
            self::PSALDOKVANTITET
        ];
        if( ! empty( $this->sie4Dto->countPsaldoDtos())) {
            ArrayUtil::assureIsArray( $this->output, $KEYS );
            foreach( $this->sie4Dto->getPsaldoDtos() as $x => $pSaldoDto ) {
                $this->loadFromPeriodDto( $KEYS, $x, $pSaldoDto );
            }
        }
    }

    /**
     * Process Sie4 PBUDGET data
     *
     * @return void
     */
    private function processPbudgetDtos() : void
    {
        static $KEYS = [
            self::PBUDGETARSNR,
            self::PBUDGETPERIOD,
            self::PBUDGETKONTONR,
            self::PBUDGETDIMENSIONNR,
            self::PBUDGETOBJEKTNR,
            self::PBUDGETSALDO,
            self::PBUDGETKVANTITET
        ];
        if( ! empty( $this->sie4Dto->countPbudgetDtos())) {
            ArrayUtil::assureIsArray( $this->output, $KEYS );
            foreach( $this->sie4Dto->getPbudgetDtos() as $x => $pBudgetDto ) {
                $this->loadFromPeriodDto( $KEYS, $x, $pBudgetDto );
            }
        }
    }

    /**
     * @param string[] $keys
     * @param int|string $x
     * @param PeriodDto $dto
     */
    private function loadFromPeriodDto( array $keys, int|string $x, PeriodDto $dto ) : void
    {
        if( $dto->isArsnrSet()) {
            $this->output[$keys[0]][$x] = $dto->getArsnr();
        }
        if( $dto->isPeriodSet()) {
            $this->output[$keys[1]][$x] = $dto->getPeriod();
        }
        if( $dto->isKontoNrSet()) {
            $this->output[$keys[2]][$x] = $dto->getKontoNr();
        }
        if( $dto->isDimensionsNrSet()) {
            $this->output[$keys[3]][$x] = $dto->getDimensionNr();
        }
        if( $dto->isObjektNrSet()) {
            $this->output[$keys[4]][$x] = $dto->getObjektNr();
        }
        if( $dto->isSaldoSet()) {
            $this->output[$keys[5]][$x] = $dto->getSaldo();
        }
        if( $dto->isKvantitetSet()) {
            $this->output[$keys[6]][$x] = $dto->getKvantitet();
        }
    }

    /**
     * Process Sie4 VER with TRANS/RTRANS/BTRANS data
     *
     * @return void
     * @since 1.8.4 20230925
     */
    private function processVerDtos() : void
    {
        static $KEYS = [
            self::VERTIMESTAMP,
            self::VERGUID,
            self::VERPARENTGUID,
            self::VERSERIE,
            self::VERNR,
            self::VERDATUM,
            self::VERTEXT,
            self::REGDATUM,
            self::VERSIGN,
            self::TRANSTIMESTAMP,
            self::TRANSGUID,
            self::TRANSPARENTGUID,
            self::TRANSKONTONR,
            self::TRANSDIMENSIONNR,
            self::TRANSOBJEKTNR,
            self::TRANSBELOPP,
            self::TRANSDAT,
            self::TRANSTEXT,
            self::TRANSKVANTITET,
            self::TRANSSIGN,
            self::RTRANSTIMESTAMP,
            self::RTRANSGUID,
            self::RTRANSPARENTGUID,
            self::RTRANSKONTONR,
            self::RTRANSDIMENSIONNR,
            self::RTRANSOBJEKTNR,
            self::RTRANSBELOPP,
            self::RTRANSDAT,
            self::RTRANSTEXT,
            self::RTRANSKVANTITET,
            self::RTRANSSIGN,
            self::BTRANSTIMESTAMP,
            self::BTRANSGUID,
            self::RTRANSPARENTGUID,
            self::BTRANSKONTONR,
            self::BTRANSDIMENSIONNR,
            self::BTRANSOBJEKTNR,
            self::BTRANSBELOPP,
            self::BTRANSDAT,
            self::BTRANSTEXT,
            self::BTRANSKVANTITET,
            self::BTRANSSIGN
        ];
        if( empty( $this->sie4Dto->countVerDtos())) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        foreach( $this->sie4Dto->getVerDtos() as $verX => $verDto ) {

            $this->output[self::VERTIMESTAMP][$verX]  = number_format(
                $verDto->getTimestamp(), 6, StringUtil::$DOT, StringUtil::$SP0
            );
            $this->output[self::VERGUID][$verX]       = $verDto->getCorrelationId();
            $this->output[self::VERPARENTGUID][$verX] = $verDto->getParentCorrelationId();

            if( $verDto->isSerieSet()) {
                $this->output[self::VERSERIE][$verX]  = $verDto->getSerie();
            }
            if( $verDto->isVernrSet()) {
                $this->output[self::VERNR][$verX]     = $verDto->getVernr();
            }
            if( $verDto->isVerdatumSet()) {
                $this->output[self::VERDATUM][$verX]  =
                    $verDto->getVerdatum()->format( self::SIE4YYYYMMDD );
            }
            if( $verDto->isVertextSet()) {
                $this->output[self::VERTEXT][$verX]   = $verDto->getVertext();
            }
            if( $verDto->isRegdatumSet()) {
                $this->output[self::REGDATUM][$verX]  =
                    $verDto->getRegdatum()->format( self::SIE4YYYYMMDD );
            }
            if( $verDto->isSignSet()) {
                $this->output[self::VERSIGN][$verX]   = $verDto->getSign();
            }
            foreach( $verDto->getTransDtos() as $transX => $transDto ) {
                $this->processSingleTransDto( $verX, $transX, $transDto );
            }
        } // end foreach
    }

    /**
     * @param int      $verX   ver order no
     * @param int      $transX trans order no (in ver)
     * @param TransDto $transDto
     * @return void
     * @since 1.8.4 20230925
     */
    private function processSingleTransDto(
        int $verX,
        int $transX,
        TransDto $transDto
    ) : void
    {
        $label    = $transDto->getTransType();
        $keyArr   = self::$TRANSKEYS[$label];

        $key      = $keyArr[self::TRANSTIMESTAMP];
        $this->output[$key][$verX][$transX] =
            number_format(
                $transDto->getTimestamp(), 6, StringUtil::$DOT, StringUtil::$SP0
            );
        $key      = $keyArr[self::TRANSGUID];
        $this->output[$key][$verX][$transX] = $transDto->getCorrelationId();
        $key      = $keyArr[self::TRANSPARENTGUID];
        $this->output[$key][$verX][$transX] = $transDto->getParentCorrelationId();

        if( $transDto->isKontoNrSet()) {
            $key  = $keyArr[self::TRANSKONTONR];
            $this->output[$key][$verX][$transX] = $transDto->getKontoNr();
        }
        if( 0 < $transDto->countObjektlista()) {
            $key  = $keyArr[self::TRANSDIMENSIONNR];
            $key2 = $keyArr[self::TRANSOBJEKTNR];
            foreach( $transDto->getObjektlista() as $doX => $dimObjektDto ) {
                $this->output[$key][$verX][$transX][$doX]    =
                    $dimObjektDto->getDimensionNr();
                $this->output[$key2][$verX][$transX][$doX] =
                    $dimObjektDto->getObjektNr();
            } // end foreach
        }
        if( $transDto->isBeloppSet()) {
            $key = $keyArr[self::TRANSBELOPP];
            $this->output[$key][$verX][$transX] =
                CommonFactory::formatAmount( $transDto->getBelopp() ?? 0.0 );
        }
        if( $transDto->isTransdatSet()) {
            $key = $keyArr[self::TRANSDAT];
            $this->output[$key][$verX][$transX] =
                $transDto->getTransdat()->format( self::SIE4YYYYMMDD );
        }
        if( $transDto->isTranstextSet()) {
            $key                                = $keyArr[self::TRANSTEXT];
            $this->output[$key][$verX][$transX] = $transDto->getTranstext();
        }
        if( $transDto->isKvantitetSet()) {
            $key                                = $keyArr[self::TRANSKVANTITET];
            $this->output[$key][$verX][$transX] = $transDto->getKvantitet();
        }
        if( $transDto->isSignSet()) {
            $key                                = $keyArr[self::TRANSSIGN];
            $this->output[$key][$verX][$transX] = $transDto->getSign();
        }
    }

    /**
     * @return int[]|float[]|string[]
     */
    public function getOutput() : array
    {
        foreach( array_keys( $this->output ) as $key ) {
            if(( 0 == $this->output[$key] ) || ( 0.0 == $this->output[$key] )) { // Note ==
                continue;
            }
            if( empty( $this->output[$key] )) {
                unset( $this->output[$key] );
            }
        }
        return $this->output;
    }
}
