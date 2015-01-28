<?php

namespace KayStrobach\Dyncss\Hooks;

/**
 * @todo missing docblock
 */
class TslibFeContentPostProcAllHook extends \KayStrobach\Dyncss\Hooks\T3libPageRendererRenderPreProcessHook {

	/**
	 * @todo add typehinting
	 */
	function main(&$param, &$obj) {
		$xml = new \DOMDocument('1.0', 'utf-8');
		$xml->substituteEntities = FALSE;
		$xml->loadHTML(mb_convert_encoding($param['pObj']->content, 'HTML-ENTITIES', "UTF-8"));

		$xPath = new \DOMXpath($xml);
		$linkElements = $xPath->query("//link[@rel='stylesheet']");

		#echo "KayStrobach\Dyncss\Hooks\TslibFeContentPostProcAllHook - elements found " . $linkElements->length . "\n";

		if($linkElements->length > 0)  {
			for($i = 0; $i < $linkElements->length; $i++) {
				$cssFile = $linkElements->item($i)->attributes->getNamedItem('href')->nodeValue;
				$questionMarkPos = strpos($cssFile, '?');
				if($questionMarkPos !== FALSE) {
					$cssFile = substr($cssFile, 0, $questionMarkPos);
				}
				$compiledFile = \KayStrobach\Dyncss\Service\DyncssService::getCompiledFile($cssFile);
				if($compiledFile !== $cssFile) {
					#$linkElements->item($i)->attributes->getNamedItem('href')->nodeValue = $compiledFile . '?' . filemtime($compiledFile);
					$param['pObj']->content = str_replace($cssFile, $compiledFile, $param['pObj']->content);
				}
			}
		}
		#$param['pObj']->content = $xml->saveHTML();
		// @todo remove refactored comments ...
	}
}

