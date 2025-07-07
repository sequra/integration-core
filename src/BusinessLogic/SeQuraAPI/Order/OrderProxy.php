<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethodCategory;
use SeQura\Core\BusinessLogic\SeQuraAPI\Authorization\AuthorizedProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\AuthorizedProxyFactory;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\CreateOrderHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\GetAvailablePaymentMethodsHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\GetFormHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\AcknowledgeOrderHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\UpdateOrderHttpRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class OrderProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order
 */
class OrderProxy implements OrderProxyInterface
{
    protected const PAYMENT_OPTIONS_KEY = 'payment_options';
    protected const METHODS_KEY = 'methods';

    /**
     * @var AuthorizedProxyFactory $authorizedProxyFactory
     */
    private $authorizedProxyFactory;

    /**
     * @param AuthorizedProxyFactory $authorizedProxyFactory
     */
    public function __construct(AuthorizedProxyFactory $authorizedProxyFactory)
    {
        $this->authorizedProxyFactory = $authorizedProxyFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function getAvailablePaymentMethods(GetAvailablePaymentMethodsRequest $request): array
    {
        $response = $this->authorizedProxyFactory->build($request->getMerchantId())
            ->get(new GetAvailablePaymentMethodsHttpRequest($request))->decodeBodyToArray();

        return $this->getListOfPaymentMethods($response);
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function getAvailablePaymentMethodsInCategories(GetAvailablePaymentMethodsRequest $request): array
    {
        $response = $this->authorizedProxyFactory->build($request->getMerchantId())
            ->get(new GetAvailablePaymentMethodsHttpRequest($request))->decodeBodyToArray();

        return $this->getListOfPaymentMethodsInCategories($response);
    }

    /**
     * @param CreateOrderRequest $request
     *
     * @return SeQuraOrder
     *
     * @throws InvalidCartItemsException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws HttpRequestException
     * @throws DeploymentNotFoundException
     */
    public function createOrder(CreateOrderRequest $request): SeQuraOrder
    {
        $response = $this->authorizedProxyFactory->build($request->getMerchant()->getId())
            ->post(new CreateOrderHttpRequest($request));

        return $request->toSequraOrderInstance($this->getOrderUUID($response->getHeaders()));
    }

    /**
     * @param string $id
     * @param CreateOrderRequest $request
     *
     * @return SeQuraOrder
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws HttpRequestException
     * @throws InvalidCartItemsException
     * @throws DeploymentNotFoundException
     */
    public function acknowledgeOrder(string $id, CreateOrderRequest $request): SeQuraOrder
    {
        $this->authorizedProxyFactory->build($request->getMerchant()->getId())
            ->put(new AcknowledgeOrderHttpRequest($id, $request));

        return $request->toSequraOrderInstance($id);
    }

    /**
     * @param UpdateOrderRequest $request
     *
     * @return bool
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws HttpRequestException
     */
    public function updateOrder(UpdateOrderRequest $request): bool
    {
        return $this->authorizedProxyFactory->build($request->getMerchant()->getId())
            ->put(new UpdateOrderHttpRequest(
                $request->getMerchant()->getId(),
                $request->getMerchantReference()->getOrderRef1(),
                $request
            ))->isSuccessful();
    }

    /**
     * @param GetFormRequest $request
     *
     * @return SeQuraForm
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws HttpRequestException
     */
    public function getForm(GetFormRequest $request): SeQuraForm
    {
        $response = $this->authorizedProxyFactory->build($request->getMerchantId())
            ->get(new GetFormHttpRequest($request));

        return new SeQuraForm($response->getBody());
    }

    /**
     * Retrieves SeQura's order ID from the location header.
     *
     * @param array<string,string> $headers
     *
     * @return string
     */
    protected function getOrderUUID(array $headers): string
    {
        $headers = array_change_key_case($headers);
        $location = array_key_exists('location', $headers) ? $headers['location'] : '';

        return !empty($location) ? basename(parse_url($location, PHP_URL_PATH)) : '';
    }

    /**
     * Gets a list of SeQuraPaymentMethods from the raw response data.
     *
     * @param mixed[] $responseData
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws Exception
     */
    protected function getListOfPaymentMethods(array $responseData): array
    {
        $paymentMethods = [];

        foreach ($responseData[self::PAYMENT_OPTIONS_KEY] as $option) {
            foreach ($option[self::METHODS_KEY] as $method) {
                $method['category'] = $option['category'];
                $paymentMethods[] = SeQuraPaymentMethod::fromArray($method);
            }
        }

        return $paymentMethods;
    }

    /**
     * Gets a list of SeQuraPaymentMethodCategories from the raw response data.
     *
     * @param mixed[] $responseData
     *
     * @return SeQuraPaymentMethodCategory[]
     *
     * @throws Exception
     */
    protected function getListOfPaymentMethodsInCategories(array $responseData): array
    {
        $paymentMethodCategories = [];

        foreach ($responseData[self::PAYMENT_OPTIONS_KEY] as $category) {
            $paymentMethodCategories[] = SeQuraPaymentMethodCategory::fromArray($category);
        }

        return $paymentMethodCategories;
    }
}
