<?php

namespace BusinessLogic\CheckoutAPI\Banners;

use SeQura\Core\BusinessLogic\CheckoutAPI\Banners\Requests\GetBannerForLocationRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockBannerSettingsService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class BannersApiTest
 *
 * @package BusinessLogic\CheckoutAPI\Banners
 */
class BannersApiTest extends BaseTestCase
{
    /**
     * @var MockBannerSettingsService
     */
    protected $mockBannerSettingsService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockBannerSettingsService = new MockBannerSettingsService(
            TestServiceRegister::getService(BannerSettingsRepositoryInterface::class)
        );

        TestServiceRegister::registerService(
            BannerSettingsService::class,
            function () {
                return $this->mockBannerSettingsService;
            }
        );
    }

    /**
     * @return void
     *
     * @throws InvalidURLException
     */
    public function testGetBannersDataSuccess(): void
    {
        //Arrange
        $this->mockBannerSettingsService->setBannerSettings(
            new BannerSettings(
                [
                    new Banner(
                        'ES',
                        'displayOnHomePage',
                        'https://www.sequra.es/es/faq#shoppers',
                        'https://shop/img/sequra/es/banner/Flag_of_Spain.svg.png'
                    )
                ]
            )
        );

        //Act
        $response = CheckoutAPI::get()->banners('1')
            ->getBannerForLocation(new GetBannerForLocationRequest('ES', 'displayOnHomePage'));

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertNotEmpty($response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidURLException
     */
    public function testGetBannersNoDataSuccess(): void
    {
        //Arrange
        $this->mockBannerSettingsService->setBannerSettings(
            new BannerSettings(
                [
                    new Banner(
                        'ES',
                        'displayOnHomePage',
                        'https://www.sequra.es/es/faq#shoppers',
                        'https://shop/img/sequra/es/banner/Flag_of_Spain.svg.png'
                    )
                ]
            )
        );

        //Act
        $response = CheckoutAPI::get()->banners('1')
            ->getBannerForLocation(new GetBannerForLocationRequest('ES', 'displayOnCartPage'));

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }
}
