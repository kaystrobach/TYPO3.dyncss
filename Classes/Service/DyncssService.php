<?php

namespace KayStrobach\Dyncss\Service;

use KayStrobach\Dyncss\Configuration\BeRegistry;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo missing docblock
 */
class DyncssService
{
    /**
     * @return string path to the compiled file, or to the input file, if not modified
     *
     * @todo add typehinting
     */
    public static function getCompiledFile($inputFile)
    {
        $currentFile = self::fixPathForInput($inputFile);
        $pathInfo = pathinfo($currentFile);
        $parser = BeRegistry::get()->getFileHandler($pathInfo['extension']);
        if ($parser !== null) {
            $parser->setOverrides(self::getOverrides());
            $outputFile = $parser->compileFile($currentFile);

            return self::fixPathForOutput($outputFile);
        } else {
            return $inputFile;
        }
    }

    /**
     * Just makes path absolute.
     *
     * @param $file
     *
     * @return string
     *
     * @todo add typehinting
     */
    protected static function fixPathForInput($file)
    {
        if (empty($file)) {
            throw new \InvalidArgumentException('fixPathForInput needs a valid $file, the given value was empty');
        }
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
            return GeneralUtility::getFileAbsFileName($file);
        }
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend() && !Environment::isCli()) {
            return GeneralUtility::resolveBackPath(Environment::getPublicPath() . '/typo3/' . $file);
        }
        return $file;
    }

    /**
     * Fixes the path for fe or be usage.
     *
     * @param $file
     *
     * @return mixed
     *
     * @todo add typehinting
     */
    protected static function fixPathForOutput($file)
    {
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
            $file = str_replace(Environment::getPublicPath() . '/', '', $file);
        } elseif (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            $file = str_replace(Environment::getPublicPath(), '../', $file);
            if (array_key_exists('BACK_PATH', $GLOBALS)) {
                $file = $GLOBALS['BACK_PATH'].$file;
            }
        }

        return $file;
    }

    /**
     * Gets the overrides (replacements) for the less file as array().
     *
     * @return array
     */
    public static function getOverrides()
    {
        $overrides = [];
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
            if ((array_key_exists('plugin.', $GLOBALS['TSFE']->tmpl->setup))
            && (array_key_exists('tx_dyncss.', $GLOBALS['TSFE']->tmpl->setup['plugin.']))
            && (array_key_exists('overrides.', $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_dyncss.']))) {
                // iterate of cObjects and render them to pass them into the vars
                foreach ($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_dyncss.']['overrides.'] as $varName => $varCObj) {
                    if (substr($varName, -1, 1) !== '.') {
                        $cObj = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
                        $overrides[$varName] = $cObj->cObjGetSingle($varCObj, $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_dyncss.']['overrides.'][$varName.'.']);
                    }
                }
            }
            //
        } elseif (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            /** @var \KayStrobach\Dyncss\Configuration\BeRegistry $configManager */
            $configManager = GeneralUtility::makeInstance('KayStrobach\Dyncss\Configuration\BeRegistry');
            $overrides = $configManager->getAllOverrides();
        }

        return $overrides;
    }
}
