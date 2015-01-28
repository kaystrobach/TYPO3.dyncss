<?php

namespace KayStrobach\Dyncss\Parser;

/**
 * @todo add docblock
 */
class DummyParser extends \KayStrobach\Dyncss\Parser\AbstractParser {

	/**
	 * @param $inputFilename
	 * @param $preparedFilename
	 * @param $outputFilename
	 * @param $cacheFilename
	 * @return mixed|string
	 *
	 * @todo add typehinting
	 */
	protected function _compileFile($inputFilename, $preparedFilename, $outputFilename, $cacheFilename) {
		return '';
	}

	/**
	 * @param $string
	 * @param null $name
	 * @return mixed
	 *
	 * @todo add typehinting
	 */
	protected function _compile($string, $name = null) {
		return $string;
	}

	/**
	 * @param $string
	 * @return mixed
	 *
	 * @todo add typehinting
	 */
	protected function _prepareCompile($string) {
		return $string;
	}
}