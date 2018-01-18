<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\helpers;

/**
 * Class ExceptionLogFileHelper
 *
 * This class contains helper methods to deal with the exception log file in the tests
 *
 * @package OxidEsales\TestingLibrary\helpers
 */
class ExceptionLogFileHelper
{
    /**
     * @var The fully qualified path to the exception log file
     */
    protected $exceptionLogFile;

    /**
     * ExceptionLogHelper constructor.
     *
     * @param string $exceptionLogFile The fully qualified path to the exception log file
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    public function __construct($exceptionLogFile)
    {
        if (!$exceptionLogFile || !is_string($exceptionLogFile)) {
            throw new \OxidEsales\Eshop\Core\Exception\StandardException('Constructor parameter $exceptionLogFile must be a non empty string');
        }
        $this->exceptionLogFile = $exceptionLogFile;
    }

    /**
     * Use this method in _justified_ cases to clear exception log, e.g. if you are testing  exceptions and their behavior.
     * Do _not_ use this method to silence exceptions, if you do not understand why they are thrown or if you are too lazy to fix the root cause.
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    public function clearExceptionLogFile()
    {
        if (!$filePointerResource = fopen($this->exceptionLogFile, 'w')) {
            throw new \OxidEsales\Eshop\Core\Exception\StandardException('File ' . $this->exceptionLogFile . ' could not be opened in write mode');
        }
        if (!fclose($filePointerResource)) {
            throw new \OxidEsales\Eshop\Core\Exception\StandardException('File pointer resource for file ' . $this->exceptionLogFile . ' could not be closed');
        };
    }


    /**
     * Return an array of arrays with parsed exception lines
     *
     * @return array
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    public function getParsedExceptions()
    {
        $parsedExceptions = [];

        $exceptions = $this->getExceptionLinesFromLogFile();
        $logFileContent = file_get_contents($this->exceptionLogFile);
        foreach ($exceptions as $exception) {
            /**
             * See \OxidEsales\EshopCommunity\Core\Exception\ExceptionHandler::getFormattedException
             * for the current log file format.
             *
             */
            $logEntryDetails = explode('[', $exception);
            array_walk(
                $logEntryDetails,
                function (&$detail) {
                    $detail = trim(str_replace(['type ', 'code ', 'file ', 'line ', 'message ',], '', $detail), '] ');
                }
            );
            list(, $timestamp, $level, $type, $code, $file, $line, $message) = $logEntryDetails;

            $parsedExceptions[] = [
                'timestamp' => $timestamp,
                'level'     => $level,
                'type'      => $type,
                'code'      => $code,
                'file'      => $file,
                'line'      => $line,
                'message'   => $message,
            ];
        }
        $parsedExceptions['original_content'] = $logFileContent;

        return $parsedExceptions;
    }

    /**
     * Return an array, which only contains the lines with information about the exception, not the whole stacktrace
     *
     * @return array
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    protected function getExceptionLinesFromLogFile()
    {
        $exceptionLogLines = file($this->exceptionLogFile, FILE_IGNORE_NEW_LINES);
        if (false === $exceptionLogLines) {
            throw new \OxidEsales\Eshop\Core\Exception\StandardException('File ' . $this->exceptionLogFile . ' could not be read');
        }

        $exceptionEntries = array_filter(
            $exceptionLogLines,
            function ($entry) {
                return false !== strpos($entry, '[exception] [type ');
            }
        );

        return $exceptionEntries;
    }
}
