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
namespace Kigkonsult\Sie4Sdk;

use DateTime;
use InvalidArgumentException;
use Kigkonsult\Asit\It;
use Kigkonsult\Sie4Sdk\Dto\AccountDto;
use Kigkonsult\Sie4Sdk\Dto\BalansDto;
use Kigkonsult\Sie4Sdk\Dto\BalansObjektDto;
use Kigkonsult\Sie4Sdk\Dto\DimDto;
use Kigkonsult\Sie4Sdk\Dto\DimObjektDto;
use Kigkonsult\Sie4Sdk\Dto\PeriodDto;
use Kigkonsult\Sie4Sdk\Dto\Sie4Dto;
use Kigkonsult\Sie4Sdk\Dto\SruDto;
use Kigkonsult\Sie4Sdk\Dto\TransDto;
use Kigkonsult\Sie4Sdk\Dto\UnderDimDto;
use Kigkonsult\Sie4Sdk\Dto\VerDto;
use Kigkonsult\Sie4Sdk\Util\ArrayUtil;
use Kigkonsult\Sie4Sdk\Util\FileUtil;
use Kigkonsult\Sie4Sdk\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Impl\CommonFactory;

use function crc32;
use function implode;
use function rtrim;
use function sprintf;

/**
 * Class Sie4WriterBase
 *
 * @since 1.8.2 2023-09-18
 */
abstract class Sie4WriterBase implements Sie4Interface
{
    /**
     * @var string
     */
    protected static string $SIEFMT1 = '%s %s';

    /**
     * @var string
     */
    protected static string $SIEFMT2 = '%s %s %s';

    /**
     * @var string
     */
    protected static string $SIEFMT3 = '%s %s %s %s';

    /**
     * @var string
     */
    protected static string $SIEFMT4 = '%s %s %s %s %s';

    /**
     * @var string
     */
    protected static string $SIEFMT5 = '%s %s %s %s %s %s';

    /**
     * @var string
     */
    protected static string $SIEFMT6 = '%s %s %s %s %s %s %s';

    /**
     * @var string
     */
    protected static string $SIEFMT7 = '%s %s %s %s %s %s %s %s';

    /**
     * @var string
     */
    protected static string $ZERO = '0.0';

    /**
     * Output file rows, managed by Asit\It
     *
     * Rows without eol
     *
     * @var It
     */
    protected It $output;

    /**
     * @var Sie4Dto|null
     */
    protected Sie4Dto $sie4Dto;

    /**
     * If to write KSUMMA or not
     * @var bool
     */
    protected bool $writeKsumma  = false;

    /**
     * String to base #KSUMMA crc-32 value on
     * @var string|null
     */
    protected ?string $ksummaBase  = null;

    /**
     * @param mixed ...$args
     * @return void
     */
    protected function appendKsumma( ...$args ) : void
    {
        if( $this->writeKsumma ) {
            $this->ksummaBase .= implode( $args );
        }
    }

    /**
     * @return null|string
     */
    public function getKsummaBase() : ?string
    {
        return $this->ksummaBase;
    }

    /**
     * Return instance
     *
     * @param Sie4Dto|null $sie4Dto
     * @return self
     * @throws InvalidArgumentException
     */
    public static function factory( ? Sie4Dto $sie4Dto = null ) : self
    {
        $instance = new static();
        if( $sie4Dto !== null ) {
            $instance->setSie4Dto( $sie4Dto );
        }
        return $instance;
    }

    /**
     * Return Sie4E/Sie4I string (without input validation)
     *
     * @param bool $isSie4E
     * @param Sie4Dto|null $sie4Dto
     * @param string|null $outputfile
     * @param bool|null $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     * @since 1.8.2 2023-09-18
     */
    protected function write(
        bool $isSie4E,
        ? Sie4Dto $sie4Dto = null,
        ? string  $outputfile = null,
        ? bool $writeKsumma = null
    ) : string
    {
        if( $sie4Dto !== null ) {
            $this->setSie4Dto( $sie4Dto );
        }
        if( ! empty( $outputfile )) {
            FileUtil::assertWriteFile( $outputfile, 5201 );
        }
        $this->writeKsumma = ( $this->sie4Dto->isKsummaSet() || ( $writeKsumma ?? false ));
        $this->output      = new It();

        $this->output->append(
            sprintf( self::$SIEFMT1, self::FLAGGA, $this->sie4Dto->getFlagga())
        );
        if( $this->writeKsumma ) {
            $this->output->append( self::KSUMMA );
        }
        $this->writeProgram();
        $this->writeFormat();
        $this->writeGen();
        $this->writeSietyp();
        $this->writeProsa();
        $this->writeFtyp();
        $this->writeFnr();
        $this->writeOrgnr();
        if( $isSie4E ) {
            $this->writeBkod();
        }
        $this->writeAdress();
        $this->writeFnamn();
        $this->writeRar();
        $this->writeTaxar();
        if( $isSie4E ) {
            $this->writeOmfattn();
        }
        $this->writeKptyp();
        $this->writeValuta();
        $this->writeKonto();
        $this->writeSRU();
        $this->writeDim(); // also UnderDim/Object
        $this->writeIbUb();
        $this->writeOibOub();
        $this->writeRes();
        $this->writePsaldoPbudget();

        $this->writeVerDtos();

        if( $this->writeKsumma ) {
            $this->computeAndWriteKsumma();
        }
        $output = ArrayUtil::eolEndElements( $this->output->get());
        if( ! empty( $outputfile )) {
            FileUtil::writeFile( $outputfile, $output, 5205 );
        }
        return implode( $output );
    }

