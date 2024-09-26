<?php

namespace KayStrobach\Dyncss\Parser;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo fix type hinting in @param comments
 */
abstract class AbstractParser implements ParserInterface
{
    const SIGNAL_DYNCSS_AFTER_FILE_PARSED = 'afterFileParsed';

    /**
     * @var array
     */
    protected $overrides = [];

    /**
     * @var null|object
     */
    protected $cssParserObject = null;

    /**
     * @var string
     */
    protected $cachePath = 'typo3temp/DynCss/';

    /**
     * @var string
     */
    protected $fileEnding = '';

    /**
     * @var array
     */
    protected $config = [];
    protected $cacheFilename = '';
    protected $inputFilename = '';
    protected $outputFilename = '';

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;


    public function __construct()
    {
        $this->initEmConfiguration();

        // ObjectManager => get SignalSlotDispatcher
        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->signalSlotDispatcher = $objectManager->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    }

    /**
     * @todo add docblock
     */
    protected function initEmConfiguration()
    {
        $this->config = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['dyncss'] ?? [];
    }

    /**
     * returns the parser version, not the extension version.
     *
     * @return string
     */
    public function getVersion()
    {
        return 'unknown';
    }

    /**
     * returns the homepage of the parser.
     *
     * @return string
     */
    public function getParserHomepage()
    {
    }

    /**
     * return readable name of the project.
     *
     * @return string
     */
    public function getParserName()
    {
    }

    /**
     * @param $inputFilename
     * @param $outputFilename
     * @param $cacheFilename
     *
     * @return mixed
     *
     * @todo add typehinting
     */
    abstract protected function _compileFile($inputFilename, $preparedFilename, $outputFilename, $cacheFilename);

    /**
     * @param $string
     * @param null $name
     *
     * @return mixed
     *
     * @todo add typehinting
     */
    abstract protected function _compile($string, $name = null);

    /**
     * @param $string
     *
     * @return mixed
     *
     * @todo add typehinting
     */
    abstract protected function _prepareCompile($string);

    /**
     * should simply return true, if compile is needed, or parser is able to do a cached compile :D.
     *
     * @param $inputFilename
     *
     * @return bool
     *
     * @todo add typehinting
     */
    protected function _checkIfCompileNeeded($inputFilename)
    {
        return false;
    }

    /**
     * Fixes pathes to compliant with original location of the file.
     *
     * @param $string
     *
     * @return mixed
     *
     * @todo add typehinting
     */
    public function _postCompile($string)
    {
        /**
         * $relativePath seems to be unused?
         *
         * @todo missing declaration of inputFilename
         */
        $relativePath = dirname(substr($this->inputFilename, strlen(Environment::getPublicPath() . '/'))).'/';

        /*
         * find all matches of url() and adjust uris
         */
        preg_match_all('|url[\s]*\([\s]*(?<url>[^\)]*)[\s]*\)[\s]*|Ui', $string, $matches, PREG_SET_ORDER);

        if (is_array($matches) && count($matches)) {
            foreach ($matches as $key => $value) {
                // Don't modify inline SVGs
                if (strpos($value['url'], 'data:image') === false) {
                    $url = trim($value[0], '\'"');
                    $orgPath = trim($value['url'], '\'"');
                    $newPath = $this->resolveUrlInCss($orgPath);
                    $string = str_replace($url, 'url("' . $newPath . '")', $string);
                }
            }
        }

        /*
         * find all matches of src= and adjust uris
         */
        preg_match_all('|src=([\'"])(?<url>[^\'"]*)\1|Ui', $string, $matches, PREG_SET_ORDER);

        if (is_array($matches) && count($matches)) {
            foreach ($matches as $key => $value) {
                $url = trim($value['url'], '\'"');
                $newPath = $this->resolveUrlInCss($url);
                $string = str_replace($url, $newPath, $string);
            }
        }

        return $string;
    }

