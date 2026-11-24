<?php

declare(strict_types=1);

namespace KayStrobach\Dyncss\Tests\Functional\Fixtures;

use KayStrobach\Dyncss\Parser\AbstractParser;

/**
 * Test parser for the file ending "upper": the "compiled" stylesheet is the input in upper case.
 */
class UppercaseParser extends AbstractParser
{
    protected function _compileFile($inputFilename, $preparedFilename, $outputFilename, $cacheFilename)
    {
        return strtoupper((string)file_get_contents($inputFilename));
    }

    protected function _compile($string, $name = null)
    {
        return strtoupper((string)$string);
    }

    protected function _prepareCompile($string)
    {
        return $string;
    }

    public function getOverrides(): array
    {
        return $this->overrides;
    }
}
