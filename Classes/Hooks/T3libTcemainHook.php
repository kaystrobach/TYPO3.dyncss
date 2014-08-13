<?php

/**
 * @todo missing docblock
 */
class tx_dyncss_Hooks_T3libTcemainHook {

	/**
	 *
	 * @param string $params
	 * @param type $pObj
	 *
	 * @todo add typehinting
	 */
	function clearCachePostProc($params, &$pObj) {
		if(isset($params['cacheCmd']) && $params['cacheCmd'] = 'pages') {
			t3lib_div::rmdir(
				PATH_site . 'typo3temp/Cache/Data/DynCss',
				TRUE
			);
		}
	}
}