    /**
     * fixes URLs for use in CSS files.
     *
     * @param $url
     *
     * @return string
     *
     * @todo add typehinting
     */
    public function resolveUrlInCss($url)
    {
        if (substr($url, 0, 2) === '//') {
            // double slashed indicate a fully fledged url like //typo3.org
            return $url;
        }
        if (strpos($url, '://') !== false) {
            // http://, ftp:// etc. should not be touched
            return $url;
        }
        if (substr($url, 0, 1) === '/') {
            if (substr($url, 0, strlen(Environment::getPublicPath() . '/')) === Environment::getPublicPath() . '/') {
                return '../../'.substr($url, strlen(Environment::getPublicPath() . '/'));
            }

            return $url;
        }
        if (substr($url, 0, 5) === 'data:') {
            // data:image/svg+xml;base64,... should not be touched
            return $url;
        }
        // anything inside TYPO3 has to be adjusted
        return '../../../../'.dirname($this->removePrefixFromString(Environment::getPublicPath() . '/', $this->inputFilename)).'/'.$url;
    }

    /**
     * removes a prefix from a string.
     *
     * @param $prefix
     * @param $string
     *
     * @return string
     *
     * @todo add typehinting
     */
    public function removePrefixFromString($prefix, $string)
    {
        if (GeneralUtility::isFirstPartOfStr($string, $prefix)) {
            return substr($string, strlen($prefix));
        } else {
            return $string;
        }
    }

    /**
     * @param array $overwrites
     */
    public function setOverrides($overwrites)
    {
        foreach ($overwrites as $key => $overwrite) {
            if (empty($overwrite)) {
                unset($overwrites[$key]);
            }
        }
        $this->overrides = array_replace_recursive($this->overrides, $overwrites);
    }

    /**
     * @param $string
     * @param null $name
     *
     * @return mixed
     *
     * @todo add typehinting
     */
    public function compile($string, $name = null)
    {
        $string = $this->_prepareCompile($string);

        return $this->_compile($string, $name);
    }

    /**
     * @param $inputFilename
     * @param null $outputFilename
     *
     * @return string
     *
     * @todo add typehinting
     */
    public function compileFile($inputFilename, $outputFilename = null)
    {
        if (!$this->prepareEnvironment($inputFilename)) {
            return $inputFilename;
        }
        if ($outputFilename === null) {
            $outputFilename = Environment::getPublicPath() . '/' .$this->cachePath.basename($inputFilename);
        }
        $outputFilenamePathInfo = pathinfo($outputFilename);
        $noExtensionFilename = $outputFilename.'-'.hash('crc32b', $inputFilename).'-'.hash('crc32b', serialize($this->overrides)).'-'.hash('crc32b', filemtime($inputFilename));
        $preparedFilename = $noExtensionFilename.'.'.$outputFilenamePathInfo['extension'];
        $cacheFilename = $noExtensionFilename.'.cache';
        $outputFilename = $noExtensionFilename.'.css';

        $this->inputFilename = $inputFilename;
        $this->outputFilename = $outputFilename;
        $this->cacheFilename = $cacheFilename;

        $applicationContext = Environment::getContext();

        // exit if a precompiled version already exists
        if ((file_exists($outputFilename)) && (!$applicationContext->isDevelopment() && (!$this->config['enableDebugMode']))) {
            return $outputFilename;
        }

        //write intermediate file, if the source has been changed, the rest is done by the cache management
        if (@filemtime($outputFilename) < @filemtime($inputFilename) || $this->_checkIfCompileNeeded($inputFilename) || $applicationContext->isDevelopment()) {
            file_put_contents($preparedFilename, $this->_prepareCompile(file_get_contents($inputFilename)));

            $fileContent = $this->_postCompile($this->_compileFile($inputFilename, $preparedFilename, $outputFilename, $cacheFilename));

            // dispatch signal
            $fileContent = $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_DYNCSS_AFTER_FILE_PARSED, [$fileContent]);

            if ($fileContent !== false) {
                file_put_contents($outputFilename, $fileContent);
                \TYPO3\CMS\Core\Utility\GeneralUtility::fixPermissions($outputFilename);
                // important for some cache clearing scenarios
                if (file_exists($preparedFilename)) {
                    unlink($preparedFilename);
                }
            }
        }

        return $outputFilename;
    }

    /**
     * Ensures, that environment is valid.
     *
     * @param $fname
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function prepareEnvironment($fname)
    {
        GeneralUtility::mkdir_deep(Environment::getPublicPath() . '/typo3temp/DynCss/');
        if (!is_dir(Environment::getPublicPath() . '/' . $this->cachePath)) {
            throw new \Exception('Can´t create cache directory PATH_site/'.$this->cachePath);
        }
        if (!is_file($fname)) {
            return false;
        }
        return true;
    }
}
