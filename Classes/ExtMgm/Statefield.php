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
				$parser = new $class();
				$buffer .= '<tr><td>*.' . $extension . '</td><td>' . $class . '</td><td>' . $parser->getVersion() . '</td></tr>';
			}
			$flashMessage = new FlashMessage(
				'Congrats, you have ' . count($handlers) . ' handlers registered.' ,
				'',
				FlashMessage::OK
			);
			return $flashMessage->render() . '<table class="t3-table"><thead><tr><th>extension</th><th>class</th><th>version</th></tr></thead>' . $buffer . '</table>';
		} else {
			$flashMessage = new FlashMessage(
				'Please install one of the dyncss_* extensions',
				'No handler registered! - No dynamic css is handled at all ;/',
				FlashMessage::ERROR
			);
			return $flashMessage->render();
		}

	}
}