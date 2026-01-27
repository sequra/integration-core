# Webhook-Based Configuration Management API Design

## 1. Current Architecture Summary

### Relevant Architectural Components

The SeQura integration-core library follows an **Onion Architecture** with clear layer separation:

#### HTTP Layer
- **AdminAPI** (`src/BusinessLogic/AdminAPI/AdminAPI.php`): Facade class providing access to admin controllers with aspect-oriented cross-cutting concerns (error handling, store context).
- **WebhookAPI** (`src/BusinessLogic/WebhookAPI/WebhookAPI.php`): Existing webhook handler for payment status notifications (approved, cancelled, needs_review).
- **CheckoutAPI** (`src/BusinessLogic/CheckoutAPI/CheckoutAPI.php`): Customer-facing checkout operations.

#### Controller Pattern
Controllers are simple classes with constructor-injected services that:
- Accept strongly-typed Request objects
- Delegate to domain services
- Return typed Response objects extending `SeQura\Core\BusinessLogic\AdminAPI\Response\Response`

Example controllers relevant to configuration:
- `GeneralSettingsController`: Get/save general settings
- `OrderStatusSettingsController`: Get/save order status mappings
- `PromotionalWidgetsController`: Get/set widget settings

#### Request/Response Pattern
- **Request objects** (`src/BusinessLogic/AdminAPI/*/Requests/`): Transform API input to domain models via `transformToDomainModel()`.
- **Response objects** (`src/BusinessLogic/AdminAPI/*/Responses/`): Transform domain models to API output via `toArray()`.

#### Aspect-Oriented Programming
- **ErrorHandlingAspect**: Catches exceptions, logs errors, returns `TranslatableErrorResponse`.
- **StoreContextAspect**: Sets multi-store context before method execution.

Aspects are composed via the `Aspects` class:
```php
Aspects
    ::run(new ErrorHandlingAspect())
    ->andRun(new StoreContextAspect($storeId))
    ->beforeEachMethodOfService(ControllerClass::class);
```

#### Service Registration
Services are registered via `ServiceRegister` in `BootstrapComponent::initServices()` and `initControllers()`.

#### Configuration Storage
Configuration is stored via repository interfaces:
- `GeneralSettingsRepositoryInterface`
- `OrderStatusSettingsRepositoryInterface`
- `WidgetSettingsRepositoryInterface`
- etc.

All repositories use `StoreContext` for multi-store isolation.

---

## 2. Webhook Contract Analysis

### Source: `webhook.postman_collection.json`

#### Endpoint
- **URL**: `{{mock_server_url}}/webhook`
- **Method**: POST
- **Content-Type**: `application/json`

#### Topic-Based Routing

The webhook uses a `topic` field in the JSON body to determine the operation:

| Topic | Operation | Direction |
|-------|-----------|-----------|
| `get-store-info` | Retrieve store/platform information | Read |
| `get-general-settings` | Retrieve general settings | Read |
| `save-general-settings` | Update general settings | Write |
| `get-widget-settings` | Retrieve widget configuration | Read |
| `save-widget-settings` | Update widget configuration | Write |
| `get-order-status-list` | Retrieve shop order statuses | Read |
| `get-order-status-settings` | Retrieve order status mappings | Read |
| `save-order-status-settings` | Update order status mappings | Write |
| `get-advanced-settings` | Retrieve debug/log settings | Read |
| `save-advanced-settings` | Update debug/log settings | Write |
| `get-log-content` | Retrieve log entries | Read |
| `remove-log-content` | Clear log content | Write |
| `get-shop-categories` | Retrieve shop categories (paginated) | Read |
| `get-shop-products` | Retrieve shop products (paginated) | Read |
| `get-selling-countries` | Retrieve selling countries | Read |

#### Payload Shapes

**Read Operations** (minimal payload):
```json
{
    "topic": "get-general-settings"
}
```

**Paginated Read Operations**:
```json
{
    "topic": "get-shop-categories",
    "page": 1,
    "limit": 5,
    "search": ""
}
```

