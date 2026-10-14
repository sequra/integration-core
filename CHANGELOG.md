# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

# [v6.0.0](https://github.com/sequra/integration-core/tree/v6.0.0)
## Added
- `ConnectionData` carries the id the SeQura portal gave the store's integration (`getIntegrationId()` / `setIntegrationId()`, a last optional constructor argument, `integrationId` in `toArray()`). Connecting and re-registering webhooks record it, and `ConnectionService::getPortalUrl()` then links to the settings page of the store's own integration instead of the list of integrations. A connection stored before the id was kept, or one whose registration was skipped, has none and still gets the list link. `ConnectionDataRepository::setConnectionData()` keeps the stored id when the model it is given carries none, so saving credentials through `saveConnectionData()` does not take the link away.

## Changed
- **Breaking:** `ConnectionService::connect()` returns the `ConnectionData[]` it connected, leaving out the connections without credentials, instead of `void`. Callers that ignore the return value are unaffected, but an integration that subclasses `ConnectionService` and overrides `connect()` has to declare `: array`, since an override that keeps `: void` fails to load.
- **Breaking:** these constructors take a new required argument, so an integration that subclasses one of them, or builds it itself instead of resolving it through `ServiceRegister`, has to pass the new collaborator. An integration that only uses the classes `BootstrapComponent` registers is unaffected.
  - `ConnectionService`: `DeploymentsRepositoryInterface $deploymentsRepository`, `DeploymentsService $deploymentsService`
  - `CredentialsService`: `SellingCountriesServiceInterface $sellingCountriesService`
  - `CheckoutService`: `CountryConfigurationService $countryConfigurationService`
  - `OrderService`: `CheckoutService $checkoutService`
  - `GeneralSettingsController` and `SaveGeneralSettingsHandler`: `StatisticalDataService $statisticalDataService`
  - `GetGeneralSettingsHandler`: `OrderIdentifiersService $orderIdentifiersService`, `StatisticalDataService $statisticalDataService`
  - `GetGeneralSettingsResponse`: `array $listOfOrderIdentifiers`, `bool $isSendStatisticalData`
- **Breaking:** `CheckoutAPI::get()->solicitation($storeId)->solicitFor()` takes a `SolicitationRequest` instead of a `CreateOrderRequestBuilder`. A caller that still passes a builder gets a `TypeError`, which the API error handling answers as an unsuccessful response, so every solicitation fails until the call is updated. Before soliciting, the core now checks the selling country configuration, the allowed IP addresses and the product and category exclusions of the general settings. A cart that fails these checks is answered with a successful `SolicitationResponse` that has no order and no payment methods, so `getSolicitedOrder()` returns `?SeQuraOrder` and `toArray()` carries `'order' => null`.
- **Breaking:** onboarding no longer stores the merchant's statistical-data consent, which is now configured in the SeQura portal and saved through the configuration webhook:
  - `OnboardingRequest` and `OnboardingData` drop the `bool $sendStatisticalData` constructor argument, and `OnboardingData` drops `isSendStatisticalData()` / `setSendStatisticalData()`. PHP ignores the extra argument, so a host that still passes it gets no error, but the value is no longer stored.
  - `ConnectionController` no longer takes `StatisticalDataService`.
  - `OnboardingDataResponse` takes `?string $portalUrl` and `array $portalUrls` instead of `?StatisticalData`, and its `toArray()` carries `portalUrl` / `portalUrls` instead of `sendStatisticalData`.
  - A connect request in which no connection carries credentials is answered with an unsuccessful `ConnectionValidationResponse` instead of a success.
