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
            [],
            ['ES'],
            ['ES'],
            'P1Y1M'
        );

        $generalSettings->setShowSeQuraCheckoutAsHostedPage(false);
        $generalSettings->setSendOrderReportsPeriodicallyToSeQura(false);
        $generalSettings->setAllowedIPAddresses(['address 4', 'address 5']);
        $generalSettings->setExcludedProducts(['sku 4', 'sku 5']);
        $generalSettings->setExcludedCategories(['3', '4']);
        $generalSettings->setEnabledForServices([]);
        $generalSettings->setAllowFirstServicePaymentDelay(['ES']);
        $generalSettings->setAllowServiceRegistrationItems(['ES']);
        $generalSettings->setDefaultServicesEndDate('P1Y1M');

        self::assertFalse($generalSettings->isShowSeQuraCheckoutAsHostedPage());
        self::assertFalse($generalSettings->isSendOrderReportsPeriodicallyToSeQura());
        self::assertEquals(['address 4', 'address 5'], $generalSettings->getAllowedIPAddresses());
        self::assertEquals(['sku 4', 'sku 5'], $generalSettings->getExcludedProducts());
        self::assertEquals(['3', '4'], $generalSettings->getExcludedCategories());
        self::assertEquals([], $generalSettings->getEnabledForServices());
        self::assertEquals(['ES'], $generalSettings->getAllowFirstServicePaymentDelay());
        self::assertEquals(['ES'], $generalSettings->getAllowServiceRegistrationItems());
        self::assertEquals('P1Y1M', $generalSettings->getDefaultServicesEndDate());
    }
}
