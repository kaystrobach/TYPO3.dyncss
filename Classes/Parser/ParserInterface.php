<?php

namespace KayStrobach\Dyncss\Parser;

/**
 * @todo missing docblock
 */
interface ParserInterface {

	/**
	* @todo missing docblock
	*/
	function setOverrides($overrides);

	/**
	* @todo missing docblock
	*/
	function compile($string, $name = null);

	/**
	* @todo missing docblock
	*/
	function compileFile($inputFilename, $outputFilename = null);
}