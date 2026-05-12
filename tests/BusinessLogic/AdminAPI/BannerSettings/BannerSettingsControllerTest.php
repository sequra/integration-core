<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\BannerSettings;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests\BannerSettingsRequest;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Banner\BannerServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockBannerService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**+
 * Class BannerSettingsControllerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\BannerSettings
 */
class BannerSettingsControllerTest extends BaseTestCase
{
    /**
     * @var BannerSettingsRepositoryInterface
     */
    private $bannerSettingsRepository;

    /**
     * @var MockBannerService
     */
    private $bannerService;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->bannerSettingsRepository = TestServiceRegister::getService(
            BannerSettingsRepositoryInterface::class
        );

        $this->bannerService = new MockBannerService();
        $this->bannerService->setBannerDisplayLocations([
            'displayOnHomePage',
            'displayOnProductPage',
            'displayOnCartPage',
            'displayOnProductListingPage',
        ]);
        TestServiceRegister::registerService(BannerServiceInterface::class, function () {
            return $this->bannerService;
        });
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetSettings(): void
    {
        // arrange
        $settings = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg',
                    'displayOnHomePage'
                ),
                new Banner(
                    'PT',
                    'https://www.sequra.com/it/faq#shoppers',
                    'https://shop/sequra/pt/image.jpg',
                    'displayOnCartPage'
                )
            ]
        );
        StoreContext::doWithStore('store1', [$this->bannerSettingsRepository, 'setBannerSettings'], [$settings]);

        // act
        $result = AdminAPI::get()->bannerSettings('store1')->getBannerSettings();

        // assert
        self::assertEquals(
            [
                'displayLocations' => [
                    'displayOnHomePage',
                    'displayOnProductPage',
                    'displayOnCartPage',
                    'displayOnProductListingPage',
                ],
                'bannerConfigs' => [
                    [
                        'country' => $settings->getBannerConfigs()[0]->getCountry(),
                        'linkUrl' => $settings->getBannerConfigs()[0]->getLinkUrl(),
                        'imageUrl' => $settings->getBannerConfigs()[0]->getImageUrl(),
                        'displayLocation' => $settings->getBannerConfigs()[0]->getDisplayLocation()
                    ],
                    [
                        'country' => $settings->getBannerConfigs()[1]->getCountry(),
                        'linkUrl' => $settings->getBannerConfigs()[1]->getLinkUrl(),
                        'imageUrl' => $settings->getBannerConfigs()[1]->getImageUrl(),
                        'displayLocation' => $settings->getBannerConfigs()[1]->getDisplayLocation()
                    ]
                ]
            ],
            $result->toArray()
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetSettingsEmpty(): void
    {
        // act
        $result = AdminAPI::get()->bannerSettings('store1')->getBannerSettings();

        // assert
        self::assertEquals(
            [
                'displayLocations' => [
                    'displayOnHomePage',
                    'displayOnProductPage',
                    'displayOnCartPage',
                    'displayOnProductListingPage',
                ],
                'bannerConfigs' => [],
            ],
            $result->toArray()
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettings(): void
    {
        // arrange
        $settings = new BannerSettingsRequest(
            [
                [
                    'country' => 'ES',
                    'displayLocation' => 'displayOnHomePage',
                    'linkUrl' => 'https://www.sequra.com/es/faq#shoppers',
                    'imageBase64' => 'ES-base64'
                ],
                [
                    'country' => 'PT',
                    'displayLocation' => 'displayOnCartPage',
                    'linkUrl' => 'https://www.sequra.com/it/faq#shoppers',
                    'imageBase64' => 'PT-base64'
                ],
            ]
        );

        // act
        $result = AdminAPI::get()->bannerSettings('store1')->setBannerSettings($settings);

        // assert
        self::assertTrue($result->isSuccessful());
        $payload = $result->toArray();
        self::assertEquals(
            [
                'displayOnHomePage',
                'displayOnProductPage',
                'displayOnCartPage',
                'displayOnProductListingPage',
            ],
            $payload['displayLocations']
        );
        self::assertCount(2, $payload['bannerConfigs']);
        self::assertNotSame('', $payload['bannerConfigs'][0]['imageUrl']);
        self::assertNotSame('', $payload['bannerConfigs'][1]['imageUrl']);

        $savedSettings = StoreContext::doWithStore(
            'store1',
            [
                $this->bannerSettingsRepository,
                'getBannerSettings'
            ]
        );
        self::assertNotNull($savedSettings);
        self::assertCount(2, $savedSettings->getBannerConfigs());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettingsInvalidURL(): void
    {
        // arrange
        $settings = new BannerSettingsRequest(
            [
                [
                    'country' => 'ES',
                    'displayLocation' => 'displayOnHomePage',
                    'linkUrl' => 'https://www.sequra.com/es/faq#shoppers',
                    'imageBase64' => 'ES-base64'
                ],
                [
                    'country' => 'PT',
                    'displayLocation' => 'displayOnCartPage',
                    'linkUrl' => 'string',
                    'imageBase64' => 'PT-base64'
                ],
            ]
        );

        // act
        $result = AdminAPI::get()->bannerSettings('store1')->setBannerSettings($settings);

        // assert
        self::assertFalse($result->isSuccessful());
        self::assertEquals([
            'statusCode' => 0,
            'errorCode' => 'general.errors.bannerSettings.invalidUrlFormat',
            'errorMessage' => 'URL format is invalid',
            'errorParameters' => [],
        ], $result->toArray());

        $savedSettings = StoreContext::doWithStore(
            'store1',
            [
                $this->bannerSettingsRepository,
                'getBannerSettings'
            ]
        );
        self::assertNull($savedSettings);
    }
}
