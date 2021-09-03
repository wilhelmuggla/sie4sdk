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

use InvalidArgumentException;
use Kigkonsult\Asit\It;
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
use Kigkonsult\Sie4Sdk\Util\GuidUtil;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use RuntimeException;

use function intval;
use function is_scalar;
use function number_format;
use function sprintf;
use function strlen;
use function substr;
use function trim;
use function var_export;

class Sie4Validator implements Sie4Interface
{
    /**
     * @var string
     */
    private static $FOUR = '4';

    /**
     * @var string
     */
    private static $ERRARSNR1 = '#%d, %s, årsnr saknas';

    /**
     * @var string
     */
    private static $ERRARSNR2 = '#%d, %s, årsnr ska vara lika med eller mindre än 0';

    /**
     * Validate #FLAGGA, #SIETYP == 4 and at least one #VER must exist (in order)
     *
     * @param It $sie4Input file rows iterator
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertSie4Input( It $sie4Input )
    {
        static $FMT1 = 'Input saknar poster';
        static $FMT2 = 'Ogiltig 1:a post';
        static $FMT3 = 'Ogiltigt antal KSUMMA : ';
        static $FMTx = ' saknas';
        if( empty( $sie4Input->count())) {
            throw new InvalidArgumentException( $FMT1, 3011 );
        }
        $sie4Input->rewind();
        if( ! $sie4Input->valid()) {
            throw new InvalidArgumentException( $FMT2, 3012 );
        }
        $flaggaExist = $sieType4Exist = $orgNrExist = $verExist = false;
        $ksummaCnt   = 0;
        while( $sie4Input->valid()) {
            $post = $sie4Input->current();
            switch( true ) {
                case empty( $post ) :
                    break;
                case StringUtil::startsWith( $post, self::FLAGGA ) :
                    $flaggaExist = true;
                    break;
                case StringUtil::startsWith( $post, self::KSUMMA ) :
                    $ksummaCnt += 1;
                    break;
                case ( $flaggaExist &&
                    StringUtil::startsWith( $post, self::SIETYP ) &&
                    StringUtil::isIn( self::$FOUR, StringUtil::after( self::SIETYP, $post ))) :
                    $sieType4Exist = true;
                    break;
                case ( $sieType4Exist &&
                    $flaggaExist &&
                    StringUtil::startsWith( $post, self::ORGNR )) :
                    $orgNrExist = true;
                    break;
                case ( $orgNrExist &&
                    $sieType4Exist &&
                    $flaggaExist &&
                    StringUtil::startsWith( $post, self::VER )) :
                    $verExist = true;
                    break;
                case ( $verExist &&
                    $orgNrExist &&
                    $sieType4Exist &&
                    $flaggaExist ) :
                    // leave while if all ok
                    break;
            } // end switch
            $sie4Input->next();
        } // end while
        if( ! $flaggaExist ) {
            throw new InvalidArgumentException( self::VER . $FMTx, 3013 );
        }
        if( ! in_array( $ksummaCnt, [ 0, 2 ] )) {
            throw new InvalidArgumentException( $FMT3 . $ksummaCnt, 3014 );
        }
        if( ! $sieType4Exist ) {
            throw new InvalidArgumentException( self::SIETYP . $FMTx, 3015 );
        }
        if( ! $orgNrExist ) {
            throw new InvalidArgumentException( self::ORGNR . $FMTx, 3016 );
        }
        if( ! $verExist ) {
            throw new InvalidArgumentException( self::VER . $FMTx, 3017 );
        }
    }

    /**
     * Assert sie4Dto : timestamp, guid, flagga and IdDto
     *
     * @param Sie4Dto $sie4Dto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertBase( Sie4Dto $sie4Dto )
    {
        static $FMT5 = 'Ogiltig flagga (0,1 förväntas) : ';
        static $FMT7 = 'Sie4 idDto saknas';
        DateTimeUtil::assertTimestamp( $sie4Dto->getTimestamp(), 3211 );
        GuidUtil::assertGuid( $sie4Dto->getCorrelationId(), 3215 );
        $flagga =$sie4Dto->getFlagga();
        if( ! in_array( $flagga, [ 0, 1 ] )) {
            throw new InvalidArgumentException( $FMT5 . $flagga, 3215 );
        }
        if( ! $sie4Dto->isIdDtoSet()) {
            throw new InvalidArgumentException( $FMT7, 3217 );
        }
    }

    /**
     * Assert mandatory sie4Dto (import) properties
     *
     * @param Sie4Dto $sie4IDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertSie4IDto( Sie4Dto $sie4IDto )
    {
        static $FMT6 = '%s får inte förekomma i Sie4';
        static $FMT9 = 'verifikationer saknas';
        self::assertBase( $sie4IDto );
        self::assertIdDto( $sie4IDto->getIdDto(), false );

        if( 0 < $sie4IDto->countAccountDtos()) {
            foreach( $sie4IDto->getAccountDtos() as $x => $accountDto ) {
                self::assertAccountDto( $x, $accountDto );
            }
        }
        if( 0 < $sie4IDto->countSruDtos()) {
            foreach( $sie4IDto->getSruDtos() as $x => $sruDto ) {
                self::assertSruDto( $x, $sruDto );
            }
        }
        if( 0 < $sie4IDto->countDimDtos()) {
            foreach( $sie4IDto->getDimDtos() as $x => $dimDto ) {
                self::assertDimDto( $x, $dimDto );
            }
        }
        if( 0 < $sie4IDto->countUnderDimDtos()) {
            foreach( $sie4IDto->getUnderDimDtos() as $x => $underDimDto ) {
                self::assertUnderDimDto( $x, $underDimDto );
            }
        }
        if( 0 < $sie4IDto->countDimObjektDtos()) {
            foreach( $sie4IDto->getDimObjektDtos() as $x => $dimObjektDto ) {
                self::assertDimObjektDto( $x, $dimObjektDto );
            }
        }

        if( 0 < $sie4IDto->countIbDtos()) {
            throw new InvalidArgumentException( sprintf( $FMT6, self::IB ), 3313 );
        }
        if( 0 < $sie4IDto->countUbDtos()) {
            throw new InvalidArgumentException( sprintf( $FMT6, self::UB ), 3314 );
        }
        if( 0 < $sie4IDto->countOibDtos()) {
            throw new InvalidArgumentException( sprintf( $FMT6, self::OIB ), 3315 );
        }
        if( 0 < $sie4IDto->countOubDtos()) {
            throw new InvalidArgumentException( sprintf( $FMT6, self::OUB ), 3316 );
        }
        if( 0 < $sie4IDto->countSaldoDtos()) {
            throw new InvalidArgumentException( sprintf( $FMT6, self::RES ), 3317 );
        }
        if( 0 < $sie4IDto->countPsaldoDtos()) {
            throw new InvalidArgumentException( sprintf( $FMT6, self::PSALDO ), 3318 );
        }
        if( 0 < $sie4IDto->countPbudgetDtos()) {
            throw new InvalidArgumentException( sprintf( $FMT6, self::PBUDGET ), 3319 );
        }
        if( empty( $sie4IDto->countVerDtos())) {
            throw new InvalidArgumentException( $FMT9,3320 );
        }
        foreach( $sie4IDto->getVerDtos() as $x => $verDto ) {
            self::assertVerDto( $x, $verDto );
        } // end foreach
    }

    /**
     * Assert mandatory sie4EDto (export) properties
     *
     * @param Sie4Dto $sie4EDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertSie4EDto( Sie4Dto $sie4EDto )
    {
        static $FMT3 = 'Konton saknas';
        static $FMT4 = 'Ingående balanser saknas';
        static $FMT5 = 'Utgående balanser saknas';
        static $FMT6 = 'Saldo (resultat) saknas';
        self::assertBase( $sie4EDto );
        self::assertIdDto( $sie4EDto->getIdDto(), true );
        if( 0 < $sie4EDto->countAccountDtos()) {
            foreach( $sie4EDto->getAccountDtos() as $x => $accountDto ) {
                self::assertAccountDto( $x, $accountDto );
            }
        }
        else {
            throw new InvalidArgumentException( $FMT3, 3412 );
        }
        if( 0 < $sie4EDto->countSruDtos()) {
            foreach( $sie4EDto->getSruDtos() as $x => $sruDto ) {
                self::assertSruDto( $x, $sruDto );
            }
        }
        if( 0 < $sie4EDto->countDimDtos()) {
            foreach( $sie4EDto->getDimDtos() as $x => $dimDto ) {
                self::assertDimDto( $x, $dimDto );
            }
        }
        if( 0 < $sie4EDto->countUnderDimDtos()) {
            foreach( $sie4EDto->getUnderDimDtos() as $x => $underDimDto ) {
                self::assertUnderDimDto( $x, $underDimDto );
            }
        }
        if( 0 < $sie4EDto->countDimObjektDtos()) {
            foreach( $sie4EDto->getDimObjektDtos() as $x => $dimObjektDto ) {
                self::assertDimObjektDto( $x, $dimObjektDto );
            }
        }
        if( 0 < $sie4EDto->countIbDtos()) {
            foreach( $sie4EDto->getIbDtos() as $x => $ibDto ) {
                self::assertBalansDto( $x, $ibDto, self::IB );
            }
        }
        else {
            throw new InvalidArgumentException( $FMT4, 3413 );
        }
        if( 0 < $sie4EDto->countUbDtos()) {
            foreach( $sie4EDto->getUbDtos() as $x => $ubDto ) {
                self::assertBalansDto( $x, $ubDto, self::UB );
            }
        }
        else {
            throw new InvalidArgumentException( $FMT5, 3414 );
        }
        if( 0 < $sie4EDto->countOibDtos()) {
            foreach( $sie4EDto->getOibDtos() as $x => $oibDto ) {
                self::assertBalansObektDto( $x, $oibDto, self::OIB );
            }
        }
        if( 0 < $sie4EDto->countOubDtos()) {
            foreach( $sie4EDto->getOubDtos() as $x => $oubDto ) {
                self::assertBalansObektDto( $x, $oubDto, self::OUB );
            }
        }
        if( 0 < $sie4EDto->countSaldoDtos()) {
            foreach( $sie4EDto->getSaldoDtos() as $x => $saldoDto ) {
                self::assertBalansDto( $x, $saldoDto, self::RES );
            }
        }
        else {
            throw new InvalidArgumentException( $FMT6, 3417 );
        }
        if( 0 < $sie4EDto->countPsaldoDtos()) {
            foreach( $sie4EDto->getPsaldoDtos() as $x => $pSaldoDto ) {
                self::assertPeriodDto( $x, $pSaldoDto, self::PSALDO );
            }
        }
        if( 0 < $sie4EDto->countPbudgetDtos()) {
            foreach( $sie4EDto->getPbudgetDtos() as $x => $pBudgetDto ) {
                self::assertPeriodDto( $x, $pBudgetDto, self::PBUDGET );
            }
        }
        if( 0 < $sie4EDto->countVerDtos()) {
            foreach( $sie4EDto->getVerDtos() as $x => $verDto ) {
                self::assertVerDto( $x, $verDto );
            } // end foreach
        }
    }

    /**
     * Validate mandatory properties in IdDto
     *
     * Flagga, Program name/version, format, gen date, sieType and Company name required
     * Sie4E requires RAR, Sietyp 4 default
     * format ignored, auto set to 'PC8'
     * gen date and program name/version auto set if missing
     *   in Sie4IDto, Sie4IWriter and Sie5EntryLoader
     *
     * @param IdDto $idDto
     * @param bool $isSie4Export
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertIdDto( IdDto $idDto, bool $isSie4Export )
    {
        static $FMT2 = 'PROGRAM saknas';
        static $FMT5 = 'SIETYP saknas eller inte 4';
        static $FMT6 = 'BKOD får inte förekomma i Sie4';
        static $FMT7 = 'Företagsnamn saknas';
        static $FMT8 = 'RAR saknas';
        static $FMT9 = 'OMFATTN får inte förekomma i Sie4';
        $programNamn = $idDto->getProgramnamn();
        if( empty( $programNamn )) {
            throw new InvalidArgumentException( $FMT2, 3511 );
        }
        $sieType = $idDto->getSieTyp();
        if( self::$FOUR != $sieType ) {
            throw new InvalidArgumentException( $FMT5 . $sieType, 3515 );
        }
        if( ! $isSie4Export && $idDto->isBkodSet()) {
            throw new InvalidArgumentException( $FMT6, 3516 );
        }
        if( $idDto->isAdressSet()) {
            self::assertAdressDto( $idDto->getAdress());
        }
        if( ! $idDto->isFnamnSet()) {
            throw new InvalidArgumentException( $FMT7, 3517 );
        }
        if( 0 < $idDto->countRarDtos()) {
            foreach( $idDto->getRarDtos() as $x =>$rarDto ) {
                self::assertRarDto( $x, $rarDto );
            }
        }
        elseif( $isSie4Export ) {
            throw new InvalidArgumentException( $FMT8, 3518 );
        }
        if( ! $isSie4Export && $idDto->isOmfattnSet()) {
            throw new InvalidArgumentException( $FMT9, 3519 );
        }
    }

    /**
     * Validate mandatory properties in AdressDto
     *
     * Kontakt, utdelningsadr, poistnr and tel required
     *
     * @param AdressDto $adressDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertAdressDto( AdressDto $adressDto )
    {
        static $FMT1 = '%s, kontakt saknas';
        static $FMT2 = '%s, utdelningsadr saknas';
        static $FMT3 = '%s, postadr saknas';
        static $FMT4 = '%s, tel saknas';
        if( ! $adressDto->isKontaktSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, self::ADRESS ),3601 );
        }
        if( ! $adressDto->isUtdelningsadrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT2, self::ADRESS ),3602 );
        }
        if( ! $adressDto->isPostadrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT3, self::ADRESS ),3603 );
        }
        if( ! $adressDto->isTelSet()) {
            throw new InvalidArgumentException( sprintf( $FMT4, self::ADRESS ),3604 );
        }
    }

    /**
     * Validate mandatory properties in AccountDto
     *
     * KontoNr/namn/typ required
     *
     * @param int        $x
     * @param AccountDto $accountDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertAccountDto( int $x, AccountDto $accountDto )
    {
        static $FMT1 = '#%d KontoNr/namn/typ förväntas';
        if( ! $accountDto->isKontoNrSet() ||
            ! $accountDto->isKontonamnSet() ||
            ! $accountDto->isKontotypSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, $x ),3611 );
        }
    }

    /**
     * Validate mandatory properties in BalansDto, IB/UB/RES
     *
     * In each balansDto, årsnr and konto are required
     *
     * @param int       $x
     * @param BalansDto $balansDto
     * @param string    $label
     * @return void
     */
    public static function assertBalansDto( int $x, BalansDto $balansDto, string $label )
    {
        static $FMT3 = '#%d, %s konto saknas';
        if( ! $balansDto->isArsnrSet()) {
            throw new InvalidArgumentException( sprintf( self::$ERRARSNR1, $x, $label ),3621 );
        }
        if( 0 < $balansDto->getArsnr()) {
            throw new InvalidArgumentException( sprintf( self::$ERRARSNR2, $x, $label ),3622 );
        }
        if( ! $balansDto->isKontoNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT3, $x, $label ),3623 );
        }
    }

    /**
     * Validate mandatory properties in BalansObjektDto, OIB/OUB
     *
     * In each balansObjektDto, årsnr, konto and objektLista are required
     *
     * @param int             $x
     * @param BalansObjektDto $balansObjektDto
     * @param string          $label
     * @return void
     */
    public static function assertBalansObektDto(
        int $x,
        BalansObjektDto $balansObjektDto,
        string $label
    )
    {
        static $FMT3 = '#%d, %s konto saknas';
        static $FMT4 = '#%d, %s objektLista (dimensionNr/objektNr) saknas eller ofullständig';
        if( ! $balansObjektDto->isArsnrSet()) {
            throw new InvalidArgumentException( sprintf( self::$ERRARSNR1, $x, $label ),3631 );
        }
        if( 0 < $balansObjektDto->getArsnr()) {
            throw new InvalidArgumentException( sprintf( self::$ERRARSNR2, $x, $label ),3632 );
        }
        if( ! $balansObjektDto->isKontoNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT3, $x, $label ),3633 );
        }
        if( ! $balansObjektDto->isDimensionsNrSet() || ! $balansObjektDto->isObjektNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT4, $x, $label ),3633 );
        }
    }

    /**
     * Validate mandatory properties in DimDto
     *
     * In each DimDto, dimensionNr and name are required
     *
     * @param int $dx
     * @param DimDto $dimDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertDimDto( int $dx, DimDto $dimDto )
    {
        static $FMT1 = 'dimensionNr (#%d) förväntas';
        static $FMT2 = 'dimensionsNamn (#%d) förväntas';
        if( ! $dimDto->isDimensionsNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, $dx ),3641 );
        }
        if( ! $dimDto->isDimensionsNamnSet()) {
            throw new InvalidArgumentException( sprintf( $FMT2, $dx ),3642 );
        }
    }

    /**
     * Validate mandatory properties in UnderDimDto
     *
     * In each UnderDimDto, dimensionNr, name and superDimNr are required
     *
     * @param int         $dx
     * @param UnderDimDto $underDimDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertUnderDimDto( int $dx, UnderDimDto $underDimDto )
    {
        static $FMT1 = 'underDimensionNr (#%d) förväntas';
        static $FMT2 = 'underDimensionsNamn (#%d) förväntas';
        static $FMT3 = 'superDimensionNr (#%d) förväntas';
        if( ! $underDimDto->isDimensionsNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, $dx ),3651 );
        }
        if( ! $underDimDto->isDimensionsNamnSet()) {
            throw new InvalidArgumentException( sprintf( $FMT2, $dx ),3652 );
        }
        if( ! $underDimDto->isSuperDimNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT3, $dx ),3651 );
        }
    }

    /**
     * Validate mandatory properties in DimObjektDto
     *
     * In each DimDto, dimensionsnr and objektnr/name are required
     * // ?? If dimensionsnamn missing, dimDto MUST exist for dimensionsnr
     *
     * @param int          $dox
     * @param DimObjektDto $dimObjektDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertDimObjektDto( int $dox, DimObjektDto $dimObjektDto )
    {
        static $FMT1 = '#%d, dimensionNr förväntas';
        static $FMT2 = '#%d, objektNr förväntas';
        static $FMT3 = '#%d, objektNamn förväntas';
        if( ! $dimObjektDto->isDimensionsNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, $dox ),3661 );
        }
        if( ! $dimObjektDto->isObjektNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT2, $dox ),3662 );
        }
        if( ! $dimObjektDto->isObjektNamnSet()) {
            throw new InvalidArgumentException( sprintf( $FMT3, $dox ),3663 );
        }
    }

    /**
     * Validate mandatory properties in PeriodDto PBUDGET, PSALDO
     *
     * In each periodDto, årsnr, period and konto are required, objektLista opt
     *
     * @param int       $x
     * @param PeriodDto $periodDto
     * @param string    $label
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertPeriodDto( int $x, PeriodDto $periodDto, string $label )
    {
        static $FMT2 = '#%d, %s period saknas';
        static $FMT3 = '#%d, %s konto saknas';
        static $FMT4 = '#%d, %s objektLista (dimensionNr %s / objektNr %s) ofullständig';
        if( ! $periodDto->isArsnrSet()) {
            throw new InvalidArgumentException( sprintf( self::$ERRARSNR1, $x, $label ),3671 );
        }
        if( 0 < $periodDto->getArsnr()) {
            throw new InvalidArgumentException( sprintf( self::$ERRARSNR2, $x, $label ),3672 );
        }
        if( ! $periodDto->isPeriodSet()) {
            throw new InvalidArgumentException( sprintf( $FMT2, $x, $label ),3673 );
        }
        if( ! $periodDto->isKontoNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT3, $x, $label ),3674 );
        }
        if( ! $periodDto->isDimensionsNrSet() && ! $periodDto->isObjektNrSet()) {
            return;
        }
        if( ! $periodDto->isDimensionsNrSet() || ! $periodDto->isObjektNrSet()) {
            throw new InvalidArgumentException(
                sprintf( $FMT4, $x, $label, $periodDto->getDimensionNr(), $periodDto->getObjektNr()),
                3675
            );
        }
    }

    /**
     * Validate mandatory properties in RarDto, #RAR
     *
     * In each rarDto, årsnr, start and slut are required
     *
     * @param int    $x
     * @param RarDto $rarDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertRarDto( int $x, RarDto $rarDto )
    {
        static $FMT4 = '#%d, start saknas';
        static $FMT5 = '#%d, slut saknas';
        if( ! $rarDto->isArsnrSet()) {
            throw new InvalidArgumentException( sprintf( self::$ERRARSNR1, $x, self::RAR ),3681 );
        }
        if( 0 < $rarDto->getArsnr()) {
            throw new InvalidArgumentException( sprintf( self::$ERRARSNR2, $x, self::RAR ),3682 );
        }
        if( ! $rarDto->isStartSet()) {
            throw new InvalidArgumentException( sprintf( $FMT4, $x ),3683 );
        }
        if( ! $rarDto->isSlutSet()) {
            throw new InvalidArgumentException( sprintf( $FMT5, $x ),3684 );
        }
    }

    /**
     * Validate mandatory properties in SruDto
     *
     * Kontonr and SRU-kod are required
     *
     * @param int    $x
     * @param SruDto $sruDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertSruDto( int $x, SruDto $sruDto )
    {
        static $FMT1 = '#%d, konto saknas';
        static $FMT2 = '#%d, sru-kod saknas';
        if( ! $sruDto->isKontoNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, $x ),3691 );
        }
        if( ! $sruDto->isSruKodSet()) {
            throw new InvalidArgumentException( sprintf( $FMT2, $x ),3691 );
        }
    }

    /**
     * Validate mandatory properties in VerDto and TransDtos array property
     *
     * Verdatum and trans required
     *
     * @param int    $x
     * @param VerDto $verDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertVerDto( int $x, VerDto $verDto )
    {
        static $FMT1 = 'ver %s (#%d), datum saknas';
        static $FMT2 = 'ver %s (#%d), konteringsrader saknas';
        static $FMT3 = 'ver %s (#%d), ej i balans, %f'; // %.2F
        static $SP0  = '';
        DateTimeUtil::assertTimestamp( $verDto->getTimestamp(), 3701 );
        Util\GuidUtil::assertGuid( $verDto->getCorrelationId(), 3703 );
        $verNr = $verDto->getVernr() ?? StringUtil::$SP0;
        if( ! $verDto->isVerdatumSet()) {
            throw new InvalidArgumentException(
                sprintf( $FMT1, $verNr, $x ),
                3705
            );
        }
        if( empty( $verDto->countTransDtos())) {
            throw new InvalidArgumentException(
                sprintf( $FMT2, $verNr, $x ),
                3706
            );
        }
        $balans = 0.00;
        foreach( $verDto->getTransDtos() as $kx => $transDto ) {
            if( self::TRANS == $transDto->getTransType()) {
                $balans += $transDto->getBelopp() ?? 0.00;
            }
            self::assertTransDto( $verNr, $x, $kx, $transDto );
        } // end foreach
        if( 0.00 != (float) number_format( $balans, 2, StringUtil::$DOT, $SP0 )) {
            throw new InvalidArgumentException(
                sprintf( $FMT3, $verNr, $x, $balans ),
                3707
            );
        }
    }

    /**
     * Validate mandatory properties in each VerDto's property TransDtos array element
     *
     * In each trans, kontonr and belopp required,
     *   in trans objektlista, if exists, pairs of dimension and objektnr required
     *
     * @param int|string $verNr
     * @param int        $vx     ver order no
     * @param int        $kx     trans order no
     * @param TransDto   $transDto
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertTransDto(
        $verNr,
        int $vx,
        int $kx,
        TransDto $transDto
    )
    {
        static $FMT0 = '%s (#%d) %s (#%d)';
        static $FMT3 = 'ver %s, kontoNr saknas';
        static $FMT4 = 'ver %s, belopp saknas';
        static $FMT6 = 'ver %s, dimensionsnr och objektnr (#%d) förväntas';
        DateTimeUtil::assertTimestamp( $transDto->getTimestamp(), 3711 );
        Util\GuidUtil::assertGuid( $transDto->getCorrelationId(), 3713 );
        $errKey = sprintf( $FMT0, $verNr, $vx, $transDto->getTransType(), $kx );
        if( ! $transDto->isKontoNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT3, $errKey ),3715 );
        }
        if( ! $transDto->isBeloppSet()) {
            throw new InvalidArgumentException( sprintf( $FMT4, $errKey ), 3717 );
        }
        if( 0 < $transDto->countObjektlista()) {
            foreach( $transDto->getObjektlista() as $x => $dimObjekt ) {
                if( ! $dimObjekt->isDimensionsNrSet() ||
                    ! $dimObjekt->isObjektNrSet() ) {
                    throw new InvalidArgumentException(
                        sprintf( $FMT6, $errKey, $x ),
                        3713
                    );
                }
            } // end foreach
        }
    }

    /**
     * Assert int or string integer
     *
     * @param string $field
     * @param int|string $value
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertIntegerish( string $field, $value )
    {
        static $ERR = '%s integer förväntas, nu %s';
        if( ! is_scalar( $value ) || ( $value != intval( $value ))) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $field, var_export( $value, true )),
                3721
            );
        }
    }

    /**
     * Assert YYYYMM-date
     *
     * @param string $field
     * @param int|string $value
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertYYYYMMDate( string $field, $value )
    {
        static $ONE = '01';
        static $ERR = '%s (#%d) YYYYMM-datum förväntas, nu %s';
        $value      = trim((string) $value );
        if(( 6 != strlen( $value )) ||
            ( 19 > substr( $value, 0, 2 )) ||
            ( 20 < substr( $value, 0, 2 )) ||
            ( 12 < substr( $value, 4, 2 ))) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $field, 1, var_export( $value, true )),
                3731
            );
        }
        try {
            DateTimeUtil::getDateTime( $value . $ONE, $field, 3732 );
        }
        catch( InvalidArgumentException $e ) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $field, 3, var_export( $value, true )),
                3733,
                $e
            );
        }
    }
}