**Save General Settings**:
```json
{
    "topic": "save-general-settings",
    "showSeQuraCheckoutAsHostedPage": false,
    "sendOrderReportsPeriodicallyToSeQura": false,
    "allowedIPAddresses": ["127.0.0.1"],
    "excludedCategories": ["16"],
    "excludedProducts": ["1", "2"],
    "sellingCountries": ["ES", "FR", "IT", "PT"],
    "enabledForServices": [],
    "allowFirstServicePaymentDelay": [],
    "allowServiceRegistrationItems": [],
    "defaultServicesEndDate": "P1Y"
}
```

**Save Widget Settings**:
```json
{
    "topic": "save-widget-settings",
    "displayWidgetOnProductPage": true,
    "widgetStyles": "{...JSON string...}",
    "showInstallmentAmountInProductListing": true,
    "showInstallmentAmountInCartPage": true,
    "productPriceSelector": "...",
    "altProductPriceSelector": "...",
    "altProductPriceTriggerSelector": "...",
    "defaultProductLocationSelector": "...",
    "customLocations": [...],
    "cartPriceSelector": "...",
    "cartLocationSelector": "...",
    "widgetOnCartPage": "pp3",
    "listingPriceSelector": "...",
    "listingLocationSelector": "...",
    "widgetOnListingPage": "pp3"
}
```

**Save Order Status Settings**:
```json
{
    "topic": "save-order-status-settings",
    "orderStatusMappings": [
        {"sequraStatus": "approved", "shopStatus": "wc-processing"},
        {"sequraStatus": "needs_review", "shopStatus": "wc-pending"},
        {"sequraStatus": "cancelled", "shopStatus": "wc-cancelled"},
        {"sequraStatus": "shipped", "shopStatus": "wc-completed"}
    ]
}
```

**Save Advanced Settings**:
```json
{
    "topic": "save-advanced-settings",
    "isEnabled": false,
    "level": 3
}
```

#### Response Formats

All responses return JSON with HTTP 200 on success.

**Get General Settings Response**:
```json
{
    "sendOrderReportsPeriodicallyToSeQura": false,
    "showSeQuraCheckoutAsHostedPage": true,
    "allowedIPAddresses": ["127.0.0.1"],
    "excludedProducts": [{"id": "1", "name": "Product 001"}],
    "excludedCategories": [{"id": "16", "name": "Accessories"}],
    ...
}
```

**Get Order Status List Response** (array):
```json
[
    {"id": "wc-pending", "name": "Pending payment"},
    {"id": "wc-processing", "name": "Processing"},
    ...
]
```

**Get Store Info Response**:
```json
{
    "store_name": "Store Name",
    "store_url": "https://store.com",
    "platform": "magento",
    "platform_version": "8.2",
    "plugin_version": "7.2.1",
    "php_version": "7.4",
    "db": "mysql 8.0.0",
    "os": "linux",
    "plugins": ["plugin1", "plugin2"]
}
```

**Save Operations Response** (empty array):
```json
[]
```

#### Error Cases

Based on the existing `WebhookAPI` patterns and AdminAPI error handling:

| Condition | Response |
|-----------|----------|
| Unknown topic | HTTP 501 Not Implemented |
| Validation failure | HTTP 400 with error details |
| Internal error | HTTP 500 with error structure |

---

## 3. Integration Strategy

### Architectural Decision: New ConfigurationWebhookAPI

The existing `WebhookAPI` handles **payment status webhooks** from SeQura (order state changes). The new webhook for configuration management is a **separate concern** and should be implemented as a distinct API entry point.

**Recommended approach**: Create `ConfigurationWebhookAPI` following the same patterns as the existing APIs.

### Why Not Extend Existing WebhookAPI?

