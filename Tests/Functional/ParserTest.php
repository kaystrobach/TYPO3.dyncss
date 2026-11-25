<?php

declare(strict_types=1);

namespace KayStrobach\Dyncss\Tests\Functional;

use KayStrobach\Dyncss\Parser\DummyParser;
use TYPO3\CMS\Core\Core\Environment;

/**
 * URLs in the compiled stylesheet are rewritten relative to typo3temp/assets/DynCss/.
 */
class ParserTest extends AbstractDyncssTestCase
{
    private function parserFor(string $inputFilename): DummyParser
    {
        $parser = new DummyParser();
        (new \ReflectionProperty($parser, 'inputFilename'))->setValue($parser, $inputFilename);
        return $parser;
    }

    public function testUrlsAreResolvedRelativeToTheCompiledFile(): void
    {
        $parser = $this->parserFor(Environment::getPublicPath() . '/typo3conf/ext/dyncss/Resources/Public/Less/someLessFile.less');
        $urls = [
            '//typo3.org' => '//typo3.org',
            'ftp://typo3.org' => 'ftp://typo3.org',
            'https://typo3.org' => 'https://typo3.org',
            '/absPath' => '/absPath',
            'data:image/png;base64,AAAA' => 'data:image/png;base64,AAAA',
            '../../Public/Contrib/fonts/icons.eot' => '../../../typo3conf/ext/dyncss/Resources/Public/Less/../../Public/Contrib/fonts/icons.eot',
            Environment::getPublicPath() . '/yeah' => '../../../yeah',
        ];
        foreach ($urls as $url => $expected) {
            self::assertSame($expected, $parser->resolveUrlInCss($url), 'url ' . $url);
        }
    }

    public function testPostCompileRewritesUrlsButNoInlineImages(): void
    {
        $parser = $this->parserFor(Environment::getPublicPath() . '/typo3conf/ext/dyncss/Resources/Public/Less/style.less');
        $css = $parser->_postCompile(
            '.a { background: url(../img/a.png); } .b { background: url("data:image/svg+xml;utf8,<svg/>"); }'
        );
        self::assertStringContainsString('url("../../../typo3conf/ext/dyncss/Resources/Public/Less/../img/a.png")', $css);
        self::assertStringContainsString('url("data:image/svg+xml;utf8,<svg/>")', $css);
    }
}
