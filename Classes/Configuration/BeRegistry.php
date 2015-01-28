<?php

namespace KayStrobach\Dyncss\Configuration;
/***************************************************************
* Copyright notice
*
* (c) 2012 Kay Strobach <typo3@kay-strobach.de>
*
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Kay Strobach
 * @package Less
 */
class BeRegistry implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var array $overrides
	 */
	protected $overrides = array();

	/**
	 *
	 * @var array $fileHandler
	 */
	protected $fileHandler = array();

	/**
	 * @return \KayStrobach\Dyncss\Configuration\BeRegistry
	 */
	static function get() {
		return GeneralUtility::makeInstance('KayStrobach\Dyncss\Configuration\BeRegistry');
	}

	/**
	 * @param $extension
	 * @param $class
	 */
	function registerFileHandler($extension, $class) {
		$this->fileHandler[$extension] = $class;
	}

	/**
	 * @param $extension
	 * @return null|\KayStrobach\Dyncss\Parser\AbstractParser
	 */
	function getFileHandler($extension) {
		if(array_key_exists($extension, $this->fileHandler)) {
			//@todo check for interface
			//@todo use factory here
			return GeneralUtility::makeInstance($this->fileHandler[$extension]);
		} else {
			return NULL;
		}
	}

	/**
	 * @todo missing documentation
	 */
	function getAllFileHandler() {
		return $this->fileHandler;
	}

	/**
	 * get an override value
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	function getOverride($name) {
		if(array_key_exists($name, $this->overrides)) {
			return $this->overrides[$name];
		} else {
			return null;
		}
	}

	/**
	 * set an override value
	 *
	 * @param string $name
	 * @param $value
	 */
	function setOverride($name, $value) {
		$this->overrides[$name] = $value;
	}

	/**
	 * get all override values
	 *
	 * @return array
	 */
	function getAllOverrides() {
		return $this->overrides;
	}
}