1. **Different purpose**: Payment webhooks vs. configuration management
2. **Different payload structure**: Payment webhooks have `sq_state`, `signature`, `order_ref`; Configuration webhooks have `topic`
3. **Different validation rules**: Payment webhooks validate signatures; Configuration webhooks validate topic and payload fields
4. **Different error responses**: Payment webhooks use HTTP 410/501; Configuration webhooks can use standard AdminAPI error responses

### Reuse of Existing Components

| Component | Reuse Strategy |
|-----------|----------------|
| `GeneralSettingsController` | Reuse directly - same service calls |
| `OrderStatusSettingsController` | Reuse directly - same service calls |
| `PromotionalWidgetsController` | Reuse directly - same service calls |
| Request/Response classes | Reuse existing AdminAPI requests/responses |
| Error handling | Reuse `ErrorHandlingAspect` pattern |
| Store context | Reuse `StoreContextAspect` |
| Service layer | No changes needed - controllers already exist |

### New Components Required

1. **ConfigurationWebhookAPI** - Entry point facade
2. **ConfigurationWebhookController** - Topic router/dispatcher
3. **Topic handlers** - Mapped to existing controllers or new services
4. **Integration interfaces** - For platform-specific data (logs, store info, products)

---

## 4. Component Design

### 4.1 ConfigurationWebhookAPI

**Location**: `src/BusinessLogic/ConfigurationWebhookAPI/ConfigurationWebhookAPI.php`

**Responsibilities**:
- Provide entry point for configuration webhook requests
- Apply store context aspect
- Apply error handling aspect

```php
class ConfigurationWebhookAPI
{
    public static function configurationHandler(string $storeId = ''): Aspects
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(ConfigurationWebhookController::class);
    }
}
```

### 4.2 ConfigurationWebhookController

**Location**: `src/BusinessLogic/ConfigurationWebhookAPI/Controller/ConfigurationWebhookController.php`

**Responsibilities**:
- Parse incoming payload and extract topic
- Route to appropriate handler based on topic
- Return appropriate response

**Key Methods**:
```php
class ConfigurationWebhookController
{
    public function handleRequest(array $payload): Response
    {
        $topic = $payload['topic'] ?? '';

        if (empty($topic)) {
            return new TopicMissingErrorResponse();
        }

        $handler = $this->topicHandlerRegistry->getHandler($topic);

        if ($handler === null) {
            return new UnknownTopicErrorResponse($topic);
        }

        return $handler->handle($payload);
    }
}
```

### 4.3 TopicHandlerRegistry

**Location**: `src/BusinessLogic/ConfigurationWebhookAPI/Handlers/TopicHandlerRegistry.php`

**Responsibilities**:
- Map topic strings to handler classes
- Provide handler lookup

```php
class TopicHandlerRegistry
{
    private const HANDLERS = [
        'get-general-settings' => GetGeneralSettingsHandler::class,
        'save-general-settings' => SaveGeneralSettingsHandler::class,
        'get-widget-settings' => GetWidgetSettingsHandler::class,
        'save-widget-settings' => SaveWidgetSettingsHandler::class,
        'get-order-status-list' => GetOrderStatusListHandler::class,
        'get-order-status-settings' => GetOrderStatusSettingsHandler::class,
        'save-order-status-settings' => SaveOrderStatusSettingsHandler::class,
        'get-advanced-settings' => GetAdvancedSettingsHandler::class,
        'save-advanced-settings' => SaveAdvancedSettingsHandler::class,
        'get-log-content' => GetLogContentHandler::class,
        'remove-log-content' => RemoveLogContentHandler::class,
        'get-shop-categories' => GetShopCategoriesHandler::class,
        'get-shop-products' => GetShopProductsHandler::class,
        'get-selling-countries' => GetSellingCountriesHandler::class,
        'get-store-info' => GetStoreInfoHandler::class,
    ];

    public function getHandler(string $topic): ?TopicHandlerInterface;
}
```

### 4.4 TopicHandlerInterface

**Location**: `src/BusinessLogic/ConfigurationWebhookAPI/Handlers/TopicHandlerInterface.php`

