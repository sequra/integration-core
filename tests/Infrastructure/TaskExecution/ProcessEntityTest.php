<?php

namespace SeQura\Core\Tests\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Process;
use SeQura\Core\Infrastructure\TaskExecution\QueueItemStarter;
use SeQura\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;

/**
 * Class ProcessEntityTest.
 *
 * @package SeQura\Core\Tests\Infrastructure\TaskExecution
 */
class ProcessEntityTest extends BaseInfrastructureTestWithServices
{
    public function testToArray()
    {
        $runner = new QueueItemStarter(1234);
        $entity = new Process();
        $entity->setId(1234);
        $entity->setGuid('test');
        $entity->setRunner($runner);

        $data = $entity->toArray();

        self::assertEquals($data['id'], $entity->getId());
        self::assertEquals($data['guid'], $entity->getGuid());
        self::assertEquals($data['runner'], Serializer::serialize($entity->getRunner()));
    }

    public function testFromArrayAndToJSON()
    {
        $runner = new QueueItemStarter(1234);
        $data = array(
            'class_name' => Process::CLASS_NAME,
            'id' => 123,
            'guid' => 'guid',
            'runner' => Serializer::serialize($runner),
        );

        $entity = Process::fromArray($data);

        self::assertEquals($entity->getId(), $data['id']);
        self::assertEquals($entity->getGuid(), $data['guid']);
        self::assertEquals($entity->getRunner(), $runner);

        self::assertEquals(json_encode($data), json_encode($entity->toArray()));
    }

    public function testFromArrayInvalidGuid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $runner = new QueueItemStarter(1234);
        $data = array(
            'id' => 123,
            'runner' => Serializer::serialize($runner),
        );

        Process::fromArray($data);
    }

    public function testFromArrayInvalidRunner()
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = array(
            'id' => 123,
            'guid' => 'test',
        );

        Process::fromArray($data);
    }
}
