<?php

class tx_Dyncss_Parser_DummyParserTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var tx_Dyncss_Parser_DummyParser
	 */
	protected $fixture;

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
	";

	public function setUp() {
		$this->fixture = new tx_Dyncss_Parser_DummyParser();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function postCompileTest() {
		$urls = array(
			'ftp://typo3.org'  => 'ftp://typo3.org',
			'http://typo3.org' => 'http://typo3.org',
			'/absPath'         => '/absPath',
			'../../Public/Contrib/bootstrap/fonts/glyphicons-halflings-regular.eot' => '../../../../typo3conf/ext/dyncss/Resources/Public/Less/../../Public/Contrib/bootstrap/fonts/glyphicons-halflings-regular.eot',
		);
		$this->fixture->inputFilename = PATH_site . 'typo3conf/ext/dyncss/Resources/Public/Less/someLessFile.less';
		foreach($urls as $key => $url) {
			$this->assertSame($url, $this->fixture->resolveUrlInCss($key), 'Failed with ' . $key);
		}

	}
} 