```php
interface TopicHandlerInterface
{
    public function handle(array $payload): Response;
}
```

### 4.5 Topic Handlers (Delegation to Existing Controllers)

Most handlers delegate to existing AdminAPI controllers:

**Example: GetGeneralSettingsHandler**
```php
class GetGeneralSettingsHandler implements TopicHandlerInterface
{
    private GeneralSettingsController $controller;

    public function handle(array $payload): Response
    {
        return $this->controller->getGeneralSettings();
    }
}
```

**Example: SaveGeneralSettingsHandler**
```php
class SaveGeneralSettingsHandler implements TopicHandlerInterface
{
    private GeneralSettingsController $controller;

    public function handle(array $payload): Response
    {
        $request = new GeneralSettingsRequest(
            $payload['sendOrderReportsPeriodicallyToSeQura'] ?? true,
            $payload['showSeQuraCheckoutAsHostedPage'] ?? null,
            $payload['allowedIPAddresses'] ?? null,
            $payload['excludedProducts'] ?? null,
            $payload['excludedCategories'] ?? null,
            $payload['defaultServicesEndDate'] ?? null
        );

        return $this->controller->saveGeneralSettings($request);
    }
}
```

### 4.6 New Integration Interfaces

For features not currently exposed via existing controllers:

**LogServiceInterface** (for get-log-content, remove-log-content):
```php
interface LogServiceInterface
{
    public function getLogContent(): array;
    public function removeLogContent(): void;
}
```

**AdvancedSettingsServiceInterface** (for get/save-advanced-settings):
```php
interface AdvancedSettingsServiceInterface
{
    public function getAdvancedSettings(): AdvancedSettings;
    public function saveAdvancedSettings(AdvancedSettings $settings): void;
}
```

**ShopProductServiceInterface** (for get-shop-products):
```php
interface ShopProductServiceInterface
{
    /**
     * @return Product[]
     */
    public function getProducts(int $page, int $limit, string $search): array;
}
```

**StoreInfoServiceInterface** (for get-store-info):
```php
interface StoreInfoServiceInterface
{
    public function getStoreInfo(): StoreInfo;
}
```

### 4.7 Data Flow

```
Platform HTTP Endpoint
        │
        ▼
ConfigurationWebhookAPI::configurationHandler($storeId)
        │
        ▼ (applies aspects: ErrorHandling, StoreContext)
        │
ConfigurationWebhookController::handleRequest($payload)
        │
        ▼ (extracts topic, looks up handler)
        │
TopicHandlerRegistry::getHandler($topic)
        │
        ▼
Specific TopicHandler::handle($payload)
        │
        ▼ (transforms payload to request, calls controller)
        │
Existing AdminAPI Controller (e.g., GeneralSettingsController)
        │
        ▼
Domain Service (e.g., GeneralSettingsService)
        │
        ▼
Repository (e.g., GeneralSettingsRepository)
        │
        ▼
Response object
```

---

## 5. Data & Validation Rules

### 5.1 Payload Validation Rules

#### Required Fields
| Topic | Required Fields |
|-------|-----------------|
| All topics | `topic` (string, non-empty) |
| `get-shop-categories` | `page` (int, >= 1), `limit` (int, >= 1) |
| `get-shop-products` | `page` (int, >= 1), `limit` (int, >= 1) |
| `save-general-settings` | `sendOrderReportsPeriodicallyToSeQura` (bool) |
| `save-widget-settings` | `displayWidgetOnProductPage` (bool), `widgetStyles` (valid JSON string) |
| `save-order-status-settings` | `orderStatusMappings` (array of objects with `sequraStatus` and `shopStatus`) |
| `save-advanced-settings` | `isEnabled` (bool), `level` (int, 1-4) |

#### Type Validation
| Field Type | Validation |
|------------|------------|
| Boolean fields | Must be boolean or coercible to boolean |
| String arrays | Each element must be string |
| JSON string fields | Must be valid JSON (use `json_decode` + check `json_last_error`) |
| Integer fields | Must be integer or numeric string |
| Order status mappings | `sequraStatus` must be one of: `approved`, `needs_review`, `cancelled`, `shipped` |