- **Breaking:** `WidgetSettingsService::setWidgetSettings()` validates the settings before storing them, on every save, Admin UI, configuration webhook and host migrations alike. It throws `EmptyWidgetSelectorParameterException` for a page the widgets are turned on for without a price selector, `DuplicatedWidgetProductException` for a payment method configured more than once, and `InvalidWidgetStylesException` for styles that are not valid JSON. A host that re-saves legacy widget settings, for example in a migration, has to handle the unsuccessful response before deleting what it had stored.
- **Breaking:** the API facade methods (`AdminAPI`, `CheckoutAPI`, the static `WebhookAPI::webhookHandler()` and `ConfigurationWebhookAPI::configurationHandler()`) and `Aspects::beforeEachMethodOfInstance()` / `beforeEachMethodOfService()` declare `: object` instead of `: Aspects`, so IDEs and PHPStan resolve the concrete controller. A subclass that overrides one of them with `: Aspects` fails to load on PHP 7.2 and 7.3, which have no return-type covariance.
- **Breaking:** a webhook with an unknown state is answered with `501 Not Implemented` instead of `400`.
- **Breaking:** `OrderProxyInterface::updateMerchantReference()` and `SeQuraOrderRepositoryInterface::getOrderBatchByCartIds()` are new interface methods. An integration with its own implementation of either interface, test doubles included, has to implement them.
- `StoreIntegrationService::createStoreIntegration()` returns the id of the registered integration as a `string` instead of `void`. Callers ignoring the return value are unaffected, but an integration that subclasses the service and overrides this method has to adopt the new return type: on PHP 8 an override that keeps `void` is a fatal error.
- `GeneralSettingsService::getGeneralSettings()` returns a `GeneralSettings` instead of `?GeneralSettings`: a store that has not stored its general settings yet is answered with the defaults (no order report, no hosted page, empty lists, `P1Y` end date) rather than `null`, with the three service lists derived from the stored credentials either way. `GeneralSettingsResponse::toArray()` and the `get-general-settings` webhook answer therefore carry the defaults for such a store instead of an empty array, and `CheckoutService` judges a virtual product by the merchant's contract whether or not the store has saved its settings. An integration that reads the response to tell a store that saved its settings from one that did not has to ask `GeneralSettingsRepositoryInterface::getGeneralSettings()` instead, which is still `null` until something is saved; one that subclasses the service and overrides this method has to adopt the new return type, since on PHP 8 an override that keeps `?GeneralSettings` is a fatal error.

## Fixed
- A credential without a merchant reference (`ref`) for a country the shop sells in made `ConnectionService::connect()` stop at that deployment on its first connection, leaving its credentials stored without a connection and skipping the deployments after it. That deployment is now left unconnected and the others are still connected; `connect()` then throws the `EmptyCountryConfigurationParameterException`, answered as `general.errors.countries.empty`, and logs a warning naming the deployment. The credentials stored for the failed deployment are replaced on its next connect.
- The portal URLs of the onboarding and connection responses (`portalUrl`, `portalUrls`) were `null` for a deployment stored before deployments carried their portal URL, until another screen happened to refresh the deployments. `ConnectionService::getPortalBaseUrl()` now fetches the deployments from the API, which also stores them, when the connection's deployment is not stored or has no portal URL; if the API cannot be reached it logs a warning and answers `null` as before.
- Saving widget settings with empty `widgetStyles` (or an empty custom widget style) through `AdminAPI::get()->widgetConfiguration($storeId)->setWidgetSettings()` answered `UnsuccessfulJsonResponse`, while the same settings were stored through the `save-widget-settings` configuration webhook. Empty styles are accepted by both now; styles that are not valid JSON are still refused.
- The service lists of the general settings (`enabledForServices`, `allowFirstServicePaymentDelay`, `allowServiceRegistrationItems`) were empty for a store that had connected but not yet saved its general settings, because they were derived only after a stored row was found. They come from the merchant's contract, so a merchant whose account allows services saw no service fields on their products until they saved the general settings once in the portal.

# [v5.8.0](https://github.com/sequra/integration-core/tree/v5.8.0)
## Added
- Payment methods of an already solicited order, from the storefront: `CheckoutAPI::get()->solicitedOrderPaymentMethods($storeId)->getPaymentMethodsInCategories(new PaymentMethodsInCategoriesRequest($orderRef))` answers with the methods SeQura offers for that order, grouped in the categories it returns them in. It depends on `OrderService` alone, so an integration that solicits orders without configuring the checkout library can use it; the methods a merchant offers before an order exists still come from `cachedPaymentMethods()`. The payload carries the categories under `categories` and, because a category with no method is returned as well, a `hasAvailablePaymentMethods` flag telling whether the buyer has anything to choose from.
- `OrderMerchantNotFoundException`, raised for a stored order whose merchant carries no id, so the failure names the order rather than surfacing further down as a missing credentials error with nothing pointing back at it.
- `SEQURA_SKIP_STORE_INTEGRATION_REGISTRATION` env var that, when set to `1`/`true`, skips store integration registration (and therefore webhook registration) during connect, re-registration and the store integration migration task. Honored for sandbox connections only; it is ignored in live mode.

## Changed
- The `$merchantId` argument of `OrderService::getAvailablePaymentMethodsInCategories()` is optional (`?string`, `null` by default). Omitting it reads the merchant off the stored order, which then has to exist; a caller that holds the merchant keeps passing it and is unaffected, and an empty string is still passed through rather than treated as omitted. Existing two argument calls need no change, but an integration that subclasses `OrderService` and overrides this method has to adopt the new signature: on PHP 8 an override that keeps the old one is a fatal error.
- `OrderNotFoundException` and `OrderMerchantNotFoundException` are answered by the API error handling as `404` responses carrying `general.errors.order.notFound` and `general.errors.order.merchantNotFound`, instead of falling through to `general.errors.unknown`. An integration that translates error codes needs the two new labels; one that does not have them renders the English `errorMessage` the response already carries. No endpoint that existed before reaches either exception, so the change is visible only through the new payment methods endpoint.

