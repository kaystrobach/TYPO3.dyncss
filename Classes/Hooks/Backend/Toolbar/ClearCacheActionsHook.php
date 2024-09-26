<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.04.15
 * Time: 15:52
 */
namespace KayStrobach\Dyncss\Hooks\Backend\Toolbar;

use TYPO3\CMS\Backend\Toolbar\ClearCacheActionsHookInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Backend\Routing\UriBuilder;

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
        $clearCacheSystemUser = (bool)($this->getBackendUser()->getTSConfig()['options.']['clearCache.']['system'] ?? false);
        $isDevelopment = Environment::getContext()->isDevelopment();
        $isAdmin = $this->getBackendUser()->isAdmin();
        if ($clearCacheSystemUser || $isDevelopment || $isAdmin) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $uriParameters = ['cacheCmd' => 'dyncss', 'ajaxCall' => 1];
            $translationPrefix = 'LLL:EXT:dyncss/Resources/Private/Language/locallang.xlf:dyncss.toolbar.clearcache.';
            $cacheActions[] = [
                'id' => 'dyncss',
                'title' => $translationPrefix . 'title',
                'description' => $translationPrefix . 'description',
                'href' => (string)$uriBuilder->buildUriFromRoute('tce_db', $uriParameters),
                'iconIdentifier' => 'actions-system-cache-clear-dyncss'
            ];
            $optionValues[] = 'dyncss';
        }
    }

    /**
     * Returns the current BE user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