### 5.2 Sanitization Requirements

| Field | Sanitization |
|-------|--------------|
| IP addresses | Validate IP format, trim whitespace |
| CSS selectors | Allow only safe CSS selector characters |
| Product/Category IDs | Trim, validate as string identifiers |
| JSON strings | Parse and re-encode to ensure valid JSON |
| Search strings | Trim, limit length |

### 5.3 Payload to Domain Model Mapping

#### General Settings
| Webhook Field | Domain Model Property |
|---------------|----------------------|
| `sendOrderReportsPeriodicallyToSeQura` | `GeneralSettings::sendOrderReportsPeriodicallyToSeQura` |
| `showSeQuraCheckoutAsHostedPage` | `GeneralSettings::showSeQuraCheckoutAsHostedPage` |
| `allowedIPAddresses` | `GeneralSettings::allowedIPAddresses` |
| `excludedProducts` | `GeneralSettings::excludedProducts` |
| `excludedCategories` | `GeneralSettings::excludedCategories` |
| `defaultServicesEndDate` | `GeneralSettings::defaultServicesEndDate` |

#### Widget Settings
| Webhook Field | Domain Model Property |
|---------------|----------------------|
| `displayWidgetOnProductPage` | `WidgetSettings::displayOnProductPage` |
| `widgetStyles` | `WidgetSettings::widgetConfig` |
| `showInstallmentAmountInProductListing` | `WidgetSettings::showInstallmentsInProductListing` |
| `showInstallmentAmountInCartPage` | `WidgetSettings::showInstallmentsInCartPage` |
| `productPriceSelector` | `WidgetSelectorSettings::priceSelector` |
| `customLocations` | `WidgetSelectorSettings::customWidgetsSettings[]` |

#### Order Status Settings
| Webhook Field | Domain Model Property |
|---------------|----------------------|
| `orderStatusMappings[].sequraStatus` | `OrderStatusMapping::sequraStatus` |
| `orderStatusMappings[].shopStatus` | `OrderStatusMapping::shopStatus` |

---

## 6. Implementation Plan (Step-by-step)

### Phase 1: Core Infrastructure

1. **Create directory structure**
   ```
   src/BusinessLogic/ConfigurationWebhookAPI/
   ├── ConfigurationWebhookAPI.php
   ├── Controller/
   │   └── ConfigurationWebhookController.php
   ├── Handlers/
   │   ├── TopicHandlerInterface.php
   │   ├── TopicHandlerRegistry.php
   │   └── (individual handlers)
   ├── Requests/
   │   └── (webhook-specific requests if needed)
   └── Responses/
       ├── TopicMissingErrorResponse.php
       └── UnknownTopicErrorResponse.php
   ```

2. **Create `TopicHandlerInterface`** in `src/BusinessLogic/ConfigurationWebhookAPI/Handlers/TopicHandlerInterface.php`

3. **Create `TopicHandlerRegistry`** in `src/BusinessLogic/ConfigurationWebhookAPI/Handlers/TopicHandlerRegistry.php`

4. **Create error response classes**:
   - `TopicMissingErrorResponse` in `src/BusinessLogic/ConfigurationWebhookAPI/Responses/`
   - `UnknownTopicErrorResponse` in `src/BusinessLogic/ConfigurationWebhookAPI/Responses/`

5. **Create `ConfigurationWebhookController`** in `src/BusinessLogic/ConfigurationWebhookAPI/Controller/`

6. **Create `ConfigurationWebhookAPI`** facade in `src/BusinessLogic/ConfigurationWebhookAPI/`

### Phase 2: Topic Handlers (Delegating to Existing Controllers)

7. **Create handler for `get-general-settings`**: `GetGeneralSettingsHandler.php`

8. **Create handler for `save-general-settings`**: `SaveGeneralSettingsHandler.php`

