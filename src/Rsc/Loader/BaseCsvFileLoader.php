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
namespace Kigkonsult\Sie4Sdk\Rsc\Loader;

use InvalidArgumentException;
use Kigkonsult\Sie4Sdk\Util\FileUtil;
use RuntimeException;

use function count;
use function file;
use function implode;
use function sprintf;
use function str_getcsv;

abstract class BaseCsvFileLoader
{
    /**
     * @var string|null
     */
    protected ?string $fileName = null;

    /**
     * @var string
     */
    protected string $separator = ',';

    /**
     * @var string
     */
    protected string $encloser = "\"";

    /**
     * @var string
     */
    protected string $escape = '\\';

    /**
     * Class constructor
     *
     * @param string[] $config   [ filename, separator, encloser, escape ]   default above, all opt
     */
    public function __construct( array $config = [] )
    {
        $this->setConfig( $config );
    }

    /**
     * Class factory method
     *
     * @param string[] $config   [ filename, separator, encloser, escape ]   default above, all opt
     * @return self
     * @throws InvalidArgumentException
     */
    public static function factory( array $config = [] ) : self
    {
        $class = static::class;
        return new $class( $config );
    }

    /**
     * @return mixed[]
     */
    abstract public function getOutput() : array;

    /**
     * Set Csv config
     *   comma ',' as field separator,
     *   string enclosing quotes '"',
     *   escape "\",
     *   all configurable
     *
     * @param string[] $config [ filename, separator, encloser, escape ]   default above, all opt
     * @return self
     */
    public function setConfig( array $config ) : self
    {
        if( isset( $config[0] )) {
            $this->setFileName( $config[0] );
        }
        if( isset( $config[1] )) {
            $this->setSeparator( $config[1] );
        }
        if( isset( $config[2] )) {
            $this->setEncloser( $config[2] );
        }
        if( isset( $config[3] )) {
            $this->setEscape( $config[3] );
        }
        return $this;
    }

    /**
     * Asserts input file, return file content as array
     *
     * @return string[]
     */
    protected function getInput(): array
    {
        static $OPTS = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
        static $ERR1 = 'No read file given';
        static $ERR2 = 'Failure on read file ';
        static $ERR3 = 'Error, empty file ';
        if( empty( $this->fileName )) {
            throw new RuntimeException( $ERR1, 18101 );
        }
        $input = file( $this->fileName, $OPTS );
        if( false === $input ) {
            throw new RuntimeException( $ERR2 . $this->fileName, 18102 );
        }
        if( empty( $input )) {
            throw new RuntimeException( $ERR3 . $this->fileName, 18103 );
        }
        return $input;
    }

    /**
     * Parse csv line into array
     *
     * @param string $line
     * @param int $lineNo
     * @param int $minExpect
     * @return string[]
     */
    protected function getRowdata( string $line, int $lineNo, int $minExpect ) : array
    {
        static $ERR5 = 'Row #%d has less than %d elements (%s)';
        $rowData     = str_getcsv( $line, $this->separator, $this->encloser, $this->escape );
        if( $minExpect > count( $rowData )) {
            throw new RuntimeException(
                sprintf( $ERR5, $lineNo, $minExpect, implode( $this->separator, $rowData )),
                18105
            );
        }
        return $rowData;
    }

    /**
     * @return null|string
     */
    public function getFileName(): ? string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return self
     * @throws InvalidArgumentException
     */
    public function setFileName( string $fileName ): self
    {
        FileUtil::assertReadFile( $fileName, 18801 );
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * @param string $separator
     * @return self
     */
    public function setSeparator( string $separator ): self
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * @return string
     */
    public function getEncloser(): string
    {
        return $this->encloser;
    }

    /**
     * @param string $encloser
     * @return self
     */
    public function setEncloser( string $encloser ): self
    {
        $this->encloser = $encloser;
        return $this;
    }

    /**
     * @return string
     */
    public function getEscape(): string
    {
        return $this->escape;
    }

    /**
     * @param string $escape
     * @return self
     */
    public function setEscape( string $escape ): self
    {
        $this->escape = $escape;
        return $this;
    }

}
