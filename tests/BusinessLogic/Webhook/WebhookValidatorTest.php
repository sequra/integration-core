<?php

namespace SeQura\Core\Tests\BusinessLogic\Webhook;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidCartException;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidSignatureException;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidStateException;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

class WebhookValidatorTest extends BaseTestCase
{
    /**
     * @return void
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $order = file_get_contents(
            __DIR__ . '/../Common/MockObjects/SeQuraOrder.json'
        );
        $array = json_decode($order, true);

        $seQuraOrder = SeQuraOrder::fromArray($array['order']);
        $seQuraOrder->setReference('d168f9bc-de62-4635-be52-0f0c0a5903aa');
        $seQuraOrder->setCartId('5678');
        $seQuraOrder->setOrderRef1('ZXCV1234');
        $seQuraOrder->setState('approved');

        $repository = RepositoryRegistry::getRepository(SeQuraOrder::getClassName());
        $repository->save($seQuraOrder);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testValidWebhook()
    {
        $validator = new WebhookValidator();
        $validator->validate(Webhook::fromArray([
            'signature' => 'K6hDNSwfcJjF+suAJqXAjA==',
            'order_ref' => 'd168f9bc-de62-4635-be52-0f0c0a5903aa',
            'approved_since' => '3',
            'product_code' => 'i1',
            'sq_state' => 'approved',
            'order_ref_1' => 'ZXCV1234',
        ]));
    }

    /**
     * @return void
     * @throws InvalidCartException
     * @throws InvalidSignatureException
     * @throws InvalidStateException
     * @throws OrderNotFoundException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testOrderNotFound()
    {
        $this->expectException(OrderNotFoundException::class);

        $validator = new WebhookValidator();
        $validator->validate(Webhook::fromArray([
            'signature' => 'K6hDNSwfcJjF+suAJqXAjA==',
            'order_ref' => 'test',
            'approved_since' => '3',
            'product_code' => 'i1',
            'sq_state' => 'approved',
            'order_ref_1' => 'ZXCV1234',
        ]));
    }

    /**
     * @return void
     * @throws InvalidCartException
     * @throws InvalidSignatureException
     * @throws InvalidStateException
     * @throws OrderNotFoundException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testInvalidSignature()
    {
        $this->expectException(InvalidSignatureException::class);

        $validator = new WebhookValidator();
        $validator->validate(Webhook::fromArray([
            'signature' => 'test',
            'order_ref' => 'd168f9bc-de62-4635-be52-0f0c0a5903aa',
            'approved_since' => '3',
            'product_code' => 'i1',
            'sq_state' => 'approved',
            'order_ref_1' => 'ZXCV1234',
        ]));
    }

    /**
     * @return void
     * @throws InvalidCartException
     * @throws InvalidSignatureException
     * @throws InvalidStateException
     * @throws OrderNotFoundException
     * @throws \SeQura\Core\BusinessLogic\WebhookAPI\Exceptions\InvalidWebhookException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testInvalidState()
    {
        $this->expectException(InvalidStateException::class);

        $validator = new WebhookValidator();
        $validator->validate(Webhook::fromArray([
            'signature' => 'K6hDNSwfcJjF+suAJqXAjA==',
            'order_ref' => 'd168f9bc-de62-4635-be52-0f0c0a5903aa',
            'approved_since' => '3',
            'product_code' => 'i1',
            'sq_state' => 'test',
            'order_ref_1' => 'ZXCV1234',
        ]));
    }
}
