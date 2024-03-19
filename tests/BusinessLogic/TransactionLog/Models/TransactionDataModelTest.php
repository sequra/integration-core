<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Models;

use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class TransactionDataModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\TransactionLog\Models
 */
class TransactionDataModelTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $transactionData = new TransactionData('1', 'ship', 123, 'Update order', true);
        $transactionData->setMerchantReference('2');
        $transactionData->setEventCode('refund');
        $transactionData->setTimestamp(456);
        $transactionData->setReason('Refund order');
        $transactionData->setIsSuccessful(false);

        self::assertEquals('2', $transactionData->getMerchantReference());
        self::assertEquals('refund', $transactionData->getEventCode());
        self::assertEquals(456, $transactionData->getTimestamp());
        self::assertEquals('Refund order', $transactionData->getReason());
        self::assertFalse($transactionData->isSuccessful());
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $transactionData = new TransactionData('1', 'ship', 123, 'Update order', true);
        $expected = [
            'merchantReference' => '1',
            'eventCode' => 'ship',
            'timestamp' => 123,
            'reason' => 'Update order',
            'isSuccessful' => true,
        ];

        self::assertEquals($expected, $transactionData->toArray());
    }

    /**
     * @return void
     */
    public function testFromArray(): void
    {
        $transactionDataArray = [
            'merchantReference' => '1',
            'eventCode' => 'ship',
            'timestamp' => 123,
            'reason' => 'Update order',
            'isSuccessful' => true,
        ];

        $expected = new TransactionData('1', 'ship', 123, 'Update order', true);

        self::assertEquals($expected, TransactionData::fromArray($transactionDataArray));
    }
}
