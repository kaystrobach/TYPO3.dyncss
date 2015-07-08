<?php


namespace KayStrobach\Dyncss\XClass;


class RteHtmlAreaBase extends \TYPO3\CMS\Rtehtmlarea\RteHtmlAreaBase {
	/**
	 * @return string
	 */
	public function getContentCssFileName() {

		$this->thisConfig['contentCSS'] = parent::getContentCssFileName();

		$this->thisConfig['contentCSS'] = substr(\KayStrobach\Dyncss\Service\DyncssService::getCompiledFile($this->thisConfig['contentCSS']), 3);

		return parent::getContentCssFileName();
	}
}