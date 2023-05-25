<?php

namespace SeQura\Core\Tests\Infrastructure\ORM;

use SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use SeQura\Core\Infrastructure\ORM\IntermediateObject;
use SeQura\Core\Infrastructure\ORM\Utility\EntityTranslator;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\TaskRunnerStatus;
use SeQura\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;

/**
 * Class EntityTranslatorTest.
 *
 * @package SeQura\Core\Tests\Infrastructure\ORM
 */
class EntityTranslatorTest extends BaseInfrastructureTestWithServices
{
    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \Exception
     */
    public function testTranslate()
    {
        $entity = new QueueItem();
        $entity->setStatus('created');
        $entity->setId(123);
        $entity->setCreateTimestamp(time());
        $entity->setLastUpdateTimestamp(time());
        $entity->setFailTimestamp(time());
        $entity->setFinishTimestamp(time());
        $entity->setPriority(Priority::LOW);

        $intermediate = new IntermediateObject();
        $data = $entity->toArray();
        $data['class_name'] = $entity::getClassName();
        $data = json_encode($data);
        $intermediate->setData($data);

        $translator = new EntityTranslator();
        $translator->init(QueueItem::getClassName());
        $entities = $translator->translate(array($intermediate));

        $this->assertEquals($entity, $entities[0]);
    }

    public function testTranslateWithoutInit()
    {
        $this->expectException(EntityClassException::class);

        $intermediate = new IntermediateObject();
        $translator = new EntityTranslator();
        $translator->translate(array($intermediate));
    }

    public function testInitOnNonEntity()
    {
        $this->expectException(EntityClassException::class);

        $translator = new EntityTranslator();
        $translator->init('\SeQura\Core\Infrastructure\ORM\IntermediateObject');
    }

    public function testTranslateWrongEntity()
    {
        $this->expectException(EntityClassException::class);

        $entity = new TaskRunnerStatus('Test', 123);

        $intermediate = new IntermediateObject();
        $intermediate->setData(Serializer::serialize($entity));

        $translator = new EntityTranslator();
        $translator->init(QueueItem::getClassName());
        $translator->translate(array($intermediate));
    }
}
