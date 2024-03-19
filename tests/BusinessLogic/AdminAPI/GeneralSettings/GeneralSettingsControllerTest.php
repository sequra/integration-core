<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\GeneralSettings;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\GeneralSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\GeneralSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\ShopCategoriesResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\SuccessfulGeneralSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveCategoriesException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCategoryService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockShopPaymentMethodsService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class GeneralSettingsControllerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\GeneralSettings
 */
class GeneralSettingsControllerTest extends BaseTestCase
{
    /**
     * @var GeneralSettingsRepositoryInterface
     */
    private $generalSettingsRepository;

    public function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(CategoryServiceInterface::class, static function () {
            return new MockCategoryService();
        });

        $this->generalSettingsRepository = TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class);
    }

    /**
     * @throws FailedToRetrieveCategoriesException
     */
    public function testIsGetCategoriesResponseSuccessful(): void
    {
        // Act
        $response = AdminAPI::get()->generalSettings('1')->getShopCategories();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws FailedToRetrieveCategoriesException
     */
    public function testGetCategoryResponse(): void
    {
        // Arrange
        $categories = [
            new Category('1', 'Test 1'),
            new Category('2', 'Test 2'),
            new Category('3', 'Test 3')
        ];

        // Act
        $response = AdminAPI::get()->generalSettings('1')->getShopCategories();
        $expectedResponse = new ShopCategoriesResponse($categories);

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws FailedToRetrieveCategoriesException
     */
    public function testGetCategoriesResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->generalSettings('1')->getShopCategories();

        // Assert
        self::assertEquals($this->expectedCategoriesToArrayResponse(), $response->toArray());
    }

    public function testIsGetGeneralSettingsResponseSuccessful(): void
    {
        // Arrange
        $this->generalSettingsRepository->setGeneralSettings(new GeneralSettings(
            true,
            true,
            ['address 1', 'address 2'],
            ['sku 1', 'sku 2'],
            ['1', '2']
        ));

        // Act
        $response = AdminAPI::get()->generalSettings('1')->getGeneralSettings();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testGetCountryConfigurationResponse(): void
    {
        // Arrange
        $generalSettings = new GeneralSettings(
            true,
            true,
            ['address 1', 'address 2'],
            ['sku 1', 'sku 2'],
            ['1', '2']
        );

        StoreContext::doWithStore('1', [$this->generalSettingsRepository, 'setGeneralSettings'], [$generalSettings]);
        $expectedResponse = new GeneralSettingsResponse($generalSettings);

        // Act
        $response = AdminAPI::get()->generalSettings('1')->getGeneralSettings();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetGeneralSettingsResponseToArray(): void
    {
        // Arrange
        $generalSettings = new GeneralSettings(
            true,
            true,
            ['address 1', 'address 2'],
            ['sku 1', 'sku 2'],
            ['1', '2']
        );

        StoreContext::doWithStore('1', [$this->generalSettingsRepository, 'setGeneralSettings'], [$generalSettings]);

        // Act
        $response = AdminAPI::get()->generalSettings('1')->getGeneralSettings();

        // Assert
        self::assertEquals($this->expectedToArrayResponse(), $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetNonExistingGeneralSettingsResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->generalSettings('1')->getGeneralSettings();

        // Assert
        self::assertEquals([], $response->toArray());
    }

    public function testIsSaveResponseSuccessful(): void
    {
        // Arrange
        $generalSettings = new GeneralSettingsRequest(
            true,
            true,
            ['address 1', 'address 2'],
            ['sku 1', 'sku 2'],
            ['1', '2']
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->saveGeneralSettings($generalSettings);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    public function testSaveResponse(): void
    {
        // Arrange
        $generalSettings = new GeneralSettingsRequest(
            true,
            true,
            ['address 1', 'address 2'],
            ['sku 1', 'sku 2'],
            ['1', '2']
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->saveGeneralSettings($generalSettings);
        $expectedResponse = new SuccessfulGeneralSettingsResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    public function testSaveResponseToArray(): void
    {
        // Arrange
        $generalSettings = new GeneralSettingsRequest(
            true,
            true,
            ['address 1', 'address 2'],
            ['sku 1', 'sku 2'],
            ['1', '2']
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->saveGeneralSettings($generalSettings);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testIsUpdateResponseSuccessful(): void
    {
        // Arrange
        $generalSettings = new GeneralSettings(
            true,
            true,
            ['address 1', 'address 2'],
            ['sku 1', 'sku 2'],
            ['1', '2']
        );

        StoreContext::doWithStore('1', [$this->generalSettingsRepository, 'setGeneralSettings'], [$generalSettings]);

        $generalSettingsRequest = new GeneralSettingsRequest(
            false,
            false,
            ['address 3', 'address 4'],
            ['sku 3', 'sku 4'],
            ['1', '2']
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->saveGeneralSettings($generalSettingsRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testUpdateResponse(): void
    {
        // Arrange
        $generalSettings = new GeneralSettings(
            true,
            true,
            ['address 1', 'address 2'],
            ['sku 1', 'sku 2'],
            ['1', '2']
        );

        StoreContext::doWithStore('1', [$this->generalSettingsRepository, 'setGeneralSettings'], [$generalSettings]);

        $generalSettingsRequest = new GeneralSettingsRequest(
            false,
            false,
            ['address 3', 'address 4'],
            ['sku 3', 'sku 4'],
            ['1', '2']
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->saveGeneralSettings($generalSettingsRequest);
        $expectedResponse = new SuccessfulGeneralSettingsResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testUpdateResponseToArray(): void
    {
        // Arrange
        $generalSettings = new GeneralSettings(
            true,
            true,
            ['address 1', 'address 2'],
            ['sku 1', 'sku 2'],
            ['1', '2']
        );

        StoreContext::doWithStore('1', [$this->generalSettingsRepository, 'setGeneralSettings'], [$generalSettings]);

        $generalSettingsRequest = new GeneralSettingsRequest(
            false,
            false,
            ['address 3', 'address 4'],
            ['sku 3', 'sku 4'],
            ['1', '2']
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->saveGeneralSettings($generalSettingsRequest);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return array
     */
    private function expectedToArrayResponse(): array
    {
        return [
            'sendOrderReportsPeriodicallyToSeQura' => true,
            'showSeQuraCheckoutAsHostedPage' => true,
            'allowedIPAddresses' => ['address 1', 'address 2'],
            'excludedProducts' => ['sku 1', 'sku 2'],
            'excludedCategories' => ['1', '2']
        ];
    }

    private function expectedCategoriesToArrayResponse(): array
    {
        return [
            [
                'id' => '1',
                'name' => 'Test 1',
            ],
            [
                'id' => '2',
                'name' => 'Test 2',
            ],
            [
                'id' => '3',
                'name' => 'Test 3',
            ]
        ];
    }

    private function expectedShopPaymentMethodsToArrayResponse(): array
    {
        return [
            [
                'code' => 'card',
                'name' => 'Credit Card',
            ],
            [
                'code' => 'cash',
                'name' => 'Cash',
            ]
        ];
    }
}
