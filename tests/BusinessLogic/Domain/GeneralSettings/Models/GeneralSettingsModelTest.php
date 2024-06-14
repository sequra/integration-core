<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Models;

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
            ['1', '2'],
            false,
            true,
            true,
            'P1Y'
        );

        $generalSettings->setShowSeQuraCheckoutAsHostedPage(false);
        $generalSettings->setSendOrderReportsPeriodicallyToSeQura(false);
        $generalSettings->setAllowedIPAddresses(['address 4', 'address 5']);
        $generalSettings->setExcludedProducts(['sku 4', 'sku 5']);
        $generalSettings->setExcludedCategories(['3', '4']);
        $generalSettings->setEnabledForServices(true);
        $generalSettings->setAllowFirstServicePaymentDelay(false);
        $generalSettings->setAllowServiceRegItems(false);
        $generalSettings->setDefaultServicesEndDate('P2Y');

        self::assertFalse($generalSettings->isShowSeQuraCheckoutAsHostedPage());
        self::assertFalse($generalSettings->isSendOrderReportsPeriodicallyToSeQura());
        self::assertEquals(['address 4', 'address 5'], $generalSettings->getAllowedIPAddresses());
        self::assertEquals(['sku 4', 'sku 5'], $generalSettings->getExcludedProducts());
        self::assertEquals(['3', '4'], $generalSettings->getExcludedCategories());
        self::assertTrue($generalSettings->isEnabledForServices());
        self::assertFalse($generalSettings->isAllowFirstServicePaymentDelay());
        self::assertFalse($generalSettings->isAllowServiceRegItems());
        self::assertEquals('P2Y', $generalSettings->getDefaultServicesEndDate());
    }
}
