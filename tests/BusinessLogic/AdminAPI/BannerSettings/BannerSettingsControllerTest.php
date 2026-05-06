<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\BannerSettings;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests\BannerSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->bannerSettingsRepository = TestServiceRegister::getService(
            BannerSettingsRepositoryInterface::class
        );
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
                    'displayOnHomePage',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg'
                ),
                new Banner(
                    'PT',
                    'displayOnCartPage',
                    'https://www.sequra.com/it/faq#shoppers',
                    'https://shop/sequra/pt/image.jpg'
                )
            ]
        );
        StoreContext::doWithStore('store1', [$this->bannerSettingsRepository, 'setBannerSettings'], [$settings]);

        // act
        $result = AdminAPI::get()->bannerSettings('store1')->getBannerSettings();

        // assert
        self::assertEquals(
            [
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
    public function testSetSettings(): void
    {
        // arrange
        $settings = new BannerSettingsRequest(
            [
                [
                    'country' => 'ES',
                    'displayLocation' => 'displayOnHomePage',
                    'linkUrl' => 'https://www.sequra.com/es/faq#shoppers',
                    'imageUrl' => 'https://shop/sequra/es/image.jpg'
                ],
                [
                    'country' => 'PT',
                    'displayLocation' => 'displayOnCartPage',
                    'linkUrl' => 'https://www.sequra.com/it/faq#shoppers',
                    'imageUrl' => 'https://shop/sequra/pt/image.jpg'
                ],
            ]
        );

        // act
        AdminAPI::get()->bannerSettings('store1')->setBannerSettings($settings);

        // assert
        $savedSettings = StoreContext::doWithStore(
            'store1',
            [
                $this->bannerSettingsRepository,
                'getBannerSettings'
            ]
        );
        self::assertEquals($settings->transformToDomainModel(), $savedSettings);
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
                    'imageUrl' => 'https://shop/sequra/es/image.jpg'
                ],
                [
                    'country' => 'PT',
                    'displayLocation' => 'displayOnCartPage',
                    'linkUrl' => 'string',
                    'imageUrl' => 'https://shop/sequra/pt/image.jpg'
                ],
            ]
        );

        // act
        $result = AdminAPI::get()->bannerSettings('store1')->setBannerSettings($settings);

        // assert
        self::assertFalse($result->isSuccessful());
        self::assertEquals([
            'errorMessage' => 'URL format is invalid',
            'errorCode' => '400'
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
