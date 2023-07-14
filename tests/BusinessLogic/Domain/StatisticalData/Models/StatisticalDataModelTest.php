<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\StatisticalData\Models;

use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class StatisticalDataModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\StatisticalData\Models
 */
class StatisticalDataModelTest extends BaseTestCase
{
    public function testSettersAndGetters(): void
    {
        $statisticalData = new StatisticalData(true);

        $statisticalData->setSendStatisticalData(false);

        self::assertFalse($statisticalData->isSendStatisticalData());
    }
}
