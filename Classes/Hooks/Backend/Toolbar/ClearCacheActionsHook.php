<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.04.15
 * Time: 15:52
 */

namespace KayStrobach\Dyncss\Hooks\Backend\Toolbar;

use TYPO3\CMS\Backend\Toolbar\ClearCacheActionsHookInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ClearCacheActionsHook implements ClearCacheActionsHookInterface {

	/**
	 * Modifies CacheMenuItems array
	 *
	 * @param array $cacheActions Array of CacheMenuItems
	 * @param array $optionValues Array of AccessConfigurations-identifiers (typically  used by userTS with options.clearCache.identifier)
	 *
	 * @return void
	 */
	public function manipulateCacheActions(&$cacheActions, &$optionValues) {
		if ($this->getBackendUser()->getTSConfigVal('options.clearCache.system')
			|| GeneralUtility::getApplicationContext()->isDevelopment()
			|| ((bool)$GLOBALS['TYPO3_CONF_VARS']['SYS']['clearCacheSystem'] === TRUE && $this->getBackendUser()->isAdmin())) {

			/**
			 * Validate Typo3 version and use old core-API for versions below 7.1
			 *
			 * @link https://docs.typo3.org/typo3cms/extensions/core/latest/Changelog/7.1/Deprecation-64922-DeprecatedEntryPoints.html
			 */
			$hrefParams = ['vC' => $this->getBackendUser()->veriCode(), 'cacheCmd' => 'dyncss', 'ajaxCall' => 1];
			if (TYPO3_version < '7.1') {
				$href = 'tce_db.php?' . http_build_query($hrefParams);
			} else {
				$href = BackendUtility::getModuleUrl('tce_db', $hrefParams);
			}

			$cacheActions[] = array(
				'id' => 'dyncss',
				'title' => $this->getLanguageService()->sL('LLL:EXT:dyncss/Resources/Private/Language/locallang.xlf:dyncss.toolbar.clearcache.title', TRUE),
				'description' => $this->getLanguageService()->sL('LLL:EXT:dyncss/Resources/Private/Language/locallang.xlf:dyncss.toolbar.clearcache.description', TRUE),
				'href' => $href . BackendUtility::getUrlToken('tceAction'),
				'icon' => IconUtility::getSpriteIcon('extensions-dyncss-lightning-blue')
			);
			$optionValues[] = 'dyncss';
		}
	}

	/**
	 * Returns LanguageService
	 *
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}

	/**
	 * Returns the current BE user.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

}
