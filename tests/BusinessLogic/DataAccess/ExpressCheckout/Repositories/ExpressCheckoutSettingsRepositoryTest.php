<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\ExpressCheckout\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\ExpressCheckout\Entities\ExpressCheckoutSettings as ExpressCheckoutSettingsEntity;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPageConfig;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts\ExpressCheckoutSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ExpressCheckoutSettingsRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\ExpressCheckout\Repositories
 */
class ExpressCheckoutSettingsRepositoryTest extends BaseTestCase
{
    /**
     * @var ExpressCheckoutSettingsRepositoryInterface
     */
    private $repository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = TestServiceRegister::getService(ExpressCheckoutSettingsRepositoryInterface::class);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetExpressCheckoutSettingsReturnsNullWhenNoEntityStored(): void
    {
        // Act
        $loaded = StoreContext::doWithStore('1', [$this->repository, 'getExpressCheckoutSettings']);

        // Assert
        self::assertNull($loaded);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetExpressCheckoutSettingsInsertsNewEntity(): void
    {
        // Arrange
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
        ]);

        // Act
        StoreContext::doWithStore('1', [$this->repository, 'setExpressCheckoutSettings'], [$settings]);
        $loaded = StoreContext::doWithStore('1', [$this->repository, 'getExpressCheckoutSettings']);

        // Assert
        self::assertNotNull($loaded);
        self::assertCount(1, $loaded->getExpressCheckoutConfigs());
        self::assertTrue($loaded->isPageEnabled(ExpressCheckoutPage::product()->getPage()));
        self::assertCount(1, $this->fetchAllEntitiesForStore('1'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetExpressCheckoutSettingsUpdatesExistingEntity(): void
    {
        // Arrange
        $first = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
        ]);
        $second = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), false),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), true),
        ]);

        // Act
        StoreContext::doWithStore('1', [$this->repository, 'setExpressCheckoutSettings'], [$first]);
        StoreContext::doWithStore('1', [$this->repository, 'setExpressCheckoutSettings'], [$second]);
        $loaded = StoreContext::doWithStore('1', [$this->repository, 'getExpressCheckoutSettings']);

        // Assert
        self::assertCount(1, $this->fetchAllEntitiesForStore('1'));
        self::assertNotNull($loaded);
        self::assertCount(2, $loaded->getExpressCheckoutConfigs());
        self::assertFalse($loaded->isPageEnabled(ExpressCheckoutPage::product()->getPage()));
        self::assertTrue($loaded->isPageEnabled(ExpressCheckoutPage::cart()->getPage()));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetExpressCheckoutSettingsScopesByStore(): void
    {
        // Arrange
        $storeOne = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
        ]);
        $storeTwo = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), true),
        ]);

        // Act
        StoreContext::doWithStore('1', [$this->repository, 'setExpressCheckoutSettings'], [$storeOne]);
        StoreContext::doWithStore('2', [$this->repository, 'setExpressCheckoutSettings'], [$storeTwo]);

        $loadedOne = StoreContext::doWithStore('1', [$this->repository, 'getExpressCheckoutSettings']);
        $loadedTwo = StoreContext::doWithStore('2', [$this->repository, 'getExpressCheckoutSettings']);

        // Assert
        self::assertNotNull($loadedOne);
        self::assertTrue($loadedOne->isPageEnabled(ExpressCheckoutPage::product()->getPage()));
        self::assertFalse($loadedOne->isPageEnabled(ExpressCheckoutPage::cart()->getPage()));
        self::assertNotNull($loadedTwo);
        self::assertFalse($loadedTwo->isPageEnabled(ExpressCheckoutPage::product()->getPage()));
        self::assertTrue($loadedTwo->isPageEnabled(ExpressCheckoutPage::cart()->getPage()));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteExpressCheckoutSettingsRemovesExistingEntity(): void
    {
        // Arrange
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
        ]);
        StoreContext::doWithStore('1', [$this->repository, 'setExpressCheckoutSettings'], [$settings]);

        // Act
        StoreContext::doWithStore('1', [$this->repository, 'deleteExpressCheckoutSettings']);

        // Assert
        $loaded = StoreContext::doWithStore('1', [$this->repository, 'getExpressCheckoutSettings']);
        self::assertNull($loaded);
        self::assertCount(0, $this->fetchAllEntitiesForStore('1'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteExpressCheckoutSettingsIsNoopWhenNoEntityStored(): void
    {
        // Act
        StoreContext::doWithStore('1', [$this->repository, 'deleteExpressCheckoutSettings']);

        // Assert
        $loaded = StoreContext::doWithStore('1', [$this->repository, 'getExpressCheckoutSettings']);
        self::assertNull($loaded);
    }

    /**
     * @param string $storeId
     *
     * @return ExpressCheckoutSettingsEntity[]
     *
     * @throws Exception
     */
    private function fetchAllEntitiesForStore(string $storeId): array
    {
        $repo = TestRepositoryRegistry::getRepository(ExpressCheckoutSettingsEntity::getClassName());
        $rows = $repo->select();

        return array_values(array_filter($rows, static function (ExpressCheckoutSettingsEntity $entity) use ($storeId) {
            return $entity->getStoreId() === $storeId;
        }));
    }
}
