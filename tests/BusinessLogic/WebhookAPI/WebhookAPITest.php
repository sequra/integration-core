<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI;

use SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses\ShopOrderStatusesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\BusinessLogic\WebhookAPI\Response\WebhookErrorResponse;
use SeQura\Core\BusinessLogic\WebhookAPI\WebhookAPI;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockShopOrderStatusesService;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopErrorOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class WebhookAPITest extends BaseTestCase
{
    /**
     * @var TestHttpClient
     */
    public $httpClient;


    /**
     * @return void
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        TestServiceRegister::registerService(ShopOrderStatusesServiceInterface::class, static function () {
            return new MockShopOrderStatusesService();
        });

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
     * @return void
     */
    public function testValidWebhook()
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], '')
        ]);

        $response = WebhookAPI::webhookHandler('1')->handleRequest([
            'cart' => '5678',
            'signature' => 'K6hDNSwfcJjF+suAJqXAjA==',
            'order_ref' => 'd168f9bc-de62-4635-be52-0f0c0a5903aa',
            'approved_since' => '3',
            'product_code' => 'i1',
            'sq_state' => 'approved',
            'order_ref_1' => 'ZXCV1234',
        ]);

        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testInvalidWebhook()
    {
        $response = WebhookAPI::webhookHandler('1')->handleRequest([
            'cart' => '5678',
            'signature' => 'test',
            'order_ref' => 'd168f9bc-de62-4635-be52-0f0c0a5903aa',
            'approved_since' => '3',
            'product_code' => 'i1',
            'sq_state' => 'test',
            'order_ref_1' => 'ZXCV1234',
        ]);

        self::assertInstanceOf(WebhookErrorResponse::class, $response);
    }

    /**
     * @return void
     */
    public function testWebhookUpdateThrowsError()
    {
        ServiceRegister::registerService(ShopOrderService::class, function () {
            return new MockShopErrorOrderService();
        });

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], '')
        ]);

        $response = WebhookAPI::webhookHandler('1')->handleRequest([
            'cart' => '5678',
            'signature' => 'K6hDNSwfcJjF+suAJqXAjA==',
            'order_ref' => 'd168f9bc-de62-4635-be52-0f0c0a5903aa',
            'approved_since' => '3',
            'product_code' => 'i1',
            'sq_state' => 'approved',
            'order_ref_1' => 'ZXCV1234',
        ]);

        self::assertInstanceOf(WebhookErrorResponse::class, $response);
    }
}
