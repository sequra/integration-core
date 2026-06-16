<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Affiliate\Services;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Services\AffiliateSettingsService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAffiliateSettingsRepository;

/**
 * Class AffiliateSettingsServiceTest.
 *
 * @package Domain\Affiliate\Services
 */
class AffiliateSettingsServiceTest extends BaseTestCase
{
    /**
     * @var AffiliateSettingsService $service
     */
    private $service;

    /**
     * @var MockAffiliateSettingsRepository $repository
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

        $this->repository = new MockAffiliateSettingsRepository();
        $this->service = new AffiliateSettingsService($this->repository);
    }

    /**
     * @return void
     */
    public function testGetAffiliateSettingsNoSettings(): void
    {
        //Arrange

        //Act
        $result = $this->service->getAffiliateSettings();

        //Assert
        self::assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetAffiliateSettings(): void
    {
        //Arrange
        $affiliateSettings = new AffiliateSettings(true, '1234', 'abc123token');
        $this->repository->setAffiliateSettings($affiliateSettings);

        //Act
        $result = $this->service->getAffiliateSettings();

        //Assert
        self::assertNotNull($result);
        self::assertTrue($result->isEnabled());
        self::assertEquals('1234', $result->getOfferId());
        self::assertEquals('abc123token', $result->getSecurityToken());
    }

    /**
     * @return void
     */
    public function testSetAffiliateSettingsNoSettingsInDB(): void
    {
        //Arrange
        $affiliateSettings = new AffiliateSettings(true, '1234', 'abc123token');

        //Act
        $this->service->setAffiliateSettings($affiliateSettings);

        //Assert
        $result = $this->repository->getAffiliateSettings();
        self::assertNotNull($result);
        self::assertTrue($result->isEnabled());
        self::assertEquals('1234', $result->getOfferId());
        self::assertEquals('abc123token', $result->getSecurityToken());
    }

    /**
     * @return void
     */
    public function testSetAffiliateSettingsSettingsChanged(): void
    {
        //Arrange
        $affiliateSettings = new AffiliateSettings(true, '1234', 'abc123token');
        $this->repository->setAffiliateSettings(new AffiliateSettings(false, '9999', 'oldtoken'));

        //Act
        $this->service->setAffiliateSettings($affiliateSettings);

        //Assert
        $result = $this->repository->getAffiliateSettings();

        self::assertNotNull($result);
        self::assertTrue($result->isEnabled());
        self::assertEquals('1234', $result->getOfferId());
        self::assertEquals('abc123token', $result->getSecurityToken());
    }
}
