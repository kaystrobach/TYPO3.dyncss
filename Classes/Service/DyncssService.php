<?php

namespace KayStrobach\Dyncss\Service;

use KayStrobach\Dyncss\Configuration\BeRegistry;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Compiles a dynamic stylesheet (less, scss, ...) with the parser registered for its file ending.
 */
class DyncssService
{
    /**
     * @return string path to the compiled file, or the input file, if no parser handles it
     */
    public static function getCompiledFile(string $inputFile): string
    {
        $currentFile = self::fixPathForInput($inputFile);
        $parser = BeRegistry::get()->getFileHandler(pathinfo($currentFile, PATHINFO_EXTENSION));
        if ($parser === null) {
            return $inputFile;
        }
        $parser->setOverrides(self::getOverrides());
        return self::fixPathForOutput($parser->compileFile($currentFile));
    }

    /**
     * Absolute file name of the given file: EXT: paths, paths relative to the public folder and
     * web paths like /_assets/<hash>/Css/style.less (composer mode).
     */
    protected static function fixPathForInput(string $file): string
    {
        if ($file === '') {
            throw new \InvalidArgumentException('fixPathForInput needs a valid $file, the given value was empty', 1099502636);
        }
        // query string of versioned files
        $file = explode('?', $file, 2)[0];
        if (PathUtility::isExtensionPath($file)) {
            return GeneralUtility::getFileAbsFileName($file);
        }
        if (str_starts_with($file, '/')) {
            if (is_file($file)) {
                return $file;
            }
            // web path below the public folder
            return Environment::getPublicPath() . $file;
        }
        if (self::isBackendRequest() && !Environment::isCli()) {
            // paths relative to the backend entry point of older TYPO3 versions ("../typo3conf/...")
            $relativeToBackend = GeneralUtility::resolveBackPath(Environment::getPublicPath() . '/typo3/' . $file);
            if (is_file($relativeToBackend)) {
                return $relativeToBackend;
            }
        }
        $absoluteFile = GeneralUtility::getFileAbsFileName($file);
        return $absoluteFile !== '' ? $absoluteFile : $file;
    }

    /**
     * Path for the page renderer: relative to the public folder in the frontend, a web path in the backend.
     */
    protected static function fixPathForOutput(string $file): string
    {
        if (self::isFrontendRequest()) {
            return str_replace(Environment::getPublicPath() . '/', '', $file);
        }
        if (self::isBackendRequest()) {
            return PathUtility::getAbsoluteWebPath($file);
        }
        return $file;
    }

    /**
     * Overrides (variables) for the compiler: plugin.tx_dyncss.overrides in the frontend (content objects),
     * the overrides of the registry in the backend.
     *
     * @return array<string, string>
     */
    public static function getOverrides(): array
    {
        $request = self::getRequest();
        if (self::isFrontendRequest()) {
            $overridesConfiguration = self::getFrontendSetup($request)['plugin.']['tx_dyncss.']['overrides.'] ?? [];
            if (!is_array($overridesConfiguration) || $overridesConfiguration === []) {
                return [];
            }
            $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $contentObjectRenderer->setRequest($request);
            $overrides = [];
            foreach ($overridesConfiguration as $varName => $varCObj) {
                if (!str_ends_with((string)$varName, '.')) {
                    $overrides[$varName] = $contentObjectRenderer->cObjGetSingle(
                        (string)$varCObj,
                        $overridesConfiguration[$varName . '.'] ?? []
                    );
                }
            }
            return $overrides;
        }
        if (self::isBackendRequest()) {
            return BeRegistry::get()->getAllOverrides();
        }
        return [];
    }

    /**
     * TypoScript setup of the frontend request. Not available for cached pages since TYPO3 v13
     * (only the parts needed for USER_INT are computed), there are no overrides then.
     */
    protected static function getFrontendSetup(?ServerRequestInterface $request): array
    {
        $frontendTypoScript = $request?->getAttribute('frontend.typoscript');
        if ($frontendTypoScript === null) {
            return [];
        }
        try {
            return $frontendTypoScript->getSetupArray();
        } catch (\RuntimeException) {
            return [];
        }
    }

    protected static function getRequest(): ?ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        return $request instanceof ServerRequestInterface ? $request : null;
    }

    protected static function isFrontendRequest(): bool
    {
        $request = self::getRequest();
        return $request !== null && $request->getAttribute('applicationType') !== null
            && ApplicationType::fromRequest($request)->isFrontend();
    }

    protected static function isBackendRequest(): bool
    {
        $request = self::getRequest();
        return $request !== null && $request->getAttribute('applicationType') !== null
            && ApplicationType::fromRequest($request)->isBackend();
    }
}
