<?php

namespace KayStrobach\Dyncss\ExtMgm;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * @todo missing docblock
 */
class Statefield {

	/**
	 * @todo missing docblock
	 */
	function main() {
		$buffer = '';
		$registry = GeneralUtility::makeInstance('KayStrobach\Dyncss\Configuration\BeRegistry');
		$handlers = $registry->getAllFileHandler();
		if(count($handlers)) {
			foreach($handlers as $extension => $class) {
				$buffer .= '<tr><td>*.' . $extension . '</td><td>' . $class . '</td></tr>';
			}
			$flashMessage = new FlashMessage(
				'<table><cols><col width="70" /><col width="*"></cols>' . $buffer . '</table>',
				'Registered Handlers',
				FlashMessage::OK
			);
		} else {
			$flashMessage = new FlashMessage(
				'Please install one of the dyncss_* extensions',
				'No handler registered! - No dynamic css is handled at all ;/',
				FlashMessage::ERROR
			);
		}
		return $flashMessage->render();
	}
}