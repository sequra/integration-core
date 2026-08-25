# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

# [Unreleased]
## Fixed
- The HTTP logger no longer writes the value of a header that authenticates the caller: `Authorization`, `Proxy-Authorization`, `Cookie` and `Set-Cookie` are logged with their name and `***` in place of the value.

## Changed
- The four API facades (`AdminAPI`, `CheckoutAPI`, `WebhookAPI`, `ConfigurationWebhookAPI`) are typed for static analysis: `Aspects::beforeEachMethodOfInstance()` / `beforeEachMethodOfService()` are generic over their subject and every facade method documents the controller it returns, so a chained call (`AdminAPI::get()->connection($storeId)->connect($request)`) type-checks without a cast or a PHPStan ignore. Runtime behaviour is unchanged: the returned object is still the `Aspects` proxy that applies error handling and the store context around every call.

## Added
- Widget settings are validated before they are stored, by the domain service, so both the admin API and the configuration webhook refuse a configuration the widgets cannot be displayed with.
- General settings carry the order identifier the integration sends to SeQura as the primary order reference: `orderIdentifier` is stored on `GeneralSettings` and read from the `save-general-settings` payload, and `get-general-settings` answers with it. The values a merchant may choose from are a property of the shop platform, so they come from the integration through the optional `OrderIdentifiersProviderInterface` companion of `StoreInfoServiceInterface`; the response carries them as `listOfOrderIdentifiers`, and leaves the key out for an integration that publishes none.
- Whether SeQura may collect statistical data is configured together with the general settings: `save-general-settings` reads `isSendStatisticalData` and stores it as the existing `StatisticalData`, and `get-general-settings` answers with it. A payload without the field leaves the stored value alone rather than turning the collection off.
- `AdminAPI::get()->countryConfiguration($storeId)->areSellingCountriesConfigured()`: tells whether a country configuration has been saved for the store, so an integration can wait for the merchant to enable the selling countries in the SeQura portal before it lets the plugin be used.
- The URL of the SeQura portal is part of the connection responses: `DeploymentURL` carries the `portal_base_url` of the `deployments` endpoint, and `getOnboardingData()` and `connect()` return it as `portalUrl` for the environment and deployment the store is connected to. The returned URL points at the store integrations page of the portal (`/development/store-integrations`), where a merchant configures the connected store.

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
