<?php

abstract class tx_Dyncss_Parser_AbstractParser implements tx_Dyncss_Parser_ParserInterface{
	/**
	 * @var array
	 */
	protected $overrides = array();
	/**
	 * @var null|object
	 */
	protected $cssParserObject = null;

	protected $cachePath  = 'typo3temp/Cache/Data/DynCss/';

	protected $fileEnding = '';

	protected $config = array();

	protected function initEmConfiguration() {
		$this->config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dyncss']);
	}
	/**
	 * @param $inputFilename
	 * @param $outputFilename
	 * @param $cacheFilename
	 * @return mixed
	 */
	abstract protected function _compileFile($inputFilename, $preparedFilename, $outputFilename, $cacheFilename);

	/**
	 * @param $string
	 * @param null $name
	 * @return mixed
	 */
	abstract protected function _compile($string, $name = null);

	/**
	 * @param $string
	 * @return mixed
	 */
	abstract protected function _prepareCompile($string);

	/**
	 * should simply return true, if compile is needed, or parser is able to do a cached compile :D
	 *
	 * @param $inputFilename
	 * @return bool
	 */
	protected function _checkIfCompileNeeded($inputFilename) {
		return false;
	}

	/**
	 * Fixes pathes to compliant with original location of the file.
	 *
	 * @param $string
	 * @return mixed
	 */
	public function _postCompile($string) {
		$relativePath = dirname(substr($this->inputFilename, strlen(PATH_site))) . '/';

		preg_match_all('|url[\s]*\([\s]*(?<url>[^\)]*)[\s]*\)[\s]*|Ui', $string, $matches, PREG_SET_ORDER);

		if(is_array($matches) && count($matches)) {
			foreach($matches as $key=>$value) {
				$url     = trim($value['url'], '\'"');
				$newPath = $this->resolveUrlInCss($url);
				$string  = str_replace($url, $newPath, $string);
			}
		}
		return $string;
	}

	/**
	 * fixes URLs for use in CSS files
	 *
	 * @param $url
	 * @return string
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
	 */
	public function removePrefixFromString($prefix, $string) {
		if (t3lib_div::isFirstPartOfStr($string, $prefix)) {
			return substr($string, strlen($prefix));
		} else {
			return $string;
		}
	}

	/**
	 * @param $overrides
	 */
	public function setOverrides($overrides) {
		$this->overrides = t3lib_div::array_merge_recursive_overrule($this->overrides, $overrides);
	}

	/**
	 * @param $string
	 * @param null $name
	 * @return mixed
	 */
	public function compile($string, $name = null) {
		$string = $this->_prepareCompile($string);
		return $this->_compile($string, $name);
	}

	/**
	 * @param $inputFilename
	 * @param null $outputFilename
	 * @return string
	 */
	public function compileFile($inputFilename, $outputFilename = null) {

		if(!$this->prepareEnvironment($inputFilename)) {
			return $inputFilename;
		}
		if($outputFilename === null) {
			$outputFilename = PATH_site . $this->cachePath . basename($inputFilename);
		}
		$outputFilenamePathInfo = pathinfo($outputFilename);
		$noExtensionFilename    = $outputFilename . '-' . hash('crc32b', $inputFilename) . '-' . hash('crc32b', serialize($this->overrides)) . '-' . hash('crc32b', filemtime($inputFilename));
		$preparedFilename       = $noExtensionFilename . '.' . $outputFilenamePathInfo['extension'];
		$cacheFilename          = $noExtensionFilename . '.cache';
		$outputFilename         = $noExtensionFilename . '.css';

		$this->inputFilename  = $inputFilename;
		$this->outputFilename = $outputFilename;
		$this->cacheFilename  = $cacheFilename;

		//write intermediate file, if the source has been changed, the rest is done by the cache management
		if(@filemtime($preparedFilename) < @filemtime($inputFilename) || $this->_checkIfCompileNeeded($inputFilename)) {
			file_put_contents($preparedFilename, $this->_prepareCompile(file_get_contents($inputFilename)));

			$fileContent = $this->_postCompile($this->_compileFile($inputFilename, $preparedFilename, $outputFilename, $cacheFilename));

			if($fileContent !== false) {
				file_put_contents($outputFilename, $fileContent);
			}
		}


		return $outputFilename;

	}

	/**
	 * Ensures, that environment is valid
	 *
	 * @param $fname
	 * @return bool
	 * @throws Exception
	 */
	public function prepareEnvironment($fname) {
		t3lib_div::mkdir_deep(PATH_site . 'typo3temp/', 'Cache/Data/DynCss/');
		if(!is_dir(PATH_site . 'typo3temp/Cache/Data/DynCss/')) {
			throw new Exception('Can´t create cache directory PATH_site/typo3temp/Cache/Data/DynCss/');
		}
		if(!is_file($fname)) {
			return false;
		}
		return true;
	}

}