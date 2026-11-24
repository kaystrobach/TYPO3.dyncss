<?php

declare(strict_types=1);

namespace KayStrobach\Dyncss\Tests\Functional;

use KayStrobach\Dyncss\Utilities\LegacyExtensionPath;

/**
 * Imports written for typo3conf/ext/ ("../../../../<extension key>/…") are resolved by extension key,
 * in composer mode the extensions are in vendor/ and the relative path leads nowhere.
 */
class LegacyExtensionPathTest extends AbstractDyncssTestCase
{
    private const IMPORT = '../../../../dyncss_fixture_imported/Resources/Private/Less/variables.less';

    public function testImportOfAnotherExtensionIsResolvedByItsExtensionKey(): void
    {
        $file = LegacyExtensionPath::resolve(self::IMPORT);
        self::assertNotNull($file);
        self::assertSame($this->fixtureFile('dyncss_fixture_imported/Resources/Private/Less/variables.less'), realpath($file));
    }

    public function testFileEndingCanBeOmitted(): void
    {
        self::assertNull(LegacyExtensionPath::resolve(substr(self::IMPORT, 0, -5)), 'without file endings to try');
        $file = LegacyExtensionPath::resolve(substr(self::IMPORT, 0, -5), ['less']);
        self::assertSame($this->fixtureFile('dyncss_fixture_imported/Resources/Private/Less/variables.less'), realpath((string)$file));
    }

    public function testOtherPathsAreLeftToTheParser(): void
    {
        self::assertNull(LegacyExtensionPath::resolve('variables.less'), 'relative to the importing file');
        self::assertNull(LegacyExtensionPath::resolve('/var/www/variables.less'), 'absolute');
        self::assertNull(LegacyExtensionPath::resolve('../../../../not_loaded/Resources/Private/Less/variables.less'), 'unknown extension');
        self::assertNull(LegacyExtensionPath::resolve('../../../../dyncss_fixture_imported/Resources/Private/Less/missing.less'), 'missing file');
    }
}
