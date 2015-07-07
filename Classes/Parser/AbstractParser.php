<?php

namespace KayStrobach\Dyncss\Parser;
use KayStrobach\Dyncss\Utilities\ApplicationContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

/**
 * @todo fix type hinting in @param comments
 */
abstract class AbstractParser implements ParserInterface{

	/**
	 * @var array
	 */
	protected $overrides = array();

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
	 * @var array $config
	 */
	protected $config = array();

	public function __construct() {
		$this->initEmConfiguration();
	}

	/**
	 * @todo add docblock
	 */
	protected function initEmConfiguration() {
		$this->config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dyncss']);
	}

	public function getVersion() {
		return 'unknown';
	}

	/**
	 * @param $inputFilename
	 * @param $outputFilename
	 * @param $cacheFilename
	 * @return mixed
	 *
	 * @todo add typehinting
	 */
	abstract protected function _compileFile($inputFilename, $preparedFilename, $outputFilename, $cacheFilename);

	/**
	 * @param $string
	 * @param null $name
	 * @return mixed
	 *
	 * @todo add typehinting
	 */
	abstract protected function _compile($string, $name = null);

	/**
	 * @param $string
	 * @return mixed
	 *
	 * @todo add typehinting
	 */
	abstract protected function _prepareCompile($string);

	/**
	 * should simply return true, if compile is needed, or parser is able to do a cached compile :D
	 *
	 * @param $inputFilename
	 * @return bool
	 *
	 * @todo add typehinting
	 */
	protected function _checkIfCompileNeeded($inputFilename) {
		return false;
	}

	/**
	 * Fixes pathes to compliant with original location of the file.
	 *
	 * @param $string
	 * @return mixed
	 *
	 * @todo add typehinting
	 */
	public function _postCompile($string) {
		/**
		 * $relativePath seems to be unused?
		 * @todo missing declaration of inputFilename
		 */
		$relativePath = dirname(substr($this->inputFilename, strlen(PATH_site))) . '/';

		/**
		 * @todo missing declaration of $matches
		 */
		preg_match_all('|url[\s]*\([\s]*(?<url>[^\)]*)[\s]*\)[\s]*|Ui', $string, $matches, PREG_SET_ORDER);

		if(is_array($matches) && count($matches)) {
			foreach($matches as $key => $value) {
				$url = trim($value['url'], '\'"');
				$newPath = $this->resolveUrlInCss($url);
				$string = str_replace($url, $newPath, $string);
			}
		}
		return $string;
	}

	/**
	 * fixes URLs for use in CSS files
	 *
	 * @param $url
	 * @return string
	 *
	 * @todo add typehinting
	 */
	public function resolveUrlInCss($url) {
		if(strpos($url, '://') !== FALSE) {
			// http://, ftp:// etc. urls leave untouched
			return $url;
		} elseif(substr($url, 0, 1) === '/') {
			// absolute path, should not be touched
			return $url;
		} else {
			// anything inside TYPO3 has to be adjusted
			return '../../../../' . dirname($this->removePrefixFromString(PATH_site, $this->inputFilename)) . '/' . $url;
		}
	}

	/**
	 * removes a prefix from a string
	 *
	 * @param $prefix
	 * @param $string
	 * @return string
	 *
	 * @todo add typehinting
	 */
	public function removePrefixFromString($prefix, $string) {
		if (GeneralUtility::isFirstPartOfStr($string, $prefix)) {
			return substr($string, strlen($prefix));
		} else {
			return $string;
		}
	}

	/**
	 * @param array $overwrites
	 *
	 */
	public function setOverrides($overwrites) {
		foreach($overwrites as $key => $overwrite) {
			if(empty($overwrite)) {
				unset($overwrites[$key]);
			}
		}
		$this->overrides = ArrayUtility::arrayMergeRecursiveOverrule($this->overrides, $overwrites);
	}

	/**
	 * @param $string
	 * @param null $name
	 * @return mixed
	 *
	 * @todo add typehinting
	 */
	public function compile($string, $name = null) {
		$string = $this->_prepareCompile($string);
		return $this->_compile($string, $name);
	}

	/**
	 * @param $inputFilename
	 * @param null $outputFilename
	 * @return string
	 *
	 * @todo add typehinting
	 */
	public function compileFile($inputFilename, $outputFilename = null) {

		if(!$this->prepareEnvironment($inputFilename)) {
			return $inputFilename;
		}
		if($outputFilename === null) {
			$outputFilename = PATH_site . $this->cachePath . basename($inputFilename);
		}
		$outputFilenamePathInfo = pathinfo($outputFilename);
		$noExtensionFilename = $outputFilename . '-' . hash('crc32b', $inputFilename) . '-' . hash('crc32b', serialize($this->overrides)) . '-' . hash('crc32b', filemtime($inputFilename));
		$preparedFilename = $noExtensionFilename . '.' . $outputFilenamePathInfo['extension'];
		$cacheFilename = $noExtensionFilename . '.cache';
		$outputFilename = $noExtensionFilename . '.css';

		$this->inputFilename = $inputFilename;
		$this->outputFilename = $outputFilename;
		$this->cacheFilename = $cacheFilename;
		
		// exit if a precompiled version already exists
		if ((file_exists($outputFilename)) && (!ApplicationContext::isDevelopmentModeActive() && (!$this->config['enableDebugMode']))) {
			return $outputFilename;
		}

		//write intermediate file, if the source has been changed, the rest is done by the cache management
		if(@filemtime($preparedFilename) < @filemtime($inputFilename) || $this->_checkIfCompileNeeded($inputFilename)) {
			file_put_contents($preparedFilename, $this->_prepareCompile(file_get_contents($inputFilename)));

			$fileContent = $this->_postCompile($this->_compileFile($inputFilename, $preparedFilename, $outputFilename, $cacheFilename));

			if($fileContent !== false) {
				file_put_contents($outputFilename, $fileContent);
				// important for some cache clearing scenarios
				if(file_exists($preparedFilename)) {
					unlink($preparedFilename);
				}
			}
		}

		return $outputFilename;
	}

	/**
	 * Ensures, that environment is valid
	 *
	 * @param $fname
	 * @return bool
	 * @throws \Exception
	 */
	public function prepareEnvironment($fname) {
		GeneralUtility::mkdir_deep(PATH_site . 'typo3temp/', 'DynCss/');
		if(!is_dir(PATH_site . $this->cachePath)) {
			throw new \Exception('Can´t create cache directory PATH_site/' . $this->cachePath);
		}
		if(!is_file($fname)) {
			return false;
		}
		return true;
	}
}