    /**
     * #PROGRAM programnamn version
     *
     * @return void
     */
    protected function writeProgram() : void
    {
        $programnamn = StringUtil::utf8toCP437(
            $this->sie4Dto->getIdDto()->getProgramnamn()  // always set
        );
        $version     = StringUtil::utf8toCP437(
            $this->sie4Dto->getIdDto()->getVersion()
        );
        $this->appendKsumma( self::PROGRAM, $programnamn, $version );
        $this->output->append(
            sprintf(
                self::$SIEFMT2,
                self::PROGRAM,
                StringUtil::quoteString( $programnamn ),
                $version
            )
        );
    }

    /**
     * #FORMAT PC8
     *
     * @return void
     */
    protected function writeFormat() : void
    {
        static $FORMATPC8 = 'PC8';
        $this->appendKsumma( self::FORMAT, $FORMATPC8 );
        $this->output->append(
            sprintf(
                self::$SIEFMT1,
                self::FORMAT,
                $FORMATPC8
            )
        );
    }

    /**
     * #GEN datum sign
     *
     * @return void
     */
    protected function writeGen() : void
    {
        $idDto   = $this->sie4Dto->getIdDto(); // always set
        $datum   = $idDto->getGenDate()->format( self::SIE4YYYYMMDD );
        $this->appendKsumma( self::GEN, $datum );
        $sign    = StringUtil::$SP0;
        if( $idDto->isSignSet()) {
            $genSign = $idDto->getSign();
            if( self::PRODUCTNAME !== $genSign ) {
                $sign = StringUtil::utf8toCP437( $genSign );
                $this->appendKsumma( $sign );
                $sign = StringUtil::quoteString( $sign );
            }
        }
        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEFMT2,
                    self::GEN,
                    $datum,
                    $sign
                )
            )
        );
    }

    /**
     * #SIETYP typnr
     *
     * @return void
     */
    protected function writeSietyp() : void
    {
        $sieType = $this->sie4Dto->getIdDto()->getSieTyp(); // IdDto always set
        $this->appendKsumma( self::SIETYP, $sieType );
        $this->output->append(
            sprintf(
                self::$SIEFMT1,
                self::SIETYP,
                $sieType
            )
        );
    }

    /**
     * #PROSA
     *
     * @return void
     */
    protected function writeProsa() : void
    {
        $idDto = $this->sie4Dto->getIdDto();  // IdDto always set
        if( $idDto->isProsaSet()) {
            $prosa = StringUtil::utf8toCP437( $idDto->getProsa());
            $this->appendKsumma( self::PROSA, $prosa );
            $this->output->append(
                sprintf( self::$SIEFMT1, self::PROSA, StringUtil::quoteString( $prosa ))
            );
        }
    }

    /**
     * #FTYP
     *
     * @return void
     */
    protected function writeFtyp() : void
    {
        $idDto = $this->sie4Dto->getIdDto();  // IdDto always set
        if( $idDto->isFtypSet()) {
            $fTyp = StringUtil::utf8toCP437( $idDto->getFtyp());
            $this->appendKsumma( self::FTYP, $fTyp );
            $this->output->append(
                sprintf( self::$SIEFMT1, self::FTYP, $fTyp )
            );
        }
    }

    /**
     * #FNR företagsid
     *
     * @return void
     */
    protected function writeFnr() : void
    {
        $idDto = $this->sie4Dto->getIdDto(); // IdDto always set
        if( $idDto->isFnrIdSet()) {
            $companyClientId = StringUtil::utf8toCP437( $idDto->getFnrId());
            $this->appendKsumma( self::FNR, $companyClientId );
            $this->output->append(
                sprintf( self::$SIEFMT1, self::FNR, $companyClientId )
            );
        }
    }

    /**
     * #ORGNR orgnr förvnr verknr (förvnr = multiple if not is null|1)
     *
     * @return void
     */
    protected function writeOrgnr() : void
    {
        $idDto = $this->sie4Dto->getIdDto(); // IdDto always set
        if( $idDto->isOrgnrSet()) {
            $orgnr    = $idDto->getOrgnr();
            $multiple = ( $idDto->getMultiple() ?: StringUtil::$SP0 );
            $this->appendKsumma( self::ORGNR, $orgnr, $multiple );
            $this->output->append(
                rtrim( sprintf( self::$SIEFMT2, self::ORGNR, $orgnr, $multiple ))
            );
        }
    }

    /**
     * #BKOD SNI-kod, Sie4E only
     *
     * @return void
     */
    protected function writeBkod() : void
    {
        $idDto = $this->sie4Dto->getIdDto(); // IdDto always set
        if( $idDto->isBkodSet()) {
            $sniKod = StringUtil::utf8toCP437( $idDto->getBkod());
            $this->appendKsumma( self::BKOD, $sniKod );
            $this->output->append(
                sprintf( self::$SIEFMT1, self::BKOD, StringUtil::quoteString( $sniKod ))
            );
        }
    }

    /**
     * #ADRESS kontakt utdelningsadr postadr tel
     *
     * @return void
     */
    protected function writeAdress() : void
    {
        $idDto = $this->sie4Dto->getIdDto(); // IdDto always set
        if( $idDto->isAdressSet()) {
            $adressDto     = $idDto->getAdress();
            $kontakt       = StringUtil::utf8toCP437((string) $adressDto->getKontakt());
            $utdelningsadr = StringUtil::utf8toCP437((string) $adressDto->getUtdelningsadr());
            $postadr       = StringUtil::utf8toCP437((string) $adressDto->getPostadr());
            $tel           = StringUtil::utf8toCP437((string) $adressDto->getTel());
            $this->appendKsumma( self::ADRESS, $kontakt, $utdelningsadr, $postadr, $tel );
            $this->output->append(
                sprintf( self::$SIEFMT4,
                    self::ADRESS,
                    StringUtil::quoteString( $kontakt ),
                    StringUtil::quoteString( $utdelningsadr ),
                    StringUtil::quoteString( $postadr ),
                    StringUtil::quoteString( $tel )
                )
            );
        }
    }

    /**
     * #FNAMN företagsnamn
     *
     * @return void
     */
    protected function writeFnamn() : void
    {
        $idDto = $this->sie4Dto->getIdDto(); // IdDto always set
        if( $idDto->isFnamnSet()) {
            $companyName = StringUtil::utf8toCP437((string) $idDto->getFnamn());
            $this->appendKsumma( self::FNAMN, $companyName );
            $this->output->append(
                sprintf(
                    self::$SIEFMT1,
                    self::FNAMN,
                    StringUtil::quoteString( $companyName )
                )
            );
        }
    }

    /**
     * #RAR årsnr start slut
     *
     * @return void
     */
    protected function writeRar() : void
    {
        $idDto = $this->sie4Dto->getIdDto(); // IdDto always set
        if( 0 < $idDto->countRarDtos()) {
            foreach( $idDto->getRarDtos() as $rarDto ) {
                $arsnr = $rarDto->getArsnr();
                $start = $rarDto->getStart()->format( self::SIE4YYYYMMDD );
                $slut  = $rarDto->getSlut()->format( self::SIE4YYYYMMDD );
                $this->appendKsumma( self::RAR, $arsnr, $start, $slut );
                $this->output->append(
                    sprintf(
                        self::$SIEFMT3,
                        self::RAR,
                        $arsnr,
                        $start,
                        $slut
                    )
                );
            } // end foreach
        }
    }

    /**
     * #TAXAR år
     *
     * @return void
     */
    protected function writeTaxar() : void
    {
        $idDto = $this->sie4Dto->getIdDto(); // IdDto always set
        if( $idDto->isTaxarSet()) {
            $taxar = $idDto->getTaxar();
            $this->appendKsumma( self::TAXAR, $taxar );
            $this->output->append(
                sprintf(
                    self::$SIEFMT1,
                    self::TAXAR,
                    $taxar
                )
            );
        }
    }

    /**
     * #OMFATTN datum
     *
     * @return void
     */
    protected function writeOmfattn() : void
    {
        $idDto = $this->sie4Dto->getIdDto(); // IdDto always set
        if( $idDto->isOmfattnSet()) {
            $datum = $idDto->getOmfattn()->format( self::SIE4YYYYMMDD );
            $this->appendKsumma( self::OMFATTN, $datum );
            $this->output->append(
                sprintf(
                    self::$SIEFMT1,
                    self::OMFATTN,
                    $datum
                )
            );
        }
    }

    /**
     * #KPTYP typ
     *
     * @return void
     */
    protected function writeKptyp() : void
    {
        $idDto = $this->sie4Dto->getIdDto(); // IdDto always set
        if( $idDto->isKptypSet()) {
            $kptyp = StringUtil::utf8toCP437((string) $idDto->getKptyp());
            $this->appendKsumma( self::KPTYP, $kptyp );
            $this->output->append(
                sprintf(
                    self::$SIEFMT1,
                    self::KPTYP,
                    StringUtil::quoteString( $kptyp )
                )
            );
        }
    }

    /**
     * #VALUTA valutakod
     *
     * @return void
     */
    protected function writeValuta() : void
    {
        $idDto = $this->sie4Dto->getIdDto();
        if( $idDto->isValutakodSet()) {
            $valutakod = StringUtil::utf8toCP437( $idDto->getValutakod());
            $this->appendKsumma( self::VALUTA, $valutakod );
            $this->output->append(
                sprintf( self::$SIEFMT1, self::VALUTA, $valutakod )
            );
        }
    }

    /**
     * #KONTO/#KTYP/#ENHET
     *
     * @return void
     * @since 1.8.2 2023-09-18
     */
    protected function writeKonto() : void
    {
        if( 0 < $this->sie4Dto->countAccountDtos()) {
            foreach( $this->sie4Dto->getAccountDtos() as $accountDto ) {
                // empty row before each #KONTO
                $this->output->append( StringUtil::$SP0 );
                $this->writeKontoData( $accountDto );
            } // end foreach
        }
    }

    /**
     * #KONTO kontonr kontoNamn
     * #KTYP kontonr  kontoTyp
     * #ENHET kontonr enhet
     *
     * @param AccountDto $accountDto
     * @return void
     * @since 1.8.3 2023-09-20
     */
    private function writeKontoData( AccountDto $accountDto ) : void
    {
        $kontoNr   = $accountDto->getKontoNr();
        $kontonamn = StringUtil::utf8toCP437((string) $accountDto->getKontoNamn());
        $this->appendKsumma( self::KONTO, $kontoNr, $kontonamn );
        $this->output->append(
            sprintf(
                self::$SIEFMT2,
                self::KONTO,
                $kontoNr,
                StringUtil::quoteString( $kontonamn )
            )
        );
        if( $accountDto->isKontotypSet()) {
            $kontotyp = StringUtil::utf8toCP437((string)$accountDto->getKontoTyp());
            $this->appendKsumma(self::KTYP, $kontoNr, $kontotyp);
            $this->output->append(
                sprintf(
                    self::$SIEFMT2,
                    self::KTYP,
                    $kontoNr,
                    $kontotyp
                )
            );
        }
        if( $accountDto->isEnhetSet()) {
            $enhet = StringUtil::utf8toCP437((string) $accountDto->getEnhet());
            $this->appendKsumma( self::ENHET, $kontoNr, $enhet );
            $this->output->append(
                sprintf(
                    self::$SIEFMT2,
                    self::ENHET,
                    $kontoNr,
                    $enhet
                )
            );
        } // end if
    }

    /**
     * #SRU
     *
     * @return void
     */
    protected function writeSRU() : void
    {
        if( 0 < $this->sie4Dto->countSruDtos()) {
            // empty row before #SRUs
            $this->output->append( StringUtil::$SP0 );
            foreach( $this->sie4Dto->getSruDtos() as $sruDto ) {
                $this->writeSruData( $sruDto );
            } // end foreach
        }
    }

    /**
     * #SRU kontoNr sruKod
     *
     * @param SruDto $sruDto
     * @return void
     */
    private function writeSruData( SruDto $sruDto ) : void
    {
        $kontoNr = $sruDto->getKontoNr();
        $sruKod  = $sruDto->getSruKod();
        $this->appendKsumma( self::SRU, $kontoNr, $sruKod );
        $this->output->append(
            sprintf(
                self::$SIEFMT2,
                self::SRU,
                $kontoNr,
                $sruKod
            )
        );
    }

    /**
     * #DIM
     *
     * @return void
     * @since 1.8.2 2023-09-18
     */
    protected function writeDim() : void
    {
        if( 0 < $this->sie4Dto->countDimDtos()) {
            foreach( $this->sie4Dto->getDimDtos() as $dimDto ) {
                // empty row before each #DIM
                $this->output->append( StringUtil::$SP0 );
                $this->writeDimData( $dimDto );
                $dimensionNr = $dimDto->getDimensionNr();
                $dimensions = $this->writeUnderDim( $dimensionNr );
                array_unshift( $dimensions, $dimensionNr );
                foreach( $dimensions as $dimension ) {
                    $this->writeObjekt( $dimension );
                }
            } // end foreach
        }
    }

    /**
     * #DIM dimensionsnr namn
     *
     * @param DimDto $dimDto
     * @return void
     */
    private function writeDimData( DimDto $dimDto ) : void
    {
        $dimId = $dimDto->getDimensionNr();
        $namn  = StringUtil::utf8toCP437((string) $dimDto->getDimensionsNamn());
        $this->appendKsumma( self::DIM, $dimId, $namn );
        $this->output->append(
            sprintf(
                self::$SIEFMT2,
                self::DIM,
                $dimId,
                StringUtil::quoteString( $namn )
            )
        );
    }

    /**
     * #UNDERDIM
     *
     * @param int $superDim
     * @return int[]
     * @since 1.8.2 2023-09-18
     */
    protected function writeUnderDim( int $superDim ) : array
    {
        $dimensions = [];
        if( 0 < $this->sie4Dto->countUnderDimDtos()) {
            foreach( $this->sie4Dto->getUnderDimDtos() as $underDimDto ) {
                if( $superDim == $underDimDto->getSuperDimNr()) {
                    $this->writeUnderDimData( $underDimDto );
                    $dimensions[] = $underDimDto->getDimensionNr();
                }
            } // end foreach
        }
        return $dimensions;
    }

    /**
     * #UNDERDIM dimensionsnr namn superdimension
     *
     * @param UnderDimDto $underDimDto
     * @return void
     */
    private function writeUnderDimData( UnderDimDto $underDimDto ) : void
    {
        $underDimId = $underDimDto->getDimensionNr();
        $namn       = StringUtil::utf8toCP437((string) $underDimDto->getDimensionsNamn());
        $superDimId = $underDimDto->getSuperDimNr();
        $this->appendKsumma( self::UNDERDIM, $underDimId, $namn, $superDimId );
        $this->output->append(
            sprintf(
                self::$SIEFMT3,
                self::UNDERDIM,
                $underDimId,
                StringUtil::quoteString( $namn ),
                $superDimId
            )
        );
    }

    /**
     * #OBJEKT
     *
     * @param int $superDim
     * @return void
     * @since 1.8.2 2023-09-18
     */
    protected function writeObjekt( int $superDim ) : void
    {
        if( 0 < $this->sie4Dto->countDimObjektDtos()) {
            foreach( $this->sie4Dto->getDimObjektDtos() as $dimObjektDto ) {
                if( $superDim == $dimObjektDto->getDimensionNr()) {
                    $this->writeDimObjektData( $dimObjektDto );
                }
            } // end foreach
        }
    }

    /**
     * #OBJEKT dimensionsnr objektnr objektnamn
     *
     * @param DimObjektDto $dimObjektDto
     * @return void
     */
    private function writeDimObjektData( DimObjektDto $dimObjektDto ) : void
    {
        $dimId      = $dimObjektDto->getDimensionNr();
        $objektnr   = StringUtil::utf8toCP437((string) $dimObjektDto->getObjektNr());
        $objektnamn = StringUtil::utf8toCP437((string) $dimObjektDto->getObjektNamn());
        $this->appendKsumma( self::OBJEKT, $dimId, $objektnr, $objektnamn );
        $this->output->append(
            sprintf(
                self::$SIEFMT3,
                self::OBJEKT,
                $dimId,
                StringUtil::quoteString( $objektnr ),
                StringUtil::quoteString( $objektnamn )
            )
        );
    }

    /**
     * Managing writing of #IB and #UB
     *
     * @return void
     */
    protected function writeIbUb() : void
    {
        if( ! empty( $this->sie4Dto->countIbDtos())) {
            // empty row before #IB
            $this->output->append( StringUtil::$SP0 );
            foreach( $this->sie4Dto->getIbDtos() as $ibDto ) {
                $this->writeBalansDto( $ibDto, self::IB );
            } // end foreach
        }
        if( ! empty( $this->sie4Dto->countUbDtos())) {
            // empty row before #UB
            $this->output->append( StringUtil::$SP0 );
            foreach( $this->sie4Dto->getUbDtos() as $ubDto ) {
                $this->writeBalansDto( $ubDto, self::UB );
            } // end foreach
        }
    }

    /**
     * Writes single #IB/#UB/#RES
     *
     * #?B årsnr konto saldo kvantitet(opt)
     *
     * @param BalansDto $balansDto
     * @param string    $label
     * @return void
     */
    protected function writeBalansDto( BalansDto $balansDto, string $label ) : void
    {
        $arsnr     = $balansDto->getArsnr();
        $kontoNr   = $balansDto->getKontoNr();
        $saldo     = $balansDto->isSaldoSet()
            ? CommonFactory::formatAmount( $balansDto->getSaldo())
            : self::$ZERO;
        $kvantitet = $balansDto->isKvantitetSet()
            ? $balansDto->getKvantitet()
            : StringUtil::$SP0;
        $this->appendKsumma( $label, $arsnr, $kontoNr, $saldo, $kvantitet );
        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEFMT4,
                    $label,
                    $arsnr,
                    $kontoNr,
                    $saldo,
                    $kvantitet
                )
            )
        );
    }

    /**
     * Managing writing of #OIB and #OUB
     *
     * @return void
     */
    protected function writeOibOub() : void
    {
        if( ! empty( $this->sie4Dto->countOibDtos())) {
            // empty row before #OIB
            $this->output->append( StringUtil::$SP0 );
            foreach( $this->sie4Dto->getOibDtos() as $oibDto ) {
                $this->writeBalansObjektDto( $oibDto, self::OIB );
            } // end foreach
        }
        if( ! empty( $this->sie4Dto->countOubDtos())) {
            // empty row before #OUB
            $this->output->append( StringUtil::$SP0 );
            foreach( $this->sie4Dto->getOubDtos() as $oubDto ) {
                $this->writeBalansObjektDto( $oubDto, self::OUB );
            } // end foreach
        }
    }

    /**
     * Writes single #OIB/#OUB
     *
     * #O?B årsnr konto {dimensionsnr objektnr} saldo kvantitet(opt)
     *
     * @param BalansObjektDto  $balansObjektDto
     * @param string     $label
     * @return void
     */
    protected function writeBalansObjektDto( BalansObjektDto $balansObjektDto, string $label ) : void
    {
        $arsnr       = $balansObjektDto->getArsnr();
        $kontoNr     = $balansObjektDto->getKontoNr();
        $this->appendKsumma( $label, $arsnr, $kontoNr );

        $objektLista = StringUtil::curlyBacketsString(
            ( $balansObjektDto->isDimensionsNrSet() && $balansObjektDto->isObjektNrSet())
                ? $this->getObjektLista(
                    $balansObjektDto->getDimensionNr(),
                    $balansObjektDto->getObjektNr()
                  )
                : StringUtil::$SP0
        );
        $saldo       = $balansObjektDto-> isSaldoSet()
            ? CommonFactory::formatAmount( $balansObjektDto->getSaldo())
            : self::$ZERO;
        $kvantitet = $balansObjektDto->isKvantitetSet()
            ? $balansObjektDto->getKvantitet()
            : StringUtil::$SP0;

        $this->appendKsumma( $saldo, $kvantitet );
        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEFMT5,
                    $label,
                    $arsnr,
                    $kontoNr,
                    $objektLista,
                    $saldo,
                    $kvantitet
                )
            )
        );
    }

    /**
     * Return objektLista without brackets
     *
     * @param int    $dimensionNr
     * @param string $objektNr
     * @return string
     */
    protected function getObjektLista( int $dimensionNr, string $objektNr ) : string
    {
        $dimNr = (string) $dimensionNr;
        $this->appendKsumma( $dimNr, $objektNr );
        return
            StringUtil::quoteString( $dimNr ) .
            StringUtil::$SP1 .
            StringUtil::quoteString( $objektNr );
    }

    /**
     * #RES
     *
     * @return void
     */
    protected function writeRes() : void
    {
        if( 0 < $this->sie4Dto->countSaldoDtos()) {
            // empty row before #RESs
            $this->output->append( StringUtil::$SP0 );
            foreach( $this->sie4Dto->getSaldoDtos() as $saldoDto ) {
                $this->writeBalansDto( $saldoDto, self::RES );
            } // end foreach
        }
    }

    /**
     * #PSALDO/PBUDGET
     *
     * @return void
     */
    protected function writePsaldoPbudget() : void
    {
        if( 0 < $this->sie4Dto->countPsaldoDtos()) {
            // empty row before #PSALDOs
            $this->output->append( StringUtil::$SP0 );
            foreach( $this->sie4Dto->getPsaldoDtos() as $pSaldoDto ) {
                $this->writePeriodDto( $pSaldoDto, self::PSALDO );
            } // end foreach
        }
        if( 0 < $this->sie4Dto->countPbudgetDtos()) {
            // empty row before #PBUDGETs
            $this->output->append( StringUtil::$SP0 );
            foreach( $this->sie4Dto->getPbudgetDtos() as $pBudgetDto ) {
                $this->writePeriodDto( $pBudgetDto, self::PBUDGET );
            } // end foreach
        }
    }

    /**
     * Writes single #PBUDGET/#PSALDO
     *
     * #P? årsnr period konto {dimensionsnr objektnr} saldo kvantitet(opt)
     *
     * @param PeriodDto  $periodDto
     * @param string     $label
     * @return void
     */
    protected function writePeriodDto( PeriodDto $periodDto, string $label ) : void
    {
        $arsnr       = $periodDto->getArsnr();
        $period      = $periodDto->getPeriod();
        $kontoNr     = $periodDto->getKontoNr();
        $this->appendKsumma( $label, $arsnr, $period, $kontoNr );
        $objektLista = StringUtil::curlyBacketsString(
            ( $periodDto->isDimensionsNrSet() && $periodDto->isObjektNrSet())
                ? $this->getObjektLista(
                    $periodDto->getDimensionNr(),
                    $periodDto->getObjektNr()
                  )
                : StringUtil::$SP0
        );
        $saldo     = $periodDto->isSaldoSet()
            ? CommonFactory::formatAmount( $periodDto->getSaldo())
            : self::$ZERO;
        $kvantitet = $periodDto->isKvantitetSet()
            ? $periodDto->getKvantitet()
            : StringUtil::$SP0;
        $this->appendKsumma( $saldo, $kvantitet );
        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEFMT6,
                    $label,
                    $arsnr,
                    $period,
                    $kontoNr,
                    $objektLista,
                    $saldo,
                    $kvantitet
                )
            )
        );
    }

    /**
     * Managing writing of #VER and #TRANS
     *
     * @return void
     */
    protected function writeVerDtos() : void
    {
        if( empty( $this->sie4Dto->countVerDtos())) {
            return;
        }
        foreach( $this->sie4Dto->getVerDtos() as $verDto ) {
            // empty row before each #VER
            $this->output->append( StringUtil::$SP0 );
            $this->writeVerDto( $verDto );
        } // end foreach
    }

    /**
     * Writes #VER and #TRANS
     *
     * #VER serie vernr verdatum vertext regdatum sign
     *
     * @param VerDto $verDto
     * @return void
     */
    protected function writeVerDto( VerDto $verDto ) : void
    {
        $this->appendKsumma( self::VER );
        if( $verDto->isSerieSet()) {
            $serie = $verDto->getSerie();
            $this->appendKsumma( $serie );
        }
        else {
            $serie = StringUtil::$DOUBLEQUOTE;
        }

        if( $verDto->isVernrSet()) {
            $vernr = $verDto->getVernr();
            $this->appendKsumma( $vernr );
        }
        else {
            $vernr = StringUtil::$DOUBLEQUOTE;
        }

        $datum     = $verDto->isVerdatumSet()
            ? $verDto->getVerdatum()
            : new DateTime();
        $verdatum  = $datum->format( self::SIE4YYYYMMDD );
        $this->appendKsumma( $verdatum );

        if( $verDto->isVertextSet()) {
            $vertext = StringUtil::utf8toCP437( $verDto->getVertext());
            $this->appendKsumma( $vertext );
            $vertext = StringUtil::quoteString( $vertext );
        }
        else {
            $vertext = StringUtil::$DOUBLEQUOTE;
        }

        if( ! $verDto->isRegdatumSet()) {
            $regdatum = StringUtil::$DOUBLEQUOTE;
        }
        else {
            $regdatum = $verDto->getRegdatum()->format( self::SIE4YYYYMMDD );
            if( $verdatum === $regdatum ) {
                // skip if equal
                $regdatum = StringUtil::$DOUBLEQUOTE;
            }
            else {
                $this->appendKsumma( $regdatum );
            }
        }

        if( $verDto->isSignSet()) {
            $sign = StringUtil::utf8toCP437( $verDto->getSign());
            $this->appendKsumma( $sign );
            $sign = StringUtil::quoteString( $sign );
        }
        else {
            $sign = StringUtil::$SP0;
        }

        $row = rtrim(
            sprintf(
                self::$SIEFMT6,
                self::VER,
                $serie,
                (string) $vernr,
                $verdatum,
                $vertext,
                $regdatum,
                $sign
            )
        );
        $this->output->append( StringUtil::d2qRtrim( $row ));

        $this->output->append( StringUtil::$CURLYBRACKETS[0] );
        foreach( $verDto->getTransDtos() as $transDto ) {
            $this->writeTransDto( $transDto, $verdatum );
        }
        $this->output->append( StringUtil::$CURLYBRACKETS[1] );
    }

    /**
     * Write #TRANS, #RTRANS, #BTRANS
     *
     * #TRANS kontonr {objektlista} belopp transdat(opt) transtext(opt) kvantitet sign
     * ex  #TRANS 7010 {"1" "456" "7" "47"} 13200.00
     * Note, sign is skipped
     *
     * @param TransDto $transDto
     * @param string   $verdatum
     * @return void
     */
    protected function writeTransDto( TransDto $transDto, string $verdatum ) : void
    {
        $label   = $transDto->getTransType();
        $kontonr = StringUtil::utf8toCP437( $transDto->getKontoNr());
        $this->appendKsumma( $label, $kontonr );

        if( 0 < $transDto->countObjektlista()) {
            [ $objektlista, $ksummaPart ] = self::getTransObjektLista(
                $transDto->getObjektlista()
            );
            if( ! empty( $objektlista )) {
                $this->appendKsumma( $ksummaPart );
            }
        }
        else {
            $objektlista = StringUtil::curlyBacketsString( StringUtil::$SP0 );
        }

        $belopp = $transDto->isBeloppSet()
            ? CommonFactory::formatAmount( $transDto->getBelopp())
            : self::$ZERO;
        $this->appendKsumma( $belopp );

        if( $transDto->isTransdatSet()) {
            $transdat = $transDto->getTransdat()->format( self::SIE4YYYYMMDD );
            if( $transdat === $verdatum ) {
                // skip if equal
                $transdat = StringUtil::$DOUBLEQUOTE;
            }
            else {
                $this->appendKsumma( $transdat );
            }
        }
        else {
            $transdat = StringUtil::$DOUBLEQUOTE;
        }

        if( $transDto->isTranstextSet()) {
            $transtext = StringUtil::utf8toCP437( $transDto->getTranstext());
            $this->appendKsumma( $transtext );
            $transtext = StringUtil::quoteString( $transtext );
        }
        else {
            $transtext = StringUtil::$DOUBLEQUOTE;
        }

        if( $transDto->isKvantitetSet()) {
            $kvantitet = $transDto->getKvantitet();
            $this->appendKsumma( $kvantitet );
        }
        else {
            $kvantitet = StringUtil::$DOUBLEQUOTE;
        }

        if( $transDto->isSignSet()) {
            $sign = StringUtil::utf8toCP437( $transDto->getSign());
            $this->appendKsumma( $sign );
            $sign = StringUtil::quoteString( $sign );
        }
        else {
            $sign = StringUtil::$SP0;
        }

        $row = rtrim(
            sprintf(
                self::$SIEFMT7,
                $label,
                $kontonr,
                $objektlista,
                $belopp,
                $transdat,
                $transtext,
                $kvantitet,
                $sign
            )
        );
        $this->output->append( StringUtil::d2qRtrim( $row ));
    }

    /**
     * Return array : string with (quoted) dimId and objectId pairs (if set), ksummapart
     *
     * @param DimObjektDto[] $dimObjektDtos
     * @return string[]
     */
    protected static function getTransObjektLista( array $dimObjektDtos ) : array
    {
        $objektlista = [];
        $ksummaPart  = StringUtil::$SP0;
        foreach( $dimObjektDtos as $dimObjektDto ) {
            $dimId         = (string) $dimObjektDto->getDimensionNr();
            $objektlista[] = StringUtil::quoteString( $dimId );
            $objektId      = StringUtil::utf8toCP437( $dimObjektDto->getObjektNr());
            $objektlista[] = StringUtil::quoteString( $objektId );
            $ksummaPart   .= $dimId . $objektId;
        } // end foreach
        return [
            StringUtil::curlyBacketsString( implode( StringUtil::$SP1, $objektlista )),
            $ksummaPart
        ];
    }

    /**
     * Computes and writes trailing Ksumma
     *
     * @return void
     */
    protected function computeAndWriteKsumma() : void
    {
        // empty row before
        $this->output->append( StringUtil::$SP0 );
        $this->output->append(
            sprintf(
                self::$SIEFMT1,
                self::KSUMMA,
                (string) crc32( $this->getKsummaBase())
            )
        );
    }

    /**
     * @param Sie4Dto $sie4Dto
     * @return self
     */
    public function setSie4Dto( Sie4Dto $sie4Dto ) : self
    {
        $this->sie4Dto = $sie4Dto;
        return $this;
    }
}
