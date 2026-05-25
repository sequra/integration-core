<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\BannerSettings\Service;

use Exception;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageRequiredException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidBannerUrlException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerInput;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockBannerService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockBannerSettingsRepository;
use Throwable;

/**
 * Class BannerSettingsServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\BannerSettings\Service
 */
class BannerSettingsServiceTest extends BaseTestCase
{
    /**
     * @var BannerSettingsService
     */
    private $bannerSettingsService;

    /**
     * @var MockBannerSettingsRepository
     */
    private $bannerSettingsRepository;

    /**
     * @var MockBannerService
     */
    private $bannerService;

    /**
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
     * @throws Exception
     */
    public function testGetBannerSettingsNoSettings(): void
    {
        $result = $this->bannerSettingsService->getBannerSettings();

        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetBannerSettings(): void
    {
        $bannerSettings = new BannerSettings([
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
        ]);
        $this->bannerSettingsRepository->setBannerSettings($bannerSettings);

        $result = $this->bannerSettingsService->getBannerSettings();

        self::assertNotNull($result);
        self::assertCount(2, $result->getBannerConfigs());
        self::assertEquals('PT', $result->getBannerConfigs()[1]->getCountry());
    }

    /**
     * @throws Exception
     */
    public function testSetBannerSettingsNoSettingsInDB(): void
    {
        $inputs = [
            new BannerInput('ES', 'https://www.sequra.com/es/faq#shoppers', 'displayOnHomePage', 'ES-base64'),
            new BannerInput('PT', 'https://www.sequra.com/it/faq#shoppers', 'displayOnCartPage', 'PT-base64'),
        ];

        $this->bannerSettingsService->setBannerSettings($inputs);

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
    }

    /**
     * @throws Exception
     */
    public function testSetBannerSettingsNewBannerWithoutImageBase64Throws(): void
    {
        $inputs = [
            new BannerInput('ES', 'https://www.sequra.com/es/faq#shoppers', 'displayOnHomePage'),
        ];

        $this->expectException(BannerImageRequiredException::class);

        $this->bannerSettingsService->setBannerSettings($inputs);
    }

    /**
     * @throws Exception
     */
    public function testSetBannerSettingsPreservesImageUrlWhenNoBase64Provided(): void
    {
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings([
            new Banner(
                'FR',
                'https://www.sequra.com/fr/faq#shoppers',
                'https://shop/sequra/fr/existing.jpg',
                'displayOnHomePage'
            ),
        ]));

        $update = [
            new BannerInput('FR', 'https://www.sequra.com/fr/updated-link', 'displayOnHomePage'),
        ];

        $this->bannerSettingsService->setBannerSettings($update);

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
     * @throws Exception
     */
    public function testSetBannerSettingsReplacesImageWhenBase64Provided(): void
    {
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings([
            new Banner(
                'FR',
                'https://www.sequra.com/fr/faq#shoppers',
                'https://shop/sequra/fr/old.jpg',
                'displayOnHomePage'
            ),
        ]));

        $update = [
            new BannerInput(
                'FR',
                'https://www.sequra.com/fr/faq#shoppers',
                'displayOnHomePage',
                'FR-new-base64'
            ),
        ];

        $this->bannerSettingsService->setBannerSettings($update);

        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertEquals(
            'https://shop.test/banners/FR_displayOnHomePage.png',
            $result->getBannerConfigs()[0]->getImageUrl()
        );
        self::assertEquals(['FR|displayOnHomePage' => 'FR-new-base64'], $this->bannerService->getStoredImages());
    }

    /**
     * @throws Exception
     */
    public function testSetBannerSettingsRelocatesImageWhenDisplayLocationChangesWithoutBase64(): void
    {
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings([
            new Banner(
                'ES',
                'https://www.sequra.com/es/faq#shoppers',
                'https://shop/sequra/es/existing.jpg',
                'displayOnHomePage'
            ),
        ]));

        $update = [
            new BannerInput('ES', 'https://www.sequra.com/es/faq#shoppers', 'displayOnCartPage'),
        ];

