<?php

class tx_Dyncss_Parser_DummyParserTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
	/**
	 * @var \KayStrobach\Dyncss\Parser\DummyParser
	 */
	protected $fixture;

	protected $urlsToCheck = array();

	/**
	 * @var string
	 */
	protected $buffer = "
		.collapsing {
		  position: relative;
		  height: 0;
		  overflow: hidden;
		  -webkit-transition: height 0.35s ease;
		  transition: height 0.35s ease;
		}
		@font-face {
		  font-family: 'Glyphicons Halflings';
		  src: url('../fonts/glyphicons-halflings-regular.eot');
		  src: url('../fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype'), url('../fonts/glyphicons-halflings-regular.woff') format('woff'), url('../fonts/glyphicons-halflings-regular.ttf') format('truetype'), url('../fonts/glyphicons-halflings-regular.svg#glyphicons_halflingsregular') format('svg');
		}
		.glyphicon {
			position: relative;
			top: 1px;
		  display: inline-block;
		  font-family: 'Glyphicons Halflings';
		  font-style: normal;
		  font-weight: normal;
		  line-height: 1;
		  -webkit-font-smoothing: antialiased;
		  -moz-osx-font-smoothing: grayscale;
		}

		.msfilter {
			-ms-filter: progid:DXImageTransform.Microsoft.AlphaImageLoader( src='../../ContentData/symbole/wissenswertes.png', sizingMethod='scale');
		}
	";

	public function setUp() {
		$this->fixture = new \KayStrobach\Dyncss\Parser\DummyParser();
		$this->urlsToCheck = array(
			'//typo3.org'         =>  '//typo3.org',
			'ftp://typo3.org'     => 'ftp://typo3.org',
			'http://typo3.org'    => 'http://typo3.org',
			'https://typo3.org'   => 'https://typo3.org',
			'/absPath'            => '/absPath',
			'data:suiehihsidgfiu' => 'data:suiehihsidgfiu',
			'../../Public/Contrib/bootstrap/fonts/glyphicons-halflings-regular.eot' => '../../../../typo3conf/ext/dyncss/Resources/Public/Less/../../Public/Contrib/bootstrap/fonts/glyphicons-halflings-regular.eot',
			PATH_site . 'yeah'    => '../../yeah'
		);
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function _postCompile() {
		/** @var \KayStrobach\Dyncss\Parser\DummyParser $mock */
		$mock = $this->getMock(
			'KayStrobach\Dyncss\Parser\DummyParser',
			array(
				'resolveUrlInCss'
			)
		);
		$mock
			->expects($this->exactly(6))
			->method('resolveUrlInCss');
		$mock->_postCompile($this->buffer);
		$this->fixture->_postCompile($this->buffer);

	}

	/**
	 * @test
	 */
	public function resolveUrlInCss() {
		$this->fixture->inputFilename = PATH_site . 'typo3conf/ext/dyncss/Resources/Public/Less/someLessFile.less';
		foreach($this->urlsToCheck as $key => $url) {
			$this->assertSame($url, $this->fixture->resolveUrlInCss($key), 'Failed with ' . $key);
		}

	}
} 