9. **Create handler for `get-widget-settings`**: `GetWidgetSettingsHandler.php`

10. **Create handler for `save-widget-settings`**: `SaveWidgetSettingsHandler.php`

11. **Create handler for `get-order-status-list`**: `GetOrderStatusListHandler.php`

12. **Create handler for `get-order-status-settings`**: `GetOrderStatusSettingsHandler.php`

13. **Create handler for `save-order-status-settings`**: `SaveOrderStatusSettingsHandler.php`

14. **Create handler for `get-selling-countries`**: `GetSellingCountriesHandler.php`

15. **Create handler for `get-shop-categories`**: `GetShopCategoriesHandler.php`

### Phase 3: New Integration Interfaces

16. **Create `LogServiceInterface`** in `src/BusinessLogic/Domain/Integration/Log/`

17. **Create `AdvancedSettingsServiceInterface`** in `src/BusinessLogic/Domain/Integration/AdvancedSettings/`

18. **Create `ShopProductServiceInterface`** in `src/BusinessLogic/Domain/Integration/Product/`
    - Extend existing `ProductServiceInterface` or create new interface

19. **Create `StoreInfoServiceInterface`** in `src/BusinessLogic/Domain/Integration/Store/`

20. **Create domain models**:
    - `AdvancedSettings` in `src/BusinessLogic/Domain/AdvancedSettings/Models/`
    - `StoreInfo` in `src/BusinessLogic/Domain/Store/Models/`
    - `ShopProduct` in `src/BusinessLogic/Domain/Product/Models/`

### Phase 4: Handlers for New Features

21. **Create handler for `get-advanced-settings`**: `GetAdvancedSettingsHandler.php`

22. **Create handler for `save-advanced-settings`**: `SaveAdvancedSettingsHandler.php`

23. **Create handler for `get-log-content`**: `GetLogContentHandler.php`

24. **Create handler for `remove-log-content`**: `RemoveLogContentHandler.php`

25. **Create handler for `get-shop-products`**: `GetShopProductsHandler.php`

26. **Create handler for `get-store-info`**: `GetStoreInfoHandler.php`

### Phase 5: Service Registration

27. **Update `BootstrapComponent.php`**:
    - Add `ConfigurationWebhookController` registration in `initControllers()`
    - Add `TopicHandlerRegistry` registration in `initServices()`
    - Register all topic handlers in `initServices()`

### Phase 6: Response Format Adapters (if needed)

28. **Review and adapt response formats** to match webhook contract:
    - Some existing responses may need wrapper responses to match expected format
    - Create adapter responses if field names differ

---

## 7. Risks and Edge Cases

### 7.1 Technical Risks

| Risk | Mitigation |
|------|------------|
| **Conflicting response formats**: AdminAPI responses may not match webhook contract exactly | Create adapter response classes that transform AdminAPI responses to webhook format |
| **Missing integration interfaces**: Log, store info, products require platform implementation | Define clear interfaces with documentation; provide stub implementations for testing |
| **Store context not provided**: Webhook may not include store ID | Define how store ID is determined (header, payload field, or default) |
| **Error response format mismatch**: Existing TranslatableErrorResponse may not match expected webhook error format | Create webhook-specific error responses or adapt existing ones |

### 7.2 Ambiguities in Webhook Specification

| Ambiguity | Recommended Resolution |
|-----------|----------------------|
| **How is store ID provided?** | Accept `storeId` in payload; fallback to default store |
| **Authentication mechanism** | Not specified in Postman collection; implement signature verification or API key validation at platform level |
| **Pagination total count** | Response examples don't show total count; consider adding `total` field for paginated responses |
| **Partial update semantics** | For save operations, treat missing fields as "no change" vs. "set to null/default" - recommend explicit: missing = no change |

### 7.3 Architectural Pitfalls to Avoid

1. **Don't duplicate business logic**: Topic handlers should delegate to existing controllers/services, not implement logic directly.

