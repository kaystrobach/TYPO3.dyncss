<?php

class tx_Dyncss_Service_DyncssServiceTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var \KayStrobach\Dyncss\Configuration\BeRegistry
	 */
	protected $fixture;

	public function setUp() {
		#$this->fixture = new tx_DynCss_Configuration_BeRegistry;
	}

	public function tearDown() {
		#unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getOverridesIsArray() {
		$this->assertEquals(
			'array',
			gettype(\KayStrobach\Dyncss\Service\DyncssService::getOverrides()),
			'dyncss is not returning an array as override collection'
		);
	}

	/**
	 * @test
	 */
	public function getOverrideForBackend() {
		$value = md5(time());
		$beRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('KayStrobach\Dyncss\Configuration\BeRegistry');
		$beRegistry->setOverride('testValue', $value);
		$this->assertContains(
			$value,
			\KayStrobach\Dyncss\Service\DyncssService::getOverrides(),
			'backend set value is not added to array'
		);
	}

	/**
	 * @test
	 */
	public function getOverrideForFrontend() {
		$this->markTestSkipped(
			'Sry, but can´t test if values are returned for frontend, as test are run in backend ...'
		);
	}

	/**
	 * @test
	 */
	public function getCompiledFileCss() {
		$this->assertEquals(
			'test.css',
			\KayStrobach\Dyncss\Service\DyncssService::getCompiledFile('test.css'),
			'check wether css files are untouched'
		);
	}

	/**
	 * need to register a dummy parser (mock) and check wether several functions are called
	 *
	 * @test
	 */
	public function getCompiledFileParsed() {
		$this->markTestSkipped(
			'not yet implemented'
		);
	}

}