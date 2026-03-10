<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\AdvancedSettings\Services;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services\AdvancedSettingsService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAdvancedSettingsRepository;

/**
 * Class AdvancedSettingsServiceTest.
 *
 * @package Domain\AdvancedSettings\Services
 */
class AdvancedSettingsServiceTest extends BaseTestCase
{
    /**
     * @var AdvancedSettingsService $service
     */
    private $service;

    /**
     * @var MockAdvancedSettingsRepository $repository
     */
    private $repository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MockAdvancedSettingsRepository();
        $this->service = new AdvancedSettingsService($this->repository);
    }

    /**
     * @return void
     */
    public function testGetAdvancedSettingsNoSettings(): void
    {
        //Arrange

        //Act
        $result = $this->service->getAdvancedSettings();

        //Assert
        self::assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetAdvancedSettings(): void
    {
        //Arrange
        $advancedSettings = new AdvancedSettings(true, 2);
        $this->repository->setAdvancedSettings($advancedSettings);

        //Act
        $result = $this->service->getAdvancedSettings();

        //Assert
        self::assertNotNull($result);
        self::assertTrue($result->isEnabled());
        self::assertEquals(2, $result->getLevel());
    }

    /**
     * @return void
     */
    public function testSetAdvancedSettingsNoSettingsInDB(): void
    {
        //Arrange
        $advancedSettings = new AdvancedSettings(true, 2);

        //Act
        $this->service->setAdvancedSettings($advancedSettings);

        //Assert
        $result = $this->repository->getAdvancedSettings();
        self::assertNotNull($result);
        self::assertTrue($result->isEnabled());
        self::assertEquals(2, $result->getLevel());
    }

    /**
     * @return void
     */
    public function testSetAdvancedSettingsSettingsChanged(): void
    {
        //Arrange
        $advancedSettings = new AdvancedSettings(true, 2);
        $this->repository->setAdvancedSettings(new AdvancedSettings(false, 1));

        //Act
        $this->service->setAdvancedSettings($advancedSettings);

        //Assert
        $result = $this->repository->getAdvancedSettings();

        self::assertNotNull($result);
        self::assertTrue($result->isEnabled());
        self::assertEquals(2, $result->getLevel());
    }
}