2. **Don't bypass aspects**: Always route through the API facade to ensure error handling and store context are applied.

3. **Don't create tight coupling**: Topic handlers should depend on interfaces, not concrete implementations.

4. **Don't skip validation**: Each save handler must validate payload before transforming to request objects.

5. **Don't ignore existing patterns**: Follow established Request/Response patterns; don't introduce new abstractions unnecessarily.

### 7.4 Edge Cases to Handle

| Edge Case | Handling |
|-----------|----------|
| Empty payload | Return `TopicMissingErrorResponse` with 400 status |
| Unknown topic | Return `UnknownTopicErrorResponse` with 501 status |
| Malformed JSON in widget styles | Return validation error before calling service |
| Invalid SeQura status in order mappings | Validate against allowed values before saving |
| Negative page/limit values | Validate and return error |
| Empty required arrays | Decide on semantics (empty = clear all vs. error) |
| Very large payloads | Consider payload size limits at platform level |

---

## 8. Out of Scope

The following items are explicitly **out of scope** for this implementation task:

- Writing automated tests (unit tests, integration tests)
- Platform-specific endpoint implementation (WooCommerce, Magento, etc.)
- Authentication/authorization implementation
- Rate limiting
- Request logging/auditing
- Webhook registration with SeQura
- Migration of existing data

---

## Appendix A: File Structure Summary

```
src/BusinessLogic/
├── ConfigurationWebhookAPI/
│   ├── ConfigurationWebhookAPI.php
│   ├── Controller/
│   │   └── ConfigurationWebhookController.php
│   ├── Handlers/
│   │   ├── TopicHandlerInterface.php
│   │   ├── TopicHandlerRegistry.php
│   │   ├── GeneralSettings/
│   │   │   ├── GetGeneralSettingsHandler.php
│   │   │   └── SaveGeneralSettingsHandler.php
│   │   ├── WidgetSettings/
│   │   │   ├── GetWidgetSettingsHandler.php
│   │   │   └── SaveWidgetSettingsHandler.php
│   │   ├── OrderStatus/
│   │   │   ├── GetOrderStatusListHandler.php
│   │   │   ├── GetOrderStatusSettingsHandler.php
│   │   │   └── SaveOrderStatusSettingsHandler.php
│   │   ├── AdvancedSettings/
│   │   │   ├── GetAdvancedSettingsHandler.php
│   │   │   └── SaveAdvancedSettingsHandler.php
│   │   ├── Log/
│   │   │   ├── GetLogContentHandler.php
│   │   │   └── RemoveLogContentHandler.php
│   │   ├── Shop/
│   │   │   ├── GetShopCategoriesHandler.php
│   │   │   ├── GetShopProductsHandler.php
│   │   │   └── GetSellingCountriesHandler.php
│   │   └── Store/
│   │       └── GetStoreInfoHandler.php
│   └── Responses/
│       ├── TopicMissingErrorResponse.php
│       └── UnknownTopicErrorResponse.php
├── Domain/
│   └── Integration/
│       ├── AdvancedSettings/
│       │   └── AdvancedSettingsServiceInterface.php
│       ├── Log/
│       │   └── LogServiceInterface.php
│       └── Store/
│           └── StoreInfoServiceInterface.php (extend existing)
└── BootstrapComponent.php (modified)
```

## Appendix B: Usage Example

```php
// Platform-specific webhook endpoint handler (e.g., WooCommerce REST controller)
class SeQuraConfigurationWebhookController {

    public function handleWebhook(WP_REST_Request $request): WP_REST_Response {
        $payload = $request->get_json_params();
        $storeId = $this->determineStoreId($request);

        // Use the ConfigurationWebhookAPI
        $response = ConfigurationWebhookAPI::configurationHandler($storeId)
            ->handleRequest($payload);

        return new WP_REST_Response(
            $response->toArray(),
            $response->isSuccessful() ? 200 : $this->getErrorCode($response)
        );
    }
}
```