## Fixed
- Creating an order from a webhook raises `OrderMerchantNotFoundException` when the stored order carries no merchant id, rather than reading the id straight off the order and failing further down in the credentials lookup. The order reference is part of the message, so a webhook that fails this way points back at the order that caused it.
- `QueueItem::setFailureDescription(null)` stores an empty string. The property and `getFailureDescription()` are typed `string`, so a null kept as it came made the getter throw a `TypeError` instead of returning.
- A partial disconnect no longer rewrites the country configuration of a store that has none stored: `getCountryConfiguration()` answering `null` is told apart from an empty list, and the deployment cleanup leaves the record alone instead of saving an empty one over it.

# [v5.6.0](https://github.com/sequra/integration-core/tree/v5.6.0)
## Added
- Affiliate outbound postbacks: an `AffiliateProxy` (under `SeQuraAPI/Affiliate`) that sends the conversion and cancellation postbacks already shaped for their destination and without attaching the connection credentials, plus a `CheckoutAPI` affiliate facade (`affiliate($storeId)->reportConversion(...)` / `->reportCancellation(...)`) that sources the affiliate credentials from the stored `AffiliateSettings` and dispatches only when affiliate marketing is enabled.

# [v5.5.0](https://github.com/sequra/integration-core/tree/v5.5.0)
## Added
- Affiliate configuration support: the `AffiliateSettings` entity and `AffiliateSettingsService`, the `get-affiliate-settings` and `save-affiliate-settings` configuration webhook topics, and connect time provisioning that reads the `affiliate` block from the merchant `configuration_data` and persists it.

# [v1.0.13](https://github.com/sequra/integration-core/tree/v1.0.13)
## Changed
- Added compatibility with PHP8.2.

# [v1.0.12](https://github.com/sequra/integration-core/tree/v1.0.12)
**BREAKING CHANGES**
- The `SeQura\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository` interface is moved and renamed to
`SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface`,
- The `SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusSettings` entity is removed.
- The `SeQura\Core\BusinessLogic\Webhook\Services\StatusMappingService` service is moved and renamed to 
`SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService`

# [v1.0.11](https://github.com/sequra/integration-core/tree/v1.0.11)
**BREAKING CHANGES**
- The `\SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService::updateStatus` method signature is changed.
Now the whole `Webhook` instance is passed as first argument instead of shop order reference. Existing code can get
the shop order id directly from `Webhook` instance.
- The `\SeQura\Core\BusinessLogic\SeQuraAPI\Order\OrderProxy::updateOrderCarts` method has been removed since it is 
the same as `updateOrder` method from the same class. Also 
`\SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\UpdateOrderCartsHttpRequest` has been removed since it is the same 
as `\SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\UpdateOrderHttpRequest`.

# [v1.0.10](https://github.com/sequra/integration-core/tree/v1.0.10)
## Changed
- Added order total calculation in cart items setter.

# [v1.0.9](https://github.com/sequra/integration-core/tree/v1.0.9)
## Changed
- Added optional parameters to the updateStatus method of ShopOrderService interface.

# [v1.0.8](https://github.com/sequra/integration-core/tree/v1.0.8)
## Changed
- Made methods of webhook handler protected.

# [v1.0.7](https://github.com/sequra/integration-core/tree/v1.0.7)
## Changed
- Fixed a duplicated cart bug when transforming order update request.

# [v1.0.6](https://github.com/sequra/integration-core/tree/v1.0.6)
## Changed
- Removed operator reference from merchant DTO.

# [v1.0.5](https://github.com/sequra/integration-core/tree/v1.0.5)
## Changed
- Updated the endpoint URL for updating order carts in proxy.

# [v1.0.4](https://github.com/sequra/integration-core/tree/v1.0.4)
## Changed
- Updated the cart DTO to allow updating order items.

# [v1.0.3](https://github.com/sequra/integration-core/tree/v1.0.3)
## Added
- Added a proxy method for updating carts on the SeQura API

# [v1.0.2](https://github.com/sequra/integration-core/tree/v1.0.2)
## Changed
- Updated webhook handling logic to execute synchronously in order to return an error response in case there are any errors while updating the target shop order status.

# [v1.0.1](https://github.com/sequra/integration-core/tree/v1.0.1)
## Added
- Function to fetch grouped payment methods.

## [v1.0.0](https://github.com/sequra/integration-core/tree/v1.0.0)
- Initial release
