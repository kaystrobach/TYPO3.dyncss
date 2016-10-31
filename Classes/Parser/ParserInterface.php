<?php

namespace KayStrobach\Dyncss\Parser;

/**
 * @todo missing docblock
 */
interface ParserInterface
{
    /**
     * @todo missing docblock
     */
    public function setOverrides($overrides);

    /**
     * @todo missing docblock
     */
    public function compile($string, $name = null);

    /**
     * @todo missing docblock
     */
    public function compileFile($inputFilename, $outputFilename = null);
}
