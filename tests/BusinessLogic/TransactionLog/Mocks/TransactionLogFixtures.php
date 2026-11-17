<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks;

use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

trait TransactionLogFixtures
{
    /**
     * @return TransactionLog[]
     */
    private function transactionLogRows(string $storeId): array
    {
        $repository = TestServiceRegister::getService(TransactionLogRepositoryInterface::class);

        return StoreContext::doWithStore($storeId, static function () use ($repository) {
            return $repository->find(100, 0);
        });
    }

    private function storeOrder(string $reference, string $storeId): void
    {
        $array = json_decode(file_get_contents(__DIR__ . '/../../Common/MockObjects/SeQuraOrder.json'), true);
        $order = SeQuraOrder::fromArray($array['order']);
        $order->setReference('sequra-ref-1234');
        $order->setCartId('5678');
        $order->setOrderRef1($reference);
        $order->setState('approved');
        $repository = TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class);
        StoreContext::doWithStore($storeId, [$repository, 'setSeQuraOrder'], [$order]);
    }

    private static function assertOrderFieldsEmpty(TransactionLog $log): void
    {
        foreach (
            [$log->getMerchantReference(), $log->getPaymentMethod(), $log->getSequraLink(),
            $log->getShopLink(), $log->getFailureDescription()] as $value
        ) {
            self::assertSame('', $value);
        }
    }
}
