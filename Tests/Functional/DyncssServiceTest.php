<?php

declare(strict_types=1);

namespace KayStrobach\Dyncss\Tests\Functional;

use KayStrobach\Dyncss\Command\CompileCommand;
use KayStrobach\Dyncss\Configuration\BeRegistry;
use KayStrobach\Dyncss\Service\DyncssService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;

class DyncssServiceTest extends AbstractDyncssTestCase
{
    private const FIXTURE = 'EXT:dyncss_fixture_imported/Resources/Public/Css/style.upper';

    private function assertCompiled(string $compiledFile): void
    {
        $absoluteFile = str_starts_with($compiledFile, '/') ? $compiledFile : Environment::getPublicPath() . '/' . $compiledFile;
        self::assertStringContainsString('typo3temp/DynCss/style.upper-', $compiledFile);
        self::assertStringEndsWith('.css', $compiledFile);
        self::assertSame("BODY { COLOR: RED; }\n", file_get_contents($absoluteFile));
    }

    private function frontendRequest(object $frontendTypoScript): ServerRequest
    {
        return (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);
    }

    public function testFilesWithoutParserAreReturnedUnchanged(): void
    {
        self::assertSame('EXT:dyncss/Resources/Public/Icons/actions-system-cache-clear-dyncss.svg', DyncssService::getCompiledFile('EXT:dyncss/Resources/Public/Icons/actions-system-cache-clear-dyncss.svg'));
    }

    public function testExtensionPathIsCompiled(): void
    {
        $this->assertCompiled(DyncssService::getCompiledFile(self::FIXTURE));
    }

    public function testWebPathBelowThePublicFolderIsCompiled(): void
    {
        // page renderer paths like /_assets/<hash>/Css/style.less (composer mode) or /typo3conf/ext/… (classic mode)
        $webPath = '/typo3conf/ext/dyncss_fixture_imported/Resources/Public/Css/style.upper?1700000000';
        $this->assertCompiled(DyncssService::getCompiledFile($webPath));
    }

    public function testFrontendGetsAPathRelativeToThePublicFolder(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->frontendRequest(new class () {
            public function getSetupArray(): array
            {
                return [];
            }
        });
        $compiledFile = DyncssService::getCompiledFile(self::FIXTURE);
        self::assertStringStartsWith('typo3temp/DynCss/', $compiledFile);
        $this->assertCompiled($compiledFile);
    }

    public function testFrontendOverridesComeFromTypoScript(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->frontendRequest(new class () {
            public function getSetupArray(): array
            {
                return ['plugin.' => ['tx_dyncss.' => ['overrides.' => [
                    'linkColor' => 'TEXT',
                    'linkColor.' => ['value' => '#ff0000'],
                ]]]];
            }
        });
        self::assertSame(['linkColor' => '#ff0000'], DyncssService::getOverrides());
    }

    public function testNoOverridesWithoutTypoScriptSetup(): void
    {
        // TYPO3 v13+: the setup is not available for pages from the page cache
        $GLOBALS['TYPO3_REQUEST'] = $this->frontendRequest(new class () {
            public function getSetupArray(): array
            {
                throw new \RuntimeException('Setup array has not been initialized', 1700000000);
            }
        });
        self::assertSame([], DyncssService::getOverrides());
    }

    public function testBackendOverridesComeFromTheRegistry(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/typo3/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
        BeRegistry::get()->setOverride('linkColor', 'blue');
        self::assertSame('blue', DyncssService::getOverrides()['linkColor'] ?? null);
    }

    public function testNoOverridesWithoutRequest(): void
    {
        self::assertSame([], DyncssService::getOverrides());
    }

    public function testCommandCompilesAFile(): void
    {
        $tester = new CommandTester(new CompileCommand());
        self::assertSame(Command::SUCCESS, $tester->execute(['file' => self::FIXTURE]));
        $this->assertCompiled(trim($tester->getDisplay()));
    }

    public function testCommandFailsForMissingFiles(): void
    {
        $tester = new CommandTester(new CompileCommand());
        self::assertSame(Command::FAILURE, $tester->execute(['file' => 'EXT:dyncss/missing.upper']));
        self::assertStringContainsString('not found', $tester->getDisplay());
    }
}
