<?php

declare(strict_types=1);

namespace KayStrobach\Dyncss\Tests\Functional;

use KayStrobach\Dyncss\EventListener\AddClearCacheAction;
use TYPO3\CMS\Backend\Backend\Event\ModifyClearCacheActionsEvent;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Cache command "dyncss": entry in the cache menu of the backend toolbar, removes the compiled files.
 */
class ClearCacheTest extends AbstractDyncssTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/be_users.csv');
    }

    /**
     * @return list<string> identifiers of the cache actions
     */
    private function cacheActionsFor(int $userUid): array
    {
        $this->setUpBackendUser($userUid);
        $event = new ModifyClearCacheActionsEvent([], []);
        $this->get(AddClearCacheAction::class)($event);
        return $event->getCacheActionIdentifiers();
    }

    public function testCacheMenuHasTheEntryForAdministrators(): void
    {
        self::assertSame(['dyncss'], $this->cacheActionsFor(1));
    }

    public function testCacheMenuHasTheEntryForUsersAllowedToClearTheSystemCache(): void
    {
        self::assertSame(['dyncss'], $this->cacheActionsFor(3));
    }

    public function testCacheMenuHasNoEntryForEditors(): void
    {
        // the Testing context is no Development context
        self::assertSame([], $this->cacheActionsFor(2));
    }

    public function testCacheCommandRemovesTheCompiledFiles(): void
    {
        // the current folder and the one of older versions
        $directories = [Environment::getPublicPath() . '/typo3temp/assets/DynCss', Environment::getPublicPath() . '/typo3temp/DynCss'];
        foreach ($directories as $directory) {
            GeneralUtility::mkdir_deep($directory);
            file_put_contents($directory . '/style.css', 'body {}');
        }

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], [], $this->setUpBackendUser(1));
        $dataHandler->clear_cacheCmd('dyncss');

        foreach ($directories as $directory) {
            self::assertDirectoryDoesNotExist($directory);
        }
    }
}