        $this->bannerSettingsService->setBannerSettings($update);

        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertNotNull($result);
        self::assertCount(1, $result->getBannerConfigs());
        self::assertEquals('displayOnCartPage', $result->getBannerConfigs()[0]->getDisplayLocation());
        self::assertEquals(
            'https://shop.test/banners/ES_displayOnCartPage.png',
            $result->getBannerConfigs()[0]->getImageUrl()
        );
        self::assertEquals(
            [
                'ES|displayOnCartPage' => [
                    'country' => 'ES',
                    'from' => 'displayOnHomePage',
                    'to' => 'displayOnCartPage',
                ],
            ],
            $this->bannerService->getMovedImages()
        );
        self::assertEmpty($this->bannerService->getDeletedImageKeys());
    }

    /**
     * @throws Exception
     */
    public function testSetBannerSettingsKeepsImageUrlWhenDisplayLocationUnchanged(): void
    {
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings([
            new Banner(
                'ES',
                'https://www.sequra.com/es/faq#shoppers',
                'https://shop/sequra/es/existing.jpg',
                'displayOnHomePage'
            ),
        ]));

        $update = [
            new BannerInput('ES', 'https://www.sequra.com/es/updated-link', 'displayOnHomePage'),
        ];

        $this->bannerSettingsService->setBannerSettings($update);

        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertEquals(
            'https://shop/sequra/es/existing.jpg',
            $result->getBannerConfigs()[0]->getImageUrl()
        );
        self::assertEmpty($this->bannerService->getMovedImages());
    }

    /**
     * @throws Exception
     */
    public function testSetBannerSettingsDisplayLocationChangeWithBase64DeletesOldImage(): void
    {
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings([
            new Banner(
                'ES',
                'https://www.sequra.com/es/faq#shoppers',
                'https://shop/sequra/es/old.jpg',
                'displayOnHomePage'
            ),
        ]));

        $update = [
            new BannerInput(
                'ES',
                'https://www.sequra.com/es/faq#shoppers',
                'displayOnCartPage',
                'ES-new-base64'
            ),
        ];

        $this->bannerSettingsService->setBannerSettings($update);

        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertEquals('displayOnCartPage', $result->getBannerConfigs()[0]->getDisplayLocation());
        self::assertEquals(
            'https://shop.test/banners/ES_displayOnCartPage.png',
            $result->getBannerConfigs()[0]->getImageUrl()
        );
        self::assertEquals(['ES|displayOnCartPage' => 'ES-new-base64'], $this->bannerService->getStoredImages());
        self::assertEquals(['ES|displayOnHomePage'], $this->bannerService->getDeletedImageKeys());
    }

    /**
     * @throws Exception
     */
    public function testSetBannerSettingsDeletesOmittedBanners(): void
    {
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings([
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
        ]));

        $update = [
            new BannerInput('ES', 'https://www.sequra.com/es/faq#shoppers', 'displayOnHomePage'),
        ];

        $this->bannerSettingsService->setBannerSettings($update);

        $result = $this->bannerSettingsRepository->getBannerSettings();
        self::assertCount(1, $result->getBannerConfigs());
        self::assertEquals('ES', $result->getBannerConfigs()[0]->getCountry());
        self::assertEquals(['PT|displayOnCartPage'], $this->bannerService->getDeletedImageKeys());
    }

    /**
     * @throws Exception
     */
    public function testSetBannerSettingsSettingsChanged(): void
    {
        $this->bannerSettingsRepository->setBannerSettings(new BannerSettings([
            new Banner(
                'FR',
                'https://www.sequra.com/fr/faq#shoppers',
                'https://shop/sequra/fr/image.jpg',
                'displayOnHomePage'
            ),
        ]));

        $inputs = [
            new BannerInput('ES', 'https://www.sequra.com/es/faq#shoppers', 'displayOnHomePage', 'ES-base64'),
            new BannerInput('PT', 'https://www.sequra.com/it/faq#shoppers', 'displayOnCartPage', 'PT-base64'),
            new BannerInput('FR', 'https://www.sequra.com/fr/faq#shoppers', 'displayOnHomePage'),
        ];

        $this->bannerSettingsService->setBannerSettings($inputs);

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
     * @throws Exception
     */
    public function testSetBannerSettingsInvalidURL(): void
    {
        $inputs = [
            new BannerInput('ES', 'link', 'displayOnHomePage', 'ES-base64'),
            new BannerInput('PT', 'https://www.sequra.com/it/faq#shoppers', 'displayOnCartPage', 'PT-base64'),
        ];

        $this->expectException(InvalidBannerUrlException::class);

        $this->bannerSettingsService->setBannerSettings($inputs);
    }

    /**
     * @throws Exception
     */
    public function testGetBannerDataByCountryAndDisplayLocation(): void
    {
        $bannerSettings = new BannerSettings([
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
        ]);
        $this->bannerSettingsRepository->setBannerSettings($bannerSettings);

        $result = $this->bannerSettingsService->getBannerData('FR', 'displayOnHomePage');

        self::assertNotNull($result);
        self::assertEquals('FR', $result->getCountry());
        self::assertEquals('displayOnHomePage', $result->getDisplayLocation());
    }

    /**
     * @throws Exception
     */
    public function testGetBannerNoDataForDisplayLocation(): void
    {
        $bannerSettings = new BannerSettings([
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
        ]);
        $this->bannerSettingsRepository->setBannerSettings($bannerSettings);

        $result = $this->bannerSettingsService->getBannerData('FR', 'displayOnCartPage');

        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetBannerNoDataForCountry(): void
    {
        $bannerSettings = new BannerSettings([
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
        ]);
        $this->bannerSettingsRepository->setBannerSettings($bannerSettings);

        $result = $this->bannerSettingsService->getBannerData('PT', 'displayOnCartPage');

        self::assertNull($result);
    }

    /**
     * @throws Exception|Throwable
     */
    public function testSetBannerSettingsRollsBackFreshUploadsWhenPersistFails(): void
    {
        $failingRepository = $this->createMock(BannerSettingsRepositoryInterface::class);
        $failingRepository->method('getBannerSettings')->willReturn(null);
        $failingRepository->method('setBannerSettings')->willThrowException(new RuntimeException('db down'));

        $bannerService = new MockBannerService();
        $service = new BannerSettingsService($failingRepository, $bannerService);

        $incoming = [
            new BannerInput('ES', 'https://www.sequra.es/es/faq#shoppers', 'displayOnHomePage', 'ES-base64'),
            new BannerInput('PT', 'https://www.sequra.pt/pt/faq#shoppers', 'displayOnCartPage', 'PT-base64'),
        ];

        $thrown = null;
        try {
            $service->setBannerSettings($incoming);
        } catch (RuntimeException $e) {
            $thrown = $e;
        }

        self::assertNotNull($thrown);
        self::assertEquals('db down', $thrown->getMessage());

        $deleted = $bannerService->getDeletedImageKeys();
        sort($deleted);
        self::assertEquals(['ES|displayOnHomePage', 'PT|displayOnCartPage'], $deleted);
        self::assertEmpty($bannerService->getStoredImages());
    }
}
