<?php

namespace SeQura\Core\Tests\Infrastructure\Serializer;

use SeQura\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class JsonSerializerTest
 *
 * @package SeQura\Core\Tests\Infrastructure\Serializer
 */
class JsonSerializerTest extends TestCase
{
    public function setUp(): void
    {
        TestServiceRegister::registerService(
            Serializer::CLASS_NAME,
            function () {
                return new JsonSerializer();
            }
        );
    }

    public function testJsonSerialization()
    {
        $task = new FooTask('test_1', 'test_2');
        /** @var FooTask $serialized */
        $serialized = Serializer::unserialize(Serializer::serialize($task));

        $this->assertInstanceOf(get_class($task), $serialized);
        $this->assertEquals($task->getDependency1(), $serialized->getDependency1());
        $this->assertEquals($task->getDependency2(), $serialized->getDependency2());
        $this->assertEquals($task->getMethodCallCount('execute'), $task->getMethodCallCount('execute'));
    }

    public function testJsonArraySerialize()
    {
        $array = array(1, 2, 3, 4, 5);
        $serialzied = Serializer::unserialize(Serializer::serialize($array));
        $this->assertCount(5, $serialzied);
    }
}
