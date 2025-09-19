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
            'P1Y1M'
        );

        $generalSettings->setShowSeQuraCheckoutAsHostedPage(false);
        $generalSettings->setSendOrderReportsPeriodicallyToSeQura(false);
        $generalSettings->setAllowedIPAddresses(['address 4', 'address 5']);
        $generalSettings->setExcludedProducts(['sku 4', 'sku 5']);
        $generalSettings->setExcludedCategories(['3', '4']);
        $generalSettings->setEnabledForServices(false);
        $generalSettings->setAllowFirstServicePaymentDelay(true);
        $generalSettings->setAllowServiceRegistrationItems(true);
        $generalSettings->setDefaultServicesEndDate('P1Y1M');

        self::assertFalse($generalSettings->isShowSeQuraCheckoutAsHostedPage());
        self::assertFalse($generalSettings->isSendOrderReportsPeriodicallyToSeQura());
        self::assertEquals(['address 4', 'address 5'], $generalSettings->getAllowedIPAddresses());
        self::assertEquals(['sku 4', 'sku 5'], $generalSettings->getExcludedProducts());
        self::assertEquals(['3', '4'], $generalSettings->getExcludedCategories());
        self::assertFalse($generalSettings->isEnabledForServices());
        self::assertTrue($generalSettings->isAllowFirstServicePaymentDelay());
        self::assertTrue($generalSettings->isAllowServiceRegistrationItems());
        self::assertEquals('P1Y1M', $generalSettings->getDefaultServicesEndDate());
    }
}
