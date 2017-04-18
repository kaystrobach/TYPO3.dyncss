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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ClearCacheActionsHook implements ClearCacheActionsHookInterface
{

    /**
     * Modifies CacheMenuItems array
     *
     * @param array $cacheActions Array of CacheMenuItems
     * @param array $optionValues Array of AccessConfigurations-identifiers (typically  used by userTS with options.clearCache.identifier)
     *
     * @return void
     */
    public function manipulateCacheActions(&$cacheActions, &$optionValues)
    {
        if ($this->getBackendUser()->getTSConfigVal('options.clearCache.system')
            || GeneralUtility::getApplicationContext()->isDevelopment()
            || ((bool)$GLOBALS['TYPO3_CONF_VARS']['SYS']['clearCacheSystem'] === true && $this->getBackendUser()->isAdmin())
        ) {
            $hrefParams = ['cacheCmd' => 'dyncss', 'ajaxCall' => 1];
            /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class);
            $translationPrefix = 'LLL:EXT:dyncss/Resources/Private/Language/locallang.xlf:dyncss.toolbar.clearcache.';
            if (version_compare(TYPO3_version, '8.7', '<')) {
                $cacheActions[] = [
                    'id' => 'dyncss',
                    'title' => LocalizationUtility::translate($translationPrefix . 'title', 'Dyncss'),
                    'description' => LocalizationUtility::translate($translationPrefix . 'description', 'Dyncss'),
                    'href' => BackendUtility::getModuleUrl('tce_db', $hrefParams),
                    'icon' => $iconFactory->getIcon('actions-system-cache-clear-dyncss', Icon::SIZE_SMALL)->render()
                ];
            } else {
                $cacheActions[] = [
                    'id' => 'dyncss',
                    'title' => $translationPrefix . 'title',
                    'description' => $translationPrefix . 'description',
                    'href' => BackendUtility::getModuleUrl('tce_db', $hrefParams),
                    'iconIdentifier' => 'actions-system-cache-clear-dyncss'
                ];
            }
            $optionValues[] = 'dyncss';
        }
    }

    /**
     * Returns the current BE user.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
