<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderReport\Listeners;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners\TickEventListener;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TickEventListenerTest
 *
 * @package BusinessLogic\Domain\OrderReport\Listeners
 */
class TickEventListenerTest extends BaseTestCase
{
    /**
     * @var ConnectionDataRepositoryInterface
     */
    public $connectionDataRepository;
    /**
     * @var TestTimeProvider
     */
    public $timeProvider;
    /**
     * @var QueueItemRepository
     */
    public $queueRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionDataRepository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);
        $this->queueRepository = TestRepositoryRegistry::getQueueItemRepository();
        $timeProvider = new TestTimeProvider();
        $this->timeProvider = $timeProvider;
        TestServiceRegister::registerService(
            TimeProvider::class,
            function () use ($timeProvider) {
                return $timeProvider;
            }
        );
        TestServiceRegister::registerService(
            StoreServiceInterface::class,
            function () {
                return new MockStoreService();
            }
        );
    }

    public function testTaskEnqueuedAtCorrectTime()
    {
        // arrange
        $this->timeProvider->setCurrentLocalTime((new \DateTime())->setTime(4, 0));
        $this->setConnectionSettings();

        // act
        TickEventListener::handle();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertNotEmpty($queueItems);
    }

    public function testTaskNotEnqueuedAtIncorrectTime()
    {
        // arrange
        $this->timeProvider->setCurrentLocalTime((new \DateTime())->setTime(16, 0));
        $this->setConnectionSettings();

        // act
        TickEventListener::handle();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertEmpty($queueItems);
    }

    public function testTaskEnqueuedForEveryStore()
    {
        // arrange
        $this->timeProvider->setCurrentLocalTime((new \DateTime())->setTime(4, 0));
        $this->setConnectionSettings();

        // act
        TickEventListener::handle();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertCount(2, $queueItems);
    }

    private function setConnectionSettings()
    {
        $connectionData = new ConnectionData('sandbox', null, new AuthorizationCredentials('test', 'test'));
        $connectionData1 = new ConnectionData('live', null, new AuthorizationCredentials('test1', 'test1'));

        StoreContext::doWithStore('store1', function () use ($connectionData) {
            $this->connectionDataRepository->setConnectionData($connectionData);
        });
        StoreContext::doWithStore('store2', function () use ($connectionData1) {
            $this->connectionDataRepository->setConnectionData($connectionData1);
        });
    }
}
