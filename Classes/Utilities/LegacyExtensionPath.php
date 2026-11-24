<?php

declare(strict_types=1);

namespace KayStrobach\Dyncss\Utilities;

use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Resolves imports between extensions written for the classic layout, where all extensions were
 * folders named by their extension key below typo3conf/ext/, e.g. in EXT:theme_bootstrap_highland:
 *
 *   @import "../../../../theme_bootstrap/Resources/Private/Dyncss/import.less";
 *
 * In composer mode (default since TYPO3 v12) the extensions are in vendor/<vendor>/<package>/ and the
 * relative path leads nowhere. The first path segment after the "../" is taken as extension key.
 * Parsers call this only after the regular resolution relative to the importing file failed.
 */
final class LegacyExtensionPath
{
    /**
     * @return string|null absolute file name, null if the path is no such import or the file does not exist
     */
    public static function resolve(string $path, array $fileEndings = []): ?string
    {
        if (!preg_match('#^(?:\.\./)+(?:\./)*([a-z0-9_]+)/(.+)$#', $path, $matches)) {
            return null;
        }
        $packagePath = self::getPackagePaths()[$matches[1]] ?? null;
        if ($packagePath === null) {
            return null;
        }
        $file = $packagePath . '/' . $matches[2];
        foreach (['', ...array_map(static fn(string $ending): string => '.' . $ending, $fileEndings)] as $suffix) {
            if (is_file($file . $suffix)) {
                return $file . $suffix;
            }
        }
        return null;
    }

    /**
     * @return array<string, string> extension key => path of the package without trailing slash
     */
    private static function getPackagePaths(): array
    {
        static $paths = null;
        if ($paths === null) {
            $paths = [];
            foreach (GeneralUtility::makeInstance(PackageManager::class)->getActivePackages() as $package) {
                $paths[$package->getPackageKey()] = rtrim($package->getPackagePath(), '/');
            }
        }
        return $paths;
    }
}
