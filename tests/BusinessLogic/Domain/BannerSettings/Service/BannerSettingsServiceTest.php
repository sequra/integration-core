<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\BannerSettings\Service;

use Exception;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageRequiredException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockBannerService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockBannerSettingsRepository;

/**
 * Class BannerSettingsServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\BannerSettings\Service
 */
class BannerSettingsServiceTest extends BaseTestCase
{
    /**
     * @var BannerSettingsService $bannerSettingsService
     */
    private $bannerSettingsService;

    /**
     * @var MockBannerSettingsRepository $bannerSettingsRepository
     */
    private $bannerSettingsRepository;

    /**
     * @var MockBannerService $bannerService
     */
    private $bannerService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->bannerSettingsRepository = new MockBannerSettingsRepository();
        $this->bannerService = new MockBannerService();
        $this->bannerSettingsService = new BannerSettingsService(
            $this->bannerSettingsRepository,
            $this->bannerService
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetBannerSettingsNoSettings(): void
    {
        //Arrange

        //Act
        $result = $this->bannerSettingsService->getBannerSettings();

        //Assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetBannerSettings(): void
    {
        //Arrange
        $bannerSettings = new BannerSettings(
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
        $this->bannerSettingsRepository->setBannerSettings($bannerSettings);

        //Act
        $result = $this->bannerSettingsService->getBannerSettings();

        //Assert
        self::assertNotNull($result);
        self::assertCount(2, $result->getBannerConfigs());
        self::assertEquals('PT', $result->getBannerConfigs()[1]->getCountry());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetBannerSettingsNoSettingsInDB(): void
    {
        //Arrange
        $bannerSettings = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'https://www.sequra.com/es/faq#shoppers',
                    '',
                    'displayOnHomePage',
                    'ES-base64'
                ),
                new Banner(
                    'PT',
                    'https://www.sequra.com/it/faq#shoppers',
                    '',
                    'displayOnCartPage',
                    'PT-base64'
                )
            ]
        );

        //Act
        $this->bannerSettingsService->setBannerSettings($bannerSettings);

        //Assert
        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertNotNull($result);
        self::assertCount(2, $result->getBannerConfigs());
        self::assertEquals('PT', $result->getBannerConfigs()[1]->getCountry());
        self::assertEquals(
            'https://shop.test/banners/ES_displayOnHomePage.png',
            $result->getBannerConfigs()[0]->getImageUrl()
        );
        self::assertEquals(
            'https://shop.test/banners/PT_displayOnCartPage.png',
            $result->getBannerConfigs()[1]->getImageUrl()
        );
        self::assertNull($result->getBannerConfigs()[0]->getImageBase64());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetBannerSettingsNewBannerWithoutImageBase64Throws(): void
    {
        //Arrange
        $bannerSettings = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'https://www.sequra.com/es/faq#shoppers',
                    '',
                    'displayOnHomePage'
                ),
            ]
        );

        //Assert
        $this->expectException(BannerImageRequiredException::class);

        //Act
        $this->bannerSettingsService->setBannerSettings($bannerSettings);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetBannerSettingsPreservesImageUrlWhenNoBase64Provided(): void
    {
        //Arrange
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings(
            [
                new Banner(
                    'FR',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/existing.jpg',
                    'displayOnHomePage'
                )
            ]
        ));

        $update = new BannerSettings(
            [
                new Banner(
                    'FR',
                    'https://www.sequra.com/fr/updated-link',
                    '',
                    'displayOnHomePage'
                ),
            ]
        );

        //Act
        $this->bannerSettingsService->setBannerSettings($update);

        //Assert
        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertNotNull($result);
        self::assertCount(1, $result->getBannerConfigs());
        self::assertEquals(
            'https://shop/sequra/fr/existing.jpg',
            $result->getBannerConfigs()[0]->getImageUrl()
        );
        self::assertEquals(
            'https://www.sequra.com/fr/updated-link',
            $result->getBannerConfigs()[0]->getLinkUrl()
        );
        self::assertEmpty($this->bannerService->getStoredImages());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetBannerSettingsReplacesImageWhenBase64Provided(): void
    {
        //Arrange
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings(
            [
                new Banner(
                    'FR',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/old.jpg',
                    'displayOnHomePage'
                )
            ]
        ));

        $update = new BannerSettings(
            [
                new Banner(
                    'FR',
                    'https://www.sequra.com/fr/faq#shoppers',
                    '',
                    'displayOnHomePage',
                    'FR-new-base64'
                ),
            ]
        );

        //Act
        $this->bannerSettingsService->setBannerSettings($update);

