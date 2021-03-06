<?php

namespace KayStrobach\Dyncss\Hooks;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo missing docblock
 */
class T3libTcemainHook
{
    /**
     * Deletes DynCss folders inside typo3temp/.
     *
     * @param array                                    $params
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     */
    public function clearCachePostProc(array $params, DataHandler &$pObj)
    {
        if (!isset($params['cacheCmd'])) {
            return;
        }
        switch ($params['cacheCmd']) {
            case 'dyncss':
                $pObj->clear_cacheCmd("pages");
                GeneralUtility::rmdir(Environment::getPublicPath() . '/typo3temp/Cache/Data/DynCss', true);
                GeneralUtility::rmdir(Environment::getPublicPath() . '/typo3temp/DynCss', true);
                break;
            default:

        }
    }
}
