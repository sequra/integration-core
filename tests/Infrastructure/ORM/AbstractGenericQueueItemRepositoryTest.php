<?php

namespace SeQura\Core\Tests\Infrastructure\ORM;

use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\BarTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractGenericTest
 * @package SeQura\Core\Tests\Infrastructure\ORM
 */
abstract class AbstractGenericQueueItemRepositoryTest extends TestCase
{
    protected $queueItemCount = 50;
    protected $fooTasks = 19;

    /**
     * @return string
     */
    abstract public function getQueueItemEntityRepositoryClass();

    /**
     * Cleans up all storage Services used by repositories
     */
    abstract public function cleanUpStorage();

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testRegisteredRepositories()
    {
        $queueItemRepo = RepositoryRegistry::getQueueItemRepository();
        $this->assertInstanceOf(
            QueueItemRepository::class,
            $queueItemRepo,
            'QueueItem repository must be instance of QueueItemRepository'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testQueueItemMassInsert()
    {
        $insertedIds = $this->insertQueueItems();
        foreach ($insertedIds as $id) {
            $this->assertGreaterThan(0, $id);
        }
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function testUpdate()
    {
        $this->insertQueueItems();
        $repository = RepositoryRegistry::getQueueItemRepository();
        $queryFilter = new QueryFilter();
        $queryFilter->where('taskType', '=', 'FooTask');
        /** @var QueueItem $queueItem */
        $queueItem = $repository->selectOne($queryFilter);

        $id = $queueItem->getId();
        $queueItem->setQueueName('Test' . $queueItem->getQueueName());
        $repository->update($queueItem);

        $queryFilter = new QueryFilter();
        $queryFilter->where('queueName', '=', $queueItem->getQueueName());
        $queueItem = $repository->selectOne($queryFilter);
        $this->assertEquals($id, $queueItem->getId());

        $queueItem->setQueueName(substr($queueItem->getQueueName(), 4));
        $repository->update($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testQueryAllQueueItems()
    {
        $this->insertQueueItems();
        $repository = RepositoryRegistry::getQueueItemRepository();

        $this->assertCount($this->queueItemCount, $repository->select());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testQueryWithFiltersString()
    {
        $this->insertQueueItems();
        $repository = RepositoryRegistry::getQueueItemRepository();
        $queryFilter = new QueryFilter();
        $queryFilter->where('taskType', '=', 'FooTask');

        $this->assertCount($this->fooTasks, $repository->select($queryFilter));

        $queryFilter = new QueryFilter();
        $queryFilter->where('taskType', '!=', 'FooTask');
        $this->assertCount($this->queueItemCount - $this->fooTasks, $repository->select($queryFilter));
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testQueryWithFiltersInt()
    {
        $this->insertQueueItems();
        $repository = RepositoryRegistry::getQueueItemRepository();
        $queryFilter = new QueryFilter();
        $queryFilter->where('lastExecutionProgress', '>', 0);

        $this->assertCount(23, $repository->select($queryFilter));

        $queryFilter = new QueryFilter();
        $queryFilter->where('lastExecutionProgress', '<', 10000);

        $this->assertCount(37, $repository->select($queryFilter));

        $queryFilter->where('lastExecutionProgress', '>', 0);
        $this->assertCount(10, $repository->select($queryFilter));
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function testQueryWithFiltersAndSort()
    {
        $this->insertQueueItems();
        $repository = RepositoryRegistry::getQueueItemRepository();
        $queryFilter = new QueryFilter();
        $queryFilter->where('queueTime', '<', DateTime::createFromFormat('Y-m-d', '2017-07-01'));
        $queryFilter->orderBy('queueTime', 'DESC');

        $results = $repository->select($queryFilter);
        $this->assertCount(10, $results);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testQueryWithFiltersAndLimit()
    {
        $this->insertQueueItems();
        $repository = RepositoryRegistry::getQueueItemRepository();
        $queryFilter = new QueryFilter();
        $queryFilter->where('queueTime', '<', DateTime::createFromFormat('Y-m-d', '2017-07-01'));
        $queryFilter->setLimit(5);

        $results = $repository->select($queryFilter);
        $this->assertCount(5, $results);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testFindOldestQueuedItems()
    {
        $this->insertQueueItems();
        $repository = RepositoryRegistry::getQueueItemRepository();

        $this->assertCount(1, $repository->findOldestQueuedItems(Priority::LOW));
        $this->assertCount(1, $repository->findOldestQueuedItems(Priority::NORMAL));
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException
     */
    public function testSaveWithCondition()
    {
        $this->expectException(QueueItemSaveException::class);

        $this->insertQueueItems();
        $repository = RepositoryRegistry::getQueueItemRepository();
        $queryFilter = new QueryFilter();
        $queryFilter->where('lastUpdateTimestamp', '=', 1493851325);

        /** @var QueueItem $item */
        $item = $repository->selectOne($queryFilter);
        $this->assertNotNull($item);

        $item->setLastUpdateTimestamp(99999999);
        $id = $repository->saveWithCondition($item, array('lastUpdateTimestamp' => 1493851325));

        $this->assertEquals($item->getId(), $id);

        $queryFilter = new QueryFilter();
        $queryFilter->where('lastUpdateTimestamp', '=', 99999999);

        /** @var QueueItem $item */
        $item = $repository->selectOne($queryFilter);
        $this->assertNotNull($item);

        $item->setLastUpdateTimestamp(88888888);
        $repository->saveWithCondition($item, array('lastUpdateTimestamp' => 1493851325));
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException
     */
    public function testSaveWithConditionWithNull()
    {
        $this->expectException(QueueItemSaveException::class);

        $this->insertQueueItems();
        $repository = RepositoryRegistry::getQueueItemRepository();
        $queryFilter = new QueryFilter();
        $queryFilter->where('lastUpdateTimestamp', '=', 1518325751);

        /** @var QueueItem $item */
        $item = $repository->selectOne($queryFilter);
        $this->assertNotNull($item);

        $item->setLastUpdateTimestamp(null);

        $id = $repository->saveWithCondition($item, array('status' => 'created', 'lastUpdateTimestamp' => 1518325751));
        $this->assertEquals($item->getId(), $id);

        $id = $repository->saveWithCondition($item, array('status' => 'created', 'lastUpdateTimestamp' => null));
        $this->assertEquals($item->getId(), $id);

        $repository->saveWithCondition($item, array('status' => 'created', 'lastUpdateTimestamp' => 1518325751));
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testInvalidQueryFilter()
    {
        $this->expectException(QueryFilterInvalidParamException::class);

        $repository = RepositoryRegistry::getQueueItemRepository();
        $queryFilter = new QueryFilter();
        $queryFilter->where('progress', '=', 20);

        $repository->select($queryFilter);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        RepositoryRegistry::registerRepository(QueueItem::getClassName(), $this->getQueueItemEntityRepositoryClass());
    }

    protected function tearDown(): void
    {
        $this->cleanUpStorage();
        parent::tearDown();
    }

    /**
     * @return array
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function insertQueueItems()
    {
        $repository = RepositoryRegistry::getQueueItemRepository();
        $ids = array();
        foreach ($this->readQueueItemsFromFile() as $entity) {
            $ids[] = $repository->save($entity);
        }

        return $ids;
    }

    /**
     * Reads test data fixtures about queue items from file
     *
     * @return QueueItem[]
     */
    protected function readQueueItemsFromFile()
    {
        $queueItems = array();
        $json = file_get_contents(__DIR__ . '/../Common/EntityData/QueueItems.json');
        $queueItemsRaw = json_decode($json, true);
        foreach ($queueItemsRaw as $item) {
            if ($item['taskType'] === 'FooTask') {
                $task = new FooTask($item['serializedTask'], $item['progress']);
            } else {
                $task = new BarTask();
            }

            $queueItem = new QueueItem();
            $queueItem->setStatus($item['status']);
            $queueItem->setQueueName($item['queueName']);
            $queueItem->setProgressBasePoints($item['progress']);
            $queueItem->setLastExecutionProgressBasePoints($item['lastExecutionProgress']);
            $queueItem->setRetries($item['retries']);
            $queueItem->setFailureDescription($item['failureDescription']);
            $queueItem->setSerializedTask(Serializer::serialize($task));
            $queueItem->setCreateTimestamp($item['createTimestamp']);
            $queueItem->setQueueTimestamp($item['queueTimestamp']);
            $queueItem->setStartTimestamp($item['startTimestamp']);
            $queueItem->setLastUpdateTimestamp($item['lastUpdateTimestamp']);
            $queueItem->setFinishTimestamp($item['finishTimestamp']);
            $queueItem->setFailTimestamp($item['failTimestamp']);
            $queueItem->setPriority($item['priority']);

            $queueItems[] = $queueItem;
        }

        return $queueItems;
    }
}