        //Assert
        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertEquals(
            'https://shop.test/banners/FR_displayOnHomePage.png',
            $result->getBannerConfigs()[0]->getImageUrl()
        );
        self::assertEquals(['FR|displayOnHomePage' => 'FR-new-base64'], $this->bannerService->getStoredImages());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetBannerSettingsDeletesOmittedBanners(): void
    {
        //Arrange
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings(
            [
                new Banner(
                    'ES',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg',
                    'displayOnHomePage'
                ),
                new Banner(
                    'PT',
                    'https://www.sequra.com/pt/faq#shoppers',
                    'https://shop/sequra/pt/image.jpg',
                    'displayOnCartPage'
                ),
            ]
        ));

        $update = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'https://www.sequra.com/es/faq#shoppers',
                    '',
                    'displayOnHomePage'
                ),
            ]
        );

        //Act
        $this->bannerSettingsService->setBannerSettings($update);

        //Assert
        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertCount(1, $result->getBannerConfigs());
        self::assertEquals('ES', $result->getBannerConfigs()[0]->getCountry());
        self::assertEquals(['PT|displayOnCartPage'], $this->bannerService->getDeletedImageKeys());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetBannerSettingsSettingsChanged(): void
    {
        //Arrange
        $bannerSettings = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'https://www.sequra.com/es/faq#shoppers',
                    '',
                    'displayOnHomePage',
                    'ES-base64'
                ),
                new Banner(
                    'PT',
                    'https://www.sequra.com/it/faq#shoppers',
                    '',
                    'displayOnCartPage',
                    'PT-base64'
                ),
                new Banner(
                    'FR',
                    'https://www.sequra.com/fr/faq#shoppers',
                    '',
                    'displayOnHomePage'
                ),

            ]
        );
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings(
            [
                new Banner(
                    'FR',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/image.jpg',
                    'displayOnHomePage'
                )
            ]
        ));

        //Act
        $this->bannerSettingsService->setBannerSettings($bannerSettings);

        //Assert
        $result = $this->bannerSettingsRepository->getBannerSettings();

        self::assertNotNull($result);
        self::assertCount(3, $result->getBannerConfigs());
        self::assertEquals('ES', $result->getBannerConfigs()[0]->getCountry());
        self::assertEquals('PT', $result->getBannerConfigs()[1]->getCountry());
        self::assertEquals('FR', $result->getBannerConfigs()[2]->getCountry());
        self::assertEquals(
            'https://shop/sequra/fr/image.jpg',
            $result->getBannerConfigs()[2]->getImageUrl()
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetBannerSettingsInvalidURL(): void
    {
        //Arrange
        $bannerSettings = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'link',
                    '',
                    'displayOnHomePage',
                    'ES-base64'
                ),
                new Banner(
                    'PT',
                    'https://www.sequra.com/it/faq#shoppers',
                    '',
                    'displayOnCartPage',
                    'PT-base64'
                )
            ]
        );

        //Assert
        $this->expectException(InvalidURLException::class);

        //Act
        $this->bannerSettingsService->setBannerSettings($bannerSettings);
    }

    /**
     * @throws Exception
     */
    public function testGetBannerDataByCountryAndDisplayLocation(): void
    {
        //Arrange
        $bannerSettings = new BannerSettings(
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
                ),
                new Banner(
                    'FR',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/image.jpg',
                    'displayOnHomePage'
                ),

            ]
        );
        $this->bannerSettingsRepository->setBannerSettings($bannerSettings);

        $country = 'FR';
        $displayLocation = 'displayOnHomePage';

        //Act
        $result = $this->bannerSettingsService->getBannerData($country, $displayLocation);

        //Assert
        self::assertNotNull($result);
        self::assertEquals($country, $result->getCountry());
        self::assertEquals($displayLocation, $result->getDisplayLocation());
    }

    /**
     * @throws Exception
     */
    public function testGetBannerNoDataForDisplayLocation(): void
    {
        //Arrange
        $bannerSettings = new BannerSettings(
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
                ),
                new Banner(
                    'FR',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/image.jpg',
                    'displayOnHomePage'
                ),

            ]
        );
        $this->bannerSettingsRepository->setBannerSettings($bannerSettings);

        $country = 'FR';
        $displayLocation = 'displayOnCartPage';

        //Act
        $result = $this->bannerSettingsService->getBannerData($country, $displayLocation);

        //Assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetBannerNoDataForCountry(): void
    {
        //Arrange
        $bannerSettings = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg',
                    'displayOnHomePage'
                ),
                new Banner(
                    'FR',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/image.jpg',
                    'displayOnHomePage'
                ),

            ]
        );
        $this->bannerSettingsRepository->setBannerSettings($bannerSettings);

        $country = 'PT';
        $displayLocation = 'displayOnCartPage';

        //Act
        $result = $this->bannerSettingsService->getBannerData($country, $displayLocation);

        //Assert
        self::assertNull($result);
    }
}
