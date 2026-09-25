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
            [new ConnectionData(
                'live',
                'logeecom',
                'sequra',
                new AuthorizationCredentials('username', 'password')
            )
            ]
        );

        $connData = [
            new ConnectionData('live', 'logeecom2', 'sequra', new AuthorizationCredentials('test', 'test'))
        ];

        $onboardingData->setConnections($connData);

        self::assertEquals($connData, $onboardingData->getConnections());
    }
}
