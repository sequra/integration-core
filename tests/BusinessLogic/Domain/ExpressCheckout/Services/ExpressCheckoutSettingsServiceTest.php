<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Services;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPageConfig;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockExpressCheckoutSettingsRepository;

/**
 * Class ExpressCheckoutSettingsServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Services
 */
class ExpressCheckoutSettingsServiceTest extends TestCase
{
    /**
     * @var MockExpressCheckoutSettingsRepository
     */
    private $repository;

    /**
     * @var ExpressCheckoutSettingsService
     */
    private $service;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new MockExpressCheckoutSettingsRepository();
        $this->service = new ExpressCheckoutSettingsService($this->repository);
    }

    /**
     * @return void
     */
    public function testGetExpressCheckoutSettingsReturnsNullWhenRepositoryEmpty(): void
    {
        self::assertNull($this->service->getExpressCheckoutSettings());
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testGetExpressCheckoutSettingsReturnsValueFromRepository(): void
    {
        $stored = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
        ]);
        $this->repository->setExpressCheckoutSettings($stored);

        self::assertSame($stored, $this->service->getExpressCheckoutSettings());
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testSaveExpressCheckoutSettingsDelegatesToRepository(): void
    {
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false),
        ]);

        $this->service->saveExpressCheckoutSettings($settings);

        self::assertSame($settings, $this->repository->getExpressCheckoutSettings());
    }
}
