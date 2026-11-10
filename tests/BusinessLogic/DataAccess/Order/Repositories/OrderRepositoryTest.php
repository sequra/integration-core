<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Order\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\Order\Repositories\SeQuraOrderRepository;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MemoryRepositoryWithConditionalDelete;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\Order\Repositories
 */
class OrderRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var SeQuraOrderRepositoryInterface */
    private $seQuraOrderRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(SeQuraOrder::getClassName());
        $this->seQuraOrderRepository = new SeQuraOrderRepository(
            $this->repository
        );

        TestServiceRegister::registerService(SeQuraOrderRepositoryInterface::class, function () {
            return $this->seQuraOrderRepository;
        });
    }

    /**
     * @throws Exception
     */
    public function testDeleteOrders(): void
    {
        // arrange
        $entity = new SeQuraOrder();
        $entity->setCartId('1');
        $entity->setReference('1');
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->seQuraOrderRepository, 'deleteAllOrders']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetByCartIdReturnsLatestOrderOfTheCart(): void
    {
        // arrange
        $this->repository->save($this->order('old-ref', 'cart-1'));
        $this->repository->save($this->order('new-ref', 'cart-1'));
        $this->repository->save($this->order('other-ref', 'cart-2'));

        // act
        $result = $this->seQuraOrderRepository->getByCartId('cart-1');

        // assert
        self::assertNotNull($result);
        self::assertEquals('new-ref', $result->getReference());
    }

    /**
     * @throws Exception
     */
    public function testGetByShopReferenceReturnsLatestOrderOfTheShopOrder(): void
    {
        // arrange
        $this->repository->save($this->order('old-ref', 'cart-1', 'shop-1'));
        $this->repository->save($this->order('new-ref', 'cart-1', 'shop-1'));
        $this->repository->save($this->order('other-ref', 'cart-2', 'shop-2'));

        // act
        $result = $this->seQuraOrderRepository->getByShopReference('shop-1');

        // assert
        self::assertNotNull($result);
        self::assertEquals('new-ref', $result->getReference());
    }

    /**
     * @throws Exception
     */
    public function testSetSeQuraOrderKeepsASingleOrderPerReference(): void
    {
        $this->repository->save($this->order('ref-a', 'cart-1'));
        $this->repository->save($this->order('ref-a', 'cart-1'));
        $this->repository->save($this->order('ref-b', 'cart-1'));

        $this->seQuraOrderRepository->setSeQuraOrder($this->order('ref-a', 'cart-1', 'shop-1'));

        $orders = $this->repository->select((new QueryFilter())->where('reference', Operators::EQUALS, 'ref-a'));
        self::assertCount(1, $orders);
        self::assertEquals('shop-1', $orders[0]->getOrderRef1());
        self::assertNotNull($this->seQuraOrderRepository->getByOrderReference('ref-b'));
    }

    /**
     * @throws Exception
     */
    public function testGetsUnlinkedOrdersAfterTheGivenIdInIdOrder(): void
    {
        $this->repository->save($this->order('ref-1', 'cart-1'));
        $this->repository->save($this->order('ref-2', 'cart-2', 'shop-2'));
        $this->repository->save($this->order('ref-3', 'cart-3'));
        $this->repository->save($this->order('ref-4', 'cart-4'));
        $this->repository->save($this->order('ref-5', 'cart-5'));
        $firstId = (int)$this->seQuraOrderRepository->getByOrderReference('ref-1')->getId();

        $orders = $this->seQuraOrderRepository->getUnlinkedOrdersAfter($firstId, 2);

        self::assertSame(['ref-3', 'ref-4'], array_map(static function (SeQuraOrder $order) {
            return $order->getReference();
        }, $orders));
    }

    /**
     * @throws Exception
     */
    public function testDeletesOnlyTheGivenOrdersThatAreStillUnlinked(): void
    {
        $repository = new MemoryRepositoryWithConditionalDelete();
        $repository->setEntityClass(SeQuraOrder::getClassName());
        $orderRepository = new SeQuraOrderRepository($repository);
        $repository->save($this->order('ref-1', 'cart-1'));
        $repository->save($this->order('ref-2', 'cart-2'));
        $repository->save($this->order('ref-3', 'cart-3'));
        $ids = array_map(static function (string $reference) use ($orderRepository) {
            return (int)$orderRepository->getByOrderReference($reference)->getId();
        }, ['ref-1', 'ref-2']);
        $orderRepository->setSeQuraOrder($this->order('ref-2', 'cart-2', 'shop-2'));

        $orderRepository->deleteUnlinkedOrders($ids);

        self::assertNull($orderRepository->getByOrderReference('ref-1'));
        self::assertNotNull($orderRepository->getByOrderReference('ref-2'));
        self::assertNotNull($orderRepository->getByOrderReference('ref-3'));
    }

    /**
     * @throws Exception
     */
    public function testDeletesNothingWhenTheStorageCannotDeleteByCondition(): void
    {
        $this->repository->save($this->order('ref-1', 'cart-1'));
        $id = (int)$this->seQuraOrderRepository->getByOrderReference('ref-1')->getId();

        $this->seQuraOrderRepository->deleteUnlinkedOrders([$id]);

        self::assertNotNull($this->seQuraOrderRepository->getByOrderReference('ref-1'));
    }

    /**
     * @throws Exception
     */
    public function testDeletesUnlinkedOrdersWithOneConditionalDeletePerCondition(): void
    {
        $repository = $this->createMock(MemoryRepositoryWithConditionalDelete::class);
        $repository->expects(self::never())->method('delete');
        $repository->expects(self::exactly(2))->method('deleteWhere');

        (new SeQuraOrderRepository($repository))->deleteUnlinkedOrders([11, 13]);
    }

    /**
     * @throws Exception
     */
    public function testKeepsAnOrderLinkedSinceItWasReadWithAConditionalDelete(): void
    {
        $repository = new MemoryRepositoryWithConditionalDelete();
        $repository->setEntityClass(SeQuraOrder::getClassName());
        $orderRepository = new SeQuraOrderRepository($repository);
        $repository->save($this->order('ref-1', 'cart-1'));
        $id = (int)$orderRepository->getByOrderReference('ref-1')->getId();
        $orderRepository->setSeQuraOrder($this->order('ref-1', 'cart-1', 'shop-1'));

        $orderRepository->deleteUnlinkedOrders([$id]);

        self::assertNotNull($orderRepository->getByOrderReference('ref-1'));
    }

    /**
     * @param string $reference
     * @param string $cartId
     * @param string $shopReference
     *
     * @return SeQuraOrder
     *
     * @throws Exception
     */
    private function order(string $reference, string $cartId, string $shopReference = ''): SeQuraOrder
    {
        $data = json_decode(
            file_get_contents(__DIR__ . '/../../../Common/MockObjects/SeQuraOrder.json'),
            true
        );

        return SeQuraOrder::fromArray($data['order'])
            ->setReference($reference)
            ->setCartId($cartId)
            ->setOrderRef1($shopReference);
    }
}
