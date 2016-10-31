<?php

class Tx_Dyncss_Configuration_BeRegistryTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \KayStrobach\Dyncss\Configuration\BeRegistry
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = new \KayStrobach\Dyncss\Configuration\BeRegistry();
    }

    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function registerFileHandlerMissing()
    {
        $this->assertSame(
            null,
            $this->fixture->getFileHandler('sass')
        );
    }

    /**
     * @test
     */
    public function registerFileHandlerExisting()
    {
        $this->fixture->registerFileHandler('less', 'KayStrobach\Dyncss\Service\DyncssService');

        $this->assertSame(
            'KayStrobach\Dyncss\Service\DyncssService',
            get_class($this->fixture->getFileHandler('less'))
        );
    }

    /**
     * @test
     */
    public function getAllFileHandler()
    {
        $this->fixture->registerFileHandler('less', 'GeneralUtility');

        $this->assertSame(
            'array',
            gettype($this->fixture->getAllFileHandler()),
            'Filetypes is not an array'
        );
    }
}
