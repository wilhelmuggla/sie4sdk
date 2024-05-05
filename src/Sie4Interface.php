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
namespace Kigkonsult\Sie4Sdk;

/**
 * Interface Sie4Interface
 *
 * Konstanter med '#'-värdePrefix förekommer endast som direktiv i en Sie4 fil (string)
 * I stor sett övriga som nycklar i Sie4 array
 *
 * @since 1.8.15 20240314
 */
Interface Sie4Interface
{
    /**
     * Product constants
     */
    public const PRODUCTNAME              = 'Kigkonsult\Sie4Sdk';
    public const PRODUCTVERSION           = '1.8.15';

    /**
     * Unik timestamp/guid för varje Sie4 fil (string)
     */

    public const TIMESTAMP                = 'TIMESTAMP';
    public const GUID                     = 'GUID';

    /**
     * Flaggpost
     */

    /**
     * Flaggpost som anger om filen tagits emot av mottagaren
     * obligatorisk i Sie4
     */
    public const FLAGGA                   = '#FLAGGA';
    public const FLAGGPOST                = 'FLAGGA';

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
    public const PROGRAM                  = '#PROGRAM';
    public const PROGRAMNAMN              = 'PROGRAMNAMN';
    public const PROGRAMVERSION           = 'PROGRAMVERSION';

     /**
      * Vilken teckenuppsättning som använts
      *
      * SKA vara IBM PC 8-bitars extended ASCII (Codepage 437)
      * https://en.wikipedia.org/wiki/Code_page_437
      * obligatorisk i Sie4, auto
      */
    public const FORMAT                   = '#FORMAT';

     /**
      * När och av vem som filen genererats
      * #GEN datum sign
      * Obligatorisk (sign opt) Sie4, båda obl. Sie5 SieEntry
      * When using Sie4 array/json input, any PHP DateTime string Ymd-formats can be used for GENDATUM
      */
    public const GEN                      = '#GEN';
    public const GENDATUM                 = 'GENDATUM';
    public const GENSIGN                  = 'GENSIGN';

     /**
      * Vilken typ av SIE-formatet filen följer
      * obligatorisk, auto i Sie4 : 4
      */
    public const SIETYP                   = '#SIETYP';

     /**
      * Fri kommentartext kring filens innehåll
      * valfri
      */
    public const PROSA                    = '#PROSA';
    public const PROSATEXT                = 'PROSATEXT';

     /**
      * Företagstyp
      * valfri
      */
    public const FTYP                     = '#FTYP';
    public const FORETAGSTYP              = 'FORETAGSTYP';

     /**
      * Redovisningsprogrammets internkod för exporterat företag
      * #FNR företagsid
      * valfri
      */
    public const FNR                      = '#FNR';
    public const FNRID                    = 'FNRID';

     /**
      * Organisationsnummer för det företag som exporterats
      * #ORGNR orgnr förvnr verknr
      * förvnr : anv då ensk. person driver flera ensk. firmor (ordningsnr)
      * verknr : anv ej
      * valfri i sieDto, obl (orgnr) i SirEntry
      */
    public const ORGNR                    = '#ORGNR';
    public const ORGNRORGNR               = 'ORGNRORGNR';
    public const ORGNRFORNVR              = 'ORGNRFORNVR';

    /**
     * Branschtillhörighet för det exporterade företaget, Sie4E only
     * valfri
     */
    public const BKOD                     = '#BKOD';
    public const SNIKOD                   = 'SNIKOD';

    /**
     * Adressuppgifter för det aktuella företaget
     * valfri
     */
    public const ADRESS                   = '#ADRESS';
    public const ADRKONTAKT               = 'ADRKONTAKT';
    public const UTDELNINGSADR            = 'UTDELNINGSADR';
    public const POSTADR                  = 'POSTADR';
    public const TEL                      = 'TEL';

     /**
      * Fullständigt namn för det företag som exporterats
      * #FNAMN företagsnamn
      * Obligatorisk i Sie4, valfri i SieEntry
      */
    public const FNAMN                    = '#FNAMN';
    public const FTGNAMN                  = 'FNAMN';

     /**
      * Räkenskapsår från vilket exporterade data hämtats
      * valfri
      * When using Sie4 array/json input, any PHP DateTime string Ymd-formats can be used for RARSTART/RARSLUT
      */
    public const RAR                      = '#RAR';
    public const RARARSNR                 = 'RARASRNR';
    public const RARSTART                 = 'RARSTART';
    public const RARSLUT                  = 'RARSLUT';

     /**
      * Taxeringsår för deklarations- information (SRU-koder)
      * valfri
      */
    public const TAXAR                    = '#TAXAR';
    public const TAXYEAR                  = 'TAXYEAR';

    /**
     * Datum för periodsaldons omfattning
     * #OMFATTN datum
     * When using Sie4 array/json input, any PHP DateTime string Ymd-formats can be used for OMFATTNDATUM
     */
    public const OMFATTN                  = '#OMFATTN';
    public const OMFATTNDATUM             = 'OMFATTNDATUM';

     /**
      * Kontoplanstyp
      * valfri
      */
    public const KPTYP                    = '#KPTYP';
    public const KPTYPE                   = 'KPTYPE';

     /**
      * Redovisningsvaluta
      * #VALUTA valutakod
      * valfri
      */
    public const VALUTA                   = '#VALUTA';
    public const VALUTAKOD                = 'VALUTAKOD';


    /**
     * Kontoplansuppgifter
     */

     /**
      * Kontouppgifter
      * #KONTO kontonr kontonamn
      * valfri
      */
    public const KONTO                    = '#KONTO';
    public const KONTONR                  = 'KONTONR';
    public const KONTONAMN                = 'KONTONAMN';

     /**
      * Kontotyp
      * #KTYP kontonr  kontoTyp
      * valfri
      */
    public const KTYP                     = '#KTYP';
    public const KONTOTYP                 = 'KONTOTYP';

     /**
      * Enhet vid kvantitetsredovisning
      * #ENHET kontonr enhet
      * valfri
      */
    public const ENHET                    = '#ENHET';
    public const KONTOENHET               = 'KONTOENHET';

     /**
      * RSV-kod för standardiserat räkenskapsutdrag
      * valfri
      */
    public const SRU                      = '#SRU';
    public const SRUKONTO                 = 'SRUKONTO';
    public const SRUKOD                   = 'SRUKOD';

     /**
      * Dimension
      * #DIM dimensionsnr namn
      * valfri
      */
    public const DIM                      = '#DIM';
    public const DIMENSIONNR              = 'DIMENSIONNR';
    public const DIMENSIONNAMN            = 'DIMENSIONNAMN';

     /**
      * Underdimension
      * valfri
      */
    public const UNDERDIM                 = '#UNDERDIM';
    public const UNDERDIMNR               = 'UNDERDIMNR';
    public const UNDERDIMNAMN             = 'UNDERDIMNAMN';
    public const UNDERDIMSUPER            = 'UNDERDIMSUPER';

     /**
      * Objekt
      * #OBJEKT dimensionsnr objektnr objektnamn
      * valfri
      */
    public const OBJEKT                   = '#OBJEKT';
    public const OBJEKTDIMENSIONNR        = 'OBJEKTDIMENSIONNR';
    public const OBJEKTNR                 = 'OBJEKTNR';
    public const OBJEKTNAMN               = 'OBJEKTNAMN';

    /**
     * Saldoposter
     */
    public const IB                       = '#IB';
    public const IBARSNR                  = 'IBARSNR';
    public const IBKONTONR                = 'IBKONTONR';
    public const IBSALDO                  = 'IBSALDO';
    public const IBKVANTITET              = 'IBKVANTITET';

    public const UB                       = '#UB';
    // årsnr konto saldo kvantitet(opt)
    public const UBARSNR                  = 'UBARSNR';
    public const UBKONTONR                = 'UBKONTONR';
    public const UBSALDO                  = 'UBSALDO';
    public const UBKVANTITET              = 'UBKVANTITET';

    public const OIB                      = '#OIB';
    // årsnr konto {dimensionsnr objektnr} saldo kvantitet(opt)
    public const OIBARSNR                 = 'OIBARSNR';
    public const OIBKONTONR               = 'OIBKONTONR';
    public const OIBDIMENSIONNR           = 'OIBDIMENSIONNR';
    public const OIBOBJEKTNR              = 'OIBOBJEKTNR';
    public const OIBSALDO                 = 'OIBSALDO';
    public const OIBKVANTITET             = 'OIBKVANTITET';

    public const OUB                      = '#OUB';
    public const OUBARSNR                 = 'OUBARSNR';
    public const OUBKONTONR               = 'OUBKONTONR';
    public const OUBDIMENSIONNR           = 'OUBDIMENSIONNR';
    public const OUBOBJEKTNR              = 'OUBOBJEKTNR';
    public const OUBSALDO                 = 'OUBSALDO';
    public const OUBKVANTITET             = 'OUBKVANTITET';

    public const RES                      = '#RES';
    public const RESARSNR                 = 'RESARSNR';
    public const RESKONTONR               = 'RESKONTONR';
    public const RESSALDO                 = 'RESSALDO';
    public const RESKVANTITET             = 'RESKVANTITET';

    public const PSALDO                   = '#PSALDO';
    public const PSALDOARSNR              = 'PSALDOARSNR';
    public const PSALDOPERIOD             = 'PSALDOPERIOD';
    public const PSALDOKONTONR            = 'PSALDOKONTONR';
    public const PSALDODIMENSIONNR        = 'PSALDODIMENSIONNR';
    public const PSALDOOBJEKTNR           = 'PSALDOOBJEKTNR';
    public const PSALDOSALDO              = 'PSALDOSALDO';
    public const PSALDOKVANTITET          = 'PSALDOKVANTITET';

    public const PBUDGET                  = '#PBUDGET';
    public const PBUDGETARSNR             = 'PBUDGETARSNR';
    public const PBUDGETPERIOD            = 'PBUDGETPERIOD';
    public const PBUDGETKONTONR           = 'PBUDGETKONTONR';
    public const PBUDGETDIMENSIONNR       = 'PBUDGETDIMENSIONNR';
    public const PBUDGETOBJEKTNR          = 'PBUDGETOBJEKTNR';
    public const PBUDGETSALDO             = 'PBUDGETSALDO';
    public const PBUDGETKVANTITET         = 'PBUDGETKVANTITET';

     /**
      * Verifikationspost
      * #VER serie vernr verdatum vertext regdatum sign
      * Obligatorisk
      * Enbart verdatum obligatoriskt, auto-gen (now) om det saknas i Sie4
      * When using Sie4 array/json input, any PHP DateTime string Ymd-formats can be used for VERDATUM/REGDATUM
      */
    public const VER                      = '#VER';
    public const VERTIMESTAMP             = 'VERTIMESTAMP';  // unik timestamp för varje verifiktion
    public const VERGUID                  = 'VERGUID';       // unik guid för varje verifiktion
    public const VERPARENTGUID            = 'VERPARENTGUID'; // guid för SieDto
    public const VERSERIE                 = 'VERSERIE';
    public const VERNR                    = 'VERNR';
    public const VERDATUM                 = 'VERDATUM';
    public const VERTEXT                  = 'VERTEXT';
    public const REGDATUM                 = 'REGDATUM';
    public const VERSIGN                  = 'VERSIGN';

     /**
      * Transaktionspost #TRANS
      * Tillagd transaktionspost #RTRANS
      * Borttagen transaktionspost #BTRANS
      * valfri enl Sie4-pdf, obl i importfil
      * #TRANS/#RTRANS/#BTRANS kontonr {objektlista} belopp transdat(opt) transtext(opt) kvantitet sign
      * Obligatoriskt : kontonr/belopp
      * When using Sie4 array/json input, any PHP DateTime string Ymd-formats can be used for TRANSDAT/RTRANSDAT/BTRANSDAT
      */
    public const TRANS                    = '#TRANS';
    public const TRANSTIMESTAMP           = 'TRANSTIMESTAMP';  // unik timestamp för varje transaktionspost
    public const TRANSGUID                = 'TRANSGUID';       // unik guid för varje transaktionspost
    public const TRANSPARENTGUID          = 'TRANSPARENTGUID'; // guid för Ver
    public const TRANSKONTONR             = 'TRANSKONTONR';
    public const TRANSDIMENSIONNR         = 'TRANSDIMENSIONNR';
    public const TRANSOBJEKTNR            = 'TRANSOBJEKTNR';
    public const TRANSBELOPP              = 'TRANSBELOPP';
    public const TRANSDAT                 = 'TRANSDAT';
    public const TRANSTEXT                = 'TRANSTEXT';
    public const TRANSKVANTITET           = 'TRANSKVANTITET';
    public const TRANSSIGN                = 'TRANSSIGN';
    public const RTRANS                   = '#RTRANS';
    public const RTRANSTIMESTAMP          = 'RTRANSTIMESTAMP';
    public const RTRANSGUID               = 'RTRANSGUID';
    public const RTRANSPARENTGUID         = 'RTRANSPARENTGUID'; // guid för Ver
    public const RTRANSKONTONR            = 'RTRANSKONTONR';
    public const RTRANSDIMENSIONNR        = 'RTRANSDIMENSIONNR';
    public const RTRANSOBJEKTNR           = 'RTRANSOBJEKTNR';
    public const RTRANSBELOPP             = 'RTRANSBELOPP';
    public const RTRANSDAT                = 'RTRANSDAT';
    public const RTRANSTEXT               = 'RTRANSTEXT';
    public const RTRANSKVANTITET          = 'RTRANSKVANTITET';
    public const RTRANSSIGN               = 'RTRANSSIGN';
    public const BTRANS                   = '#BTRANS';
    public const BTRANSTIMESTAMP          = 'BTRANSTIMESTAMP';
    public const BTRANSGUID               = 'BTRANSGUID';
    public const BTRANSPARENTGUID         = 'BTRANSPARENTGUID'; // guid för Ver
    public const BTRANSKONTONR            = 'BTRANSKONTONR';
    public const BTRANSDIMENSIONNR        = 'BTRANSDIMENSIONNR';
    public const BTRANSOBJEKTNR           = 'BTRANSOBJEKTNR';
    public const BTRANSBELOPP             = 'BTRANSBELOPP';
    public const BTRANSDAT                = 'BTRANSDAT';
    public const BTRANSTEXT               = 'BTRANSTEXT';
    public const BTRANSKVANTITET          = 'BTRANSKVANTITET';
    public const BTRANSSIGN               = 'BTRANSSIGN';

    /**
     * Kontrollsummeposter
     */

     /**
      * Start av kontrollsummering/-summa
      * valfri
      */
    public const KSUMMA                   = '#KSUMMA';
    public const KSUMMAPOST               = 'KSUMMA';

    /**
     * Sie4 datumformat
     */
    public const SIE4YYYYMMDD             = 'Ymd';
}
