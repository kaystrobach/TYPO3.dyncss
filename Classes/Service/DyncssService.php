<?php

namespace KayStrobach\Dyncss\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo missing docblock
 */
class DyncssService
{
    /**
     * @param $inputFile notCompiled Dynamic Css file
     *
     * @return string path to the compiled file, or to the input file, if not modified
     *
     * @todo add typehinting
     */
    public static function getCompiledFile($inputFile)
    {
        $currentFile = self::fixPathForInput($inputFile);
        $pathInfo = pathinfo($currentFile);
        $parser = \KayStrobach\Dyncss\Configuration\BeRegistry::get()->getFileHandler($pathInfo['extension']);
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
        if (TYPO3_MODE === 'FE') {
            $file = GeneralUtility::getFileAbsFileName($file);
        } elseif (TYPO3_MODE === 'BE' && !TYPO3_cliMode) {
            $file = GeneralUtility::resolveBackPath(PATH_typo3.$file);
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
        if (TYPO3_MODE === 'FE') {
            $file = str_replace(PATH_site, '', $file);
        } elseif (TYPO3_MODE === 'BE') {
            $file = str_replace(PATH_site, '../', $file);
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
        if (TYPO3_MODE === 'FE') {
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
        } elseif (TYPO3_MODE === 'BE') {
            /** @var \KayStrobach\Dyncss\Configuration\BeRegistry $configManager */
            $configManager = GeneralUtility::makeInstance('KayStrobach\Dyncss\Configuration\BeRegistry');
            $overrides = $configManager->getAllOverrides();
        }

        return $overrides;
    }
}
