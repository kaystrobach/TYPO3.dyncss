<?php

declare(strict_types=1);

namespace KayStrobach\Dyncss\Tests\Functional;

use KayStrobach\Dyncss\Configuration\BeRegistry;
use KayStrobach\Dyncss\Tests\Functional\Fixtures\UppercaseParser;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractDyncssTestCase extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['install'];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/dyncss',
        __DIR__ . '/Fixtures/Extensions/dyncss_fixture_importer',
        __DIR__ . '/Fixtures/Extensions/dyncss_fixture_imported',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        BeRegistry::get()->registerFileHandler('upper', UppercaseParser::class);
        unset($GLOBALS['TYPO3_REQUEST']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST'], $GLOBALS['BE_USER']);
        GeneralUtility::rmdir(Environment::getPublicPath() . '/typo3temp/DynCss', true);
        parent::tearDown();
    }

    protected function fixtureFile(string $pathInExtension): string
    {
        return realpath(__DIR__ . '/Fixtures/Extensions/' . $pathInExtension) ?: '';
    }
}
