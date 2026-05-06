<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\BannerSettings\Service;

use Exception;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
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
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->bannerSettingsRepository = new MockBannerSettingsRepository();
        $this->bannerSettingsService = new BannerSettingsService($this->bannerSettingsRepository);
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
        //Act
        $this->bannerSettingsService->setBannerSettings($bannerSettings);

        //Assert
        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertNotNull($result);
        self::assertCount(2, $result->getBannerConfigs());
        self::assertEquals('PT', $result->getBannerConfigs()[1]->getCountry());
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
                    'displayOnHomePage',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg'
                ),
                new Banner(
                    'PT',
                    'displayOnCartPage',
                    'https://www.sequra.com/it/faq#shoppers',
                    'https://shop/sequra/pt/image.jpg'
                ),
                new Banner(
                    'FR',
                    'displayOnHomePage',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/image.jpg'
                ),

            ]
        );
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings(
            [
                new Banner(
                    'FR',
                    'displayOnHomePage',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/image.jpg'
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
                    'displayOnHomePage',
                    'link',
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
                    'displayOnHomePage',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg'
                ),
                new Banner(
                    'PT',
                    'displayOnCartPage',
                    'https://www.sequra.com/it/faq#shoppers',
                    'https://shop/sequra/pt/image.jpg'
                ),
                new Banner(
                    'FR',
                    'displayOnHomePage',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/image.jpg'
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
                    'displayOnHomePage',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg'
                ),
                new Banner(
                    'PT',
                    'displayOnCartPage',
                    'https://www.sequra.com/it/faq#shoppers',
                    'https://shop/sequra/pt/image.jpg'
                ),
                new Banner(
                    'FR',
                    'displayOnHomePage',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/image.jpg'
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
                    'displayOnHomePage',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg'
                ),
                new Banner(
                    'FR',
                    'displayOnHomePage',
                    'https://www.sequra.com/fr/faq#shoppers',
                    'https://shop/sequra/fr/image.jpg'
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
