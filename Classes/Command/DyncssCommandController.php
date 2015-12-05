<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.03.15
 * Time: 21:32
 */

namespace KayStrobach\Dyncss\Command;


use KayStrobach\Dyncss\Service\DyncssService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class DyncssCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * @param string $file
	 * @throws StopActionException
	 */
	public function compileCommand($file = '') {
		if(!file_exists($file)) {
			$this->outputLine($file . ' not found');
			throw new StopActionException;
		}
		$this->outputLine(DyncssService::getCompiledFile($file));
	}
}