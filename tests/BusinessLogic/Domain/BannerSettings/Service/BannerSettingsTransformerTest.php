<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\BannerSettings\Service;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageTooLargeException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\EmptyBannerParameterException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerInput;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsTransformer;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class BannerSettingsTransformerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\BannerSettings\Service
 */
class BannerSettingsTransformerTest extends BaseTestCase
{
    /**
     * @var BannerSettingsTransformer
     */
    private $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transformer = new BannerSettingsTransformer();
    }

    public function testTransformHappyPath(): void
    {
        $result = $this->transformer->transform([
            [
                'country' => 'ES',
                'linkUrl' => 'https://www.sequra.es/es/faq#shoppers',
                'displayLocation' => 'displayOnHomePage',
                'imageBase64' => 'ES-base64',
            ],
            [
                'country' => 'PT',
                'linkUrl' => 'https://www.sequra.pt/pt/faq#shoppers',
                'displayLocation' => 'displayOnCartPage',
            ],
        ]);

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(BannerInput::class, $result);

        self::assertEquals('ES', $result[0]->getCountry());
        self::assertEquals('https://www.sequra.es/es/faq#shoppers', $result[0]->getLinkUrl());
        self::assertEquals('displayOnHomePage', $result[0]->getDisplayLocation());
        self::assertEquals('ES-base64', $result[0]->getImageBase64());

        self::assertEquals('PT', $result[1]->getCountry());
        self::assertNull($result[1]->getImageBase64());
    }

    /**
     * @throws EmptyBannerParameterException
     */
    public function testTransformEmptyInputProducesEmptyArray(): void
    {
        $result = $this->transformer->transform([]);

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testTransformEmptyImageBase64IsTreatedAsNull(): void
    {
        $result = $this->transformer->transform([
            [
                'country' => 'ES',
                'linkUrl' => 'https://www.sequra.es',
                'displayLocation' => 'displayOnHomePage',
                'imageBase64' => '',
            ],
        ]);

        self::assertNull($result[0]->getImageBase64());
    }

    public function testTransformThrowsOnMissingCountry(): void
    {
        $this->expectException(EmptyBannerParameterException::class);
        $this->expectExceptionMessage("Banner 'country' must not be empty.");

        $this->transformer->transform([
            [
                'linkUrl' => 'https://www.sequra.es',
                'displayLocation' => 'displayOnHomePage',
                'imageBase64' => 'base64',
            ],
        ]);
    }

    public function testTransformThrowsOnMissingLinkUrl(): void
    {
        $this->expectException(EmptyBannerParameterException::class);
        $this->expectExceptionMessage("Banner 'linkUrl' must not be empty.");

        $this->transformer->transform([
            [
                'country' => 'ES',
                'displayLocation' => 'displayOnHomePage',
                'imageBase64' => 'base64',
            ],
        ]);
    }

    public function testTransformThrowsOnMissingDisplayLocation(): void
    {
        $this->expectException(EmptyBannerParameterException::class);
        $this->expectExceptionMessage("Banner 'displayLocation' must not be empty.");

        $this->transformer->transform([
            [
                'country' => 'ES',
                'linkUrl' => 'https://www.sequra.es',
                'imageBase64' => 'base64',
            ],
        ]);
    }

    public function testTransformThrowsWhenImageBase64TooLarge(): void
    {
        $this->expectException(BannerImageTooLargeException::class);

        $oversized = str_repeat('A', BannerSettingsTransformer::MAX_IMAGE_BASE64_LENGTH + 1);

        $this->transformer->transform([
            [
                'country' => 'ES',
                'linkUrl' => 'https://www.sequra.es',
                'displayLocation' => 'displayOnHomePage',
                'imageBase64' => $oversized,
            ],
        ]);
    }
}
