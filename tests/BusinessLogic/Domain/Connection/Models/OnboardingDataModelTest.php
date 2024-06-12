<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Connection\Models;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\OnboardingData;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class OnboardingDataModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Connection\Models
 */
class OnboardingDataModelTest extends BaseTestCase
{
    /**
     * @throws InvalidEnvironmentException
     */
    public function testSettersAndGetters(): void
    {
        $onboardingData = new OnboardingData(
            new ConnectionData(
                'live',
                'logeecom',
                new AuthorizationCredentials('username', 'password')
            ),
            true
        );

        $connData = new ConnectionData('live', 'logeecom2', new AuthorizationCredentials('test', 'test'));

        $onboardingData->setConnectionData($connData);
        $onboardingData->setSendStatisticalData(false);

        self::assertEquals($connData, $onboardingData->getConnectionData());
        self::assertFalse($onboardingData->isSendStatisticalData());
    }
}
