# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

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
