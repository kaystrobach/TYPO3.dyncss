<?php

namespace KayStrobach\Dyncss\Hooks;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo missing docblock
 */
class T3libTcemainHook {

	/**
	 *
	 * @param string $params
	 * @param type $pObj
	 *
	 * @todo add typehinting
	 */
	function clearCachePostProc($params, &$pObj) {
		if(isset($params['cacheCmd']) && $params['cacheCmd'] = 'pages') {
			GeneralUtility::rmdir(
				PATH_site . 'typo3temp/Cache/Data/DynCss',
				TRUE
			);

			GeneralUtility::rmdir(
				PATH_site . 'typo3temp/DynCss',
				TRUE
			);
		}
	}
}