<?php

namespace KayStrobach\Dyncss\ExtMgm;
use KayStrobach\Dyncss\Parser\AbstractParser;
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
				/** @var AbstractParser $parser */
				$parser = new $class();
				$buffer .= '<tr><td>*.' . $extension . '</td>';
				$buffer .= '<td>' . $class . '</td>';
				$buffer .= '<td><a href="' . $parser->getParserHomepage() . '" target="_blank">' . $parser->getParserName() . '</a></td>';
				$buffer .= '<td>' . $parser->getVersion() . '</td>';
				$buffer .= '</tr>';
			}
			$flashMessage = new FlashMessage(
				'Congrats, you have ' . count($handlers) . ' handlers registered.' ,
				'',
				FlashMessage::OK
			);
			return $flashMessage->render() . '<table class="t3-table"><thead><tr><th>extension</th><th>class</th><th>name</th><th>version</th></tr></thead>' . $buffer . '</table>';
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