<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Models;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class GeneralSettingsModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Models
 */
class GeneralSettingsModelTest extends BaseTestCase
{
    public function testSettersAndGetters(): void
    {
        $generalSettings = new GeneralSettings(
            true,
            true,
            ['address 1', 'address 2', 'address 3'],
            ['sku 1', 'sku 2', 'sku 3'],
            [
                new Category('1', 'name 1'),
                new Category('2', 'name 2'),
                new Category('3', 'name 3')
            ]
        );

        $generalSettings->setShowSeQuraCheckoutAsHostedPage(false);
        $generalSettings->setSendOrderReportsPeriodicallyToSeQura(false);
        $generalSettings->setAllowedIPAddresses(['address 4', 'address 5']);
        $generalSettings->setExcludedProducts(['sku 4', 'sku 5']);
        $generalSettings->setExcludedCategories([
            new Category('4', 'name 4'),
            new Category('5', 'name 5')
        ]);

        self::assertFalse($generalSettings->isShowSeQuraCheckoutAsHostedPage());
        self::assertFalse($generalSettings->isSendOrderReportsPeriodicallyToSeQura());
        self::assertEquals(['address 4', 'address 5'], $generalSettings->getAllowedIPAddresses());
        self::assertEquals(['sku 4', 'sku 5'], $generalSettings->getExcludedProducts());
        self::assertEquals([
            new Category('4', 'name 4'),
            new Category('5', 'name 5')
        ], $generalSettings->getExcludedCategories());
    }
}
