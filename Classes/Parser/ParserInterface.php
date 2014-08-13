<?php

/**
 * @todo missing docblock
 */
interface tx_Dyncss_Parser_ParserInterface {

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