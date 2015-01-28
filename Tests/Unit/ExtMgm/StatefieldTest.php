<?php

class tx_Dyncss_ExtMgm_StatefieldTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var \KayStrobach\Dyncss\ExtMgm\Statefield
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new \KayStrobach\Dyncss\ExtMgm\Statefield();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function mainTest() {
		$this->assertSame(
			'string',
			gettype($this->fixture->main()),
			'got not html string to embed in extension manager'
		);
	}
}