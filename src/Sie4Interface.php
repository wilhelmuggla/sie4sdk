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
namespace Kigkonsult\Sie4Sdk;

Interface Sie4Interface
{
    /**
     * Flaggpost
     */

    /**
     * Flaggpost som anger om filen tagits emot av mottagaren
     * obligatorisk i Sie4
     */
    const FLAGGA                   = '#FLAGGA';
    const FLAGGPOST                = 'FLAGGA';

    /**
     * Product constants
     */
    const PRODUCTNAME              = 'Kigkonsult\Sie4Sdk';
    const PRODUCTVERSION           = '1.4';

    /**
     * Constants for Sie4 labels and sub-labels values
     * as defined in 'SIE filformat - Utgåva 4B'.
     * ALL constant values in upper case.
     * If lable has only one sub-field, NO sub-label defined
     * For skipped lables, no sub-lables defined
     */

    /**
     * Identifikationsposter
     */

     /**
      * Vilket program som genererat filen
      * #PROGRAM programnamn version
      * obligatorisk
      */
    const PROGRAM                  = '#PROGRAM';
    const PROGRAMNAMN              = 'PROGRAMNAMN';
    const PROGRAMVERSION           = 'PROGRAMVERSION';

     /**
      * Vilken teckenuppsättning som använts
      *
      * SKA vara IBM PC 8-bitars extended ASCII (Codepage 437)
      * https://en.wikipedia.org/wiki/Code_page_437
      * obligatorisk i Sie4, auto
      */
    const FORMAT                   = '#FORMAT';

     /**
      * När och av vem som filen genererats
      * #GEN datum sign
      * Obligatorisk (sign opt) Sie4, båda obl. Sie5 SieEntry
      */
    const GEN                      = '#GEN';
    const GENDATUM                 = 'GENDATUM';
    const GENSIGN                  = 'GENSIGN';

     /**
      * Vilken typ av SIE-formatet filen följer
      * obligatorisk, auto i Sie4 : 4
      */
    const SIETYP                   = '#SIETYP';

     /**
      * Fri kommentartext kring filens innehåll
      * valfri
      */
    const PROSA                    = '#PROSA';
    const PROSATEXT                = 'PROSATEXT';

     /**
      * Företagstyp
      * valfri
      */
    const FTYP                     = '#FTYP';
    const FORETAGSTYP              = 'FORETAGSTYP';

     /**
      * Redovisningsprogrammets internkod för exporterat företag
      * #FNR företagsid
      * valfri
      */
    const FNR                      = '#FNR';
    const FNRID                    = 'FNRID';

     /**
      * Organisationsnummer för det företag som exporterats
      * #ORGNR orgnr förvnr verknr
      * förvnr : anv då ensk. person driver flera ensk. firmor (ordningsnr)
      * verknr : anv ej
      * valfri i sieDto, obl (orgnr) i SirEntry
      */
    const ORGNR                    = '#ORGNR';
    const ORGNRORGNR               = 'ORGNRORGNR';
    const ORGNRFORNVR              = 'ORGNRFORNVR';

    /**
     * Branschtillhörighet för det exporterade företaget, Sie4E only
     * valfri
     */
    const BKOD                     = '#BKOD';
    const SNIKOD                   = 'SNIKOD';

    /**
     * Adressuppgifter för det aktuella företaget
     * valfri
     */
    const ADRESS                   = '#ADRESS';
    const ADRKONTAKT               = 'ADRKONTAKT';
    const UTDELNINGSADR            = 'UTDELNINGSADR';
    const POSTADR                  = 'POSTADR';
    const TEL                      = 'TEL';

     /**
      * Fullständigt namn för det företag som exporterats
      * #FNAMN företagsnamn
      * Obligatorisk i Sie4, valfri i SieEntry
      */
    const FNAMN                    = '#FNAMN';
    const FTGNAMN                  = 'FNAMN';

     /**
      * Räkenskapsår från vilket exporterade data hämtats
      * valfri
      */
    const RAR                      = '#RAR';
    const RARARSNR                 = 'RARASRNR';
    const RARSTART                 = 'RARSTART';
    const RARSLUT                  = 'RARSLUT';

     /**
      * Taxeringsår för deklarations- information (SRU-koder)
      * valfri
      */
    const TAXAR                    = '#TAXAR';
    const TAXYEAR                  = 'TAXYEAR';

    /**
     * Datum för periodsaldons omfattning
     * #OMFATTN datum
     */
    const OMFATTN                  = '#OMFATTN';
    const OMFATTNDATUM             = 'OMFATTNDATUM';

     /**
      * Kontoplanstyp
      * valfri
      */
    const KPTYP                    = '#KPTYP';
    const KPTYPE                   = 'KPTYPE';

     /**
      * Redovisningsvaluta
      * #VALUTA valutakod
      * valfri
      */
    const VALUTA                   = '#VALUTA';
    const VALUTAKOD                = 'VALUTAKOD';


    /**
     * Kontoplansuppgifter
     */

     /**
      * Kontouppgifter
      * #KONTO kontonr kontonamn
      * valfri
      */
    const KONTO                    = '#KONTO';
    const KONTONR                  = 'KONTONR';
    const KONTONAMN                = 'KONTONAMN';

     /**
      * Kontotyp
      * #KTYP kontonr  kontoTyp
      * valfri
      */
    const KTYP                     = '#KTYP';
    const KONTOTYP                 = 'KONTOTYP';

     /**
      * Enhet vid kvantitetsredovisning
      * #ENHET kontonr enhet
      * valfri
      */
    const ENHET                    = '#ENHET';
    const KONTOENHET               = 'KONTOENHET';

     /**
      * RSV-kod för standardiserat räkenskapsutdrag
      * valfri
      */
    const SRU                      = '#SRU';
    const SRUKONTO                 = 'SRUKONTO';
    const SRUKOD                   = 'SRUKOD';

     /**
      * Dimension
      * #DIM dimensionsnr namn
      * valfri
      */
    const DIM                      = '#DIM';
    const DIMENSIONNR              = 'DIMENSIONNR';
    const DIMENSIONNAMN            = 'DIMENSIONNAMN';

     /**
      * Underdimension
      * valfri, ignoreras
      */
    const UNDERDIM                 = '#UNDERDIM';

     /**
      * Objekt
      * #OBJEKT dimensionsnr objektnr objektnamn
      * valfri
      */
    const OBJEKT                   = '#OBJEKT';
    const OBJEKTDIMENSIONNR        = 'OBJEKTDIMENSIONNR';
    const OBJEKTNR                 = 'OBJEKTNR';
    const OBJEKTNAMN               = 'OBJEKTNAMN';

    /**
     * Saldoposter
     */
    const IB                       = '#IB';
    const IBARSNR                  = 'IBARSNR';
    const IBKONTONR                = 'IBKONTONR';
    const IBSALDO                  = 'IBSALDO';
    const IBKVANTITET              = 'IBKVANTITET';

    const UB                       = '#UB';
    // årsnr konto saldo kvantitet(opt)
    const UBARSNR                  = 'UBARSNR';
    const UBKONTONR                = 'UBKONTONR';
    const UBSALDO                  = 'UBSALDO';
    const UBKVANTITET              = 'UBKVANTITET';

    const OIB                      = '#OIB';
    // årsnr konto {dimensionsnr objektnr} saldo kvantitet(opt)
    const OIBARSNR                 = 'OIBARSNR';
    const OIBKONTONR               = 'OIBKONTONR';
    const OIBDIMENSIONNR           = 'OIBDIMENSIONNR';
    const OIBOBJEKTNR              = 'OIBOBJEKTNR';
    const OIBSALDO                 = 'OIBSALDO';
    const OIBKVANTITET             = 'OIBKVANTITET';

    const OUB                      = '#OUB';
    const OUBARSNR                 = 'OUBARSNR';
    const OUBKONTONR               = 'OUBKONTONR';
    const OUBDIMENSIONNR           = 'OUBDIMENSIONNR';
    const OUBOBJEKTNR              = 'OUBOBJEKTNR';
    const OUBSALDO                 = 'OUBSALDO';
    const OUBKVANTITET             = 'OUBKVANTITET';

    const RES                      = '#RES';
    const RESARSNR                 = 'RESARSNR';
    const RESKONTONR               = 'RESKONTONR';
    const RESSALDO                 = 'RESSALDO';
    const RESKVANTITET             = 'RESKVANTITET';

    const PSALDO                   = '#PSALDO';
    const PSALDOARSNR              = 'PSALDOARSNR';
    const PSALDOPERIOD             = 'PSALDOPERIOD';
    const PSALDOKONTONR            = 'PSALDOKONTONR';
    const PSALDODIMENSIONNR        = 'PSALDODIMENSIONNR';
    const PSALDOOBJEKTNR           = 'PSALDOOBJEKTNR';
    const PSALDOSALDO              = 'PSALDOSALDO';
    const PSALDOKVANTITET          = 'PSALDOKVANTITET';

    const PBUDGET                  = '#PBUDGET';
    const PBUDGETARSNR             = 'PBUDGETARSNR';
    const PBUDGETPERIOD            = 'PBUDGETPERIOD';
    const PBUDGETKONTONR           = 'PBUDGETKONTONR';
    const PBUDGETDIMENSIONNR       = 'PBUDGETDIMENSIONNR';
    const PBUDGETOBJEKTNR          = 'PBUDGETOBJEKTNR';
    const PBUDGETSALDO             = 'PBUDGETSALDO';
    const PBUDGETKVANTITET         = 'PBUDGETKVANTITET';

     /**
      * Verifikationspost
      * #VER serie vernr verdatum vertext regdatum sign
      * Obligatorisk
      * Enbart verdatum obligatoriskt, auto-gen (now) om det saknas i Sie4
      */
    const VER                      = '#VER';
    const VERSERIE                 = 'VERSERIE';
    const VERNR                    = 'VERNR';
    const VERDATUM                 = 'VERDATUM';
    const VERTEXT                  = 'VERTEXT';
    const REGDATUM                 = 'REGDATUM';
    const VERSIGN                  = 'VERSIGN';

     /**
      * Transaktionspost #TRANS
      * Tillagd transaktionspost #RTRANS
      * Borttagen transaktionspost #BTRANS
      * valfri enl Sie4-pdf, obl i importfil
      * #TRANS/#RTRANS/#BTRANS kontonr {objektlista} belopp transdat(opt) transtext(opt) kvantitet sign
      * #RTRANS
      * Obligatoriskt : kontonr/belopp
      */
    const TRANS                    = '#TRANS';
    const TRANSKONTONR             = 'TRANSKONTONR';
    const TRANSDIMENSIONNR         = 'TRANSDIMENSIONNR';
    const TRANSOBJEKTNR            = 'TRANSOBJEKTNR';
    const TRANSBELOPP              = 'TRANSBELOPP';
    const TRANSDAT                 = 'TRANSDAT';
    const TRANSTEXT                = 'TRANSTEXT';
    const TRANSKVANTITET           = 'TRANSKVANTITET';
    const TRANSSIGN                = 'TRANSSIGN';
    const RTRANS                   = '#RTRANS';
    const RTRANSKONTONR            = 'RTRANSKONTONR';
    const RTRANSDIMENSIONNR        = 'RTRANSDIMENSIONNR';
    const RTRANSOBJEKTNR           = 'RTRANSOBJEKTNR';
    const RTRANSBELOPP             = 'RTRANSBELOPP';
    const RTRANSDAT                = 'RTRANSDAT';
    const RTRANSTEXT               = 'RTRANSTEXT';
    const RTRANSKVANTITET          = 'RTRANSKVANTITET';
    const RTRANSSIGN               = 'RTRANSSIGN';
    const BTRANS                   = '#BTRANS';
    const BTRANSKONTONR            = 'BTRANSKONTONR';
    const BTRANSDIMENSIONNR        = 'BTRANSDIMENSIONNR';
    const BTRANSOBJEKTNR           = 'BTRANSOBJEKTNR';
    const BTRANSBELOPP             = 'BTRANSBELOPP';
    const BTRANSDAT                = 'BTRANSDAT';
    const BTRANSTEXT               = 'BTRANSTEXT';
    const BTRANSKVANTITET          = 'BTRANSKVANTITET';
    const BTRANSSIGN               = 'BTRANSSIGN';

    /**
     * Kontrollsummeposter
     */

     /**
      * Start av kontrollsummering/-summa
      * valfri
      */
    const KSUMMA                   = '#KSUMMA';
    const KSUMMAPOST               = 'KSUMMA';

    /**
     * Sie4 date format
     */
    const SIE4YYYYMMDD             = 'Ymd';
}
