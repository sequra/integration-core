# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

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
