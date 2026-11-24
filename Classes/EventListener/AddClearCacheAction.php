<?php

declare(strict_types=1);

namespace KayStrobach\Dyncss\EventListener;

use TYPO3\CMS\Backend\Backend\Event\ModifyClearCacheActionsEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Environment;

/**
 * "Clear DynCss cache" in the cache menu of the backend toolbar, handled by
 * {@see \KayStrobach\Dyncss\Hooks\T3libTcemainHook::clearCachePostProc()}.
 */
final class AddClearCacheAction
{
    private const TRANSLATION_PREFIX = 'LLL:EXT:dyncss/Resources/Private/Language/locallang.xlf:dyncss.toolbar.clearcache.';

    public function __construct(private readonly UriBuilder $uriBuilder) {}

    public function __invoke(ModifyClearCacheActionsEvent $event): void
    {
        $backendUser = $GLOBALS['BE_USER'] ?? null;
        if (!$backendUser instanceof BackendUserAuthentication) {
            return;
        }
        $clearCacheSystemUser = (bool)($backendUser->getTSConfig()['options.']['clearCache.']['system'] ?? false);
        if (!$clearCacheSystemUser && !$backendUser->isAdmin() && !Environment::getContext()->isDevelopment()) {
            return;
        }
        $event->addCacheAction([
            'id' => 'dyncss',
            'title' => self::TRANSLATION_PREFIX . 'title',
            'description' => self::TRANSLATION_PREFIX . 'description',
            'href' => (string)$this->uriBuilder->buildUriFromRoute('tce_db', ['cacheCmd' => 'dyncss']),
            'iconIdentifier' => 'actions-system-cache-clear-dyncss',
        ]);
        $event->addCacheActionIdentifier('dyncss');
    }
}
