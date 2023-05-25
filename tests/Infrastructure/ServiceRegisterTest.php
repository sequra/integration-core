<?php

namespace SeQura\Core\Tests\Infrastructure;

use SeQura\Core\Infrastructure\Exceptions\ServiceNotRegisteredException;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

class ServiceRegisterTest extends TestCase
{
    /**
     * Test simple registering the service and getting the instance back
     *
     * @throws \InvalidArgumentException
     */
    public function testGetInstance()
    {
        $service = ServiceRegister::getInstance();

        $this->assertInstanceOf(
            '\SeQura\Core\Infrastructure\ServiceRegister',
            $service,
            'Failed to retrieve registered instance of interface.'
        );
    }

    /**
     * Test simple registering the service and getting the instance back
     *
     */
    public function testSimpleRegisterAndGet()
    {
        new TestServiceRegister(
            array(
                TestService::CLASS_NAME => function () {
                    return new TestService('first');
                },
            )
        );

        $result = ServiceRegister::getService(TestService::CLASS_NAME);

        $this->assertInstanceOf(
            TestService::CLASS_NAME,
            $result,
            'Failed to retrieve registered instance of interface.'
        );
    }

    /**
     * Test simple registering the service via static call and getting the instance back
     */
    public function testStaticSimpleRegisterAndGet()
    {
        ServiceRegister::registerService(
            'test 2',
            function () {
                return new TestService('first');
            }
        );

        $result = ServiceRegister::getService(TestService::CLASS_NAME);

        $this->assertInstanceOf(
            TestService::CLASS_NAME,
            $result,
            'Failed to retrieve registered instance of interface.'
        );
    }

    /**
     * Test throwing exception when service is not registered.
     */
    public function testGettingServiceWhenItIsNotRegistered()
    {
        $this->expectException(ServiceNotRegisteredException::class);
        ServiceRegister::getService('SomeService');
    }

    /**
     * Test throwing exception when trying to register service with non callable delegate
     */
    public function testRegisteringServiceWhenDelegateIsNotCallable()
    {
        $this->expectException(\InvalidArgumentException::class);
        new TestServiceRegister(
            array(
                TestService::CLASS_NAME => 'Some non callable string',
            )
        );
    }
}
