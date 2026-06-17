# Unit Testing Guide

How we write unit tests in `integration-core`. The rule of thumb: **every unit
(service, repository, controller) gets its own isolated test, and every
collaborator it depends on is replaced by a test double.** A test should fail
only because the unit under test is wrong — never because of a real network
call, a real database, or a bug in a different class.

## Layout

Tests live under `tests/` and mirror `src/` one-to-one:

```
src/Infrastructure/...                  -> tests/Infrastructure/...
src/BusinessLogic/Domain/...            -> tests/BusinessLogic/Domain/...
src/BusinessLogic/CheckoutAPI/...       -> tests/BusinessLogic/CheckoutAPI/...
src/BusinessLogic/DataAccess/...        -> tests/BusinessLogic/DataAccess/...
```

Two PHPUnit suites are defined in `phpunit.xml`: *Infrastructure Test Suite*
(`tests/Infrastructure`) and *Business Logic Test Suite* (`tests/BusinessLogic`).

Test class name = unit name + `Test` (e.g. `CheckoutInitializationServiceTest`),
placed in the package that mirrors the unit's namespace.

## Running

Tests run inside the project's PHP container; there is no host PHP.

```bash
# full matrix + static analysis (CI parity)
./run-tests.sh                      # PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5 + phpcs + phpstan

# during development (single version)
vendor/bin/phpunit --configuration ./phpunit.xml
vendor/bin/phpunit --configuration ./phpunit.xml tests/BusinessLogic/Domain/Checkout/Services/CheckoutInitializationServiceTest.php
```

> Pass a **single** path to scope a run. PHPUnit does not accept multiple
> arbitrary paths in one invocation here — run them one at a time.

The library supports PHP 7.4–8.x, so tests must pass on **all** versions in
`run-tests.sh`, not just your local one.

## Coverage

`phpunit.xml` whitelists `./src` with `processUncoveredFilesFromWhitelist="true"`,
which means **every file in `src/` counts toward coverage** — a file with no test
shows up as 0%, it is not silently ignored. The expectation is therefore:

- Every **service** has a `…ServiceTest`.
- Every **repository** has a `…RepositoryTest`.
- Every **CheckoutAPI / AdminAPI / WebhookAPI controller** is covered by an
  `…ApiTest` that drives it through its public facade.
- Both the **happy path and the meaningful edge/failure paths** are covered
  (e.g. "not found returns null", "unsupported currency returns false",
  exception is translated into an unsuccessful response). One behaviour per test
  method.

When you add a class to `src/`, you add its test in the same PR.

## The test foundation

### `BaseTestCase`

Almost every business-logic test extends
`tests/BusinessLogic/Common/BaseTestCase` (which extends PHPUnit's `TestCase`).
Its `setUp()` builds a fully wired **test container** so the unit under test can
resolve its real collaborators — but pointed at in-memory / fake
implementations:

- `TestServiceRegister` is (re)initialised with a map of service closures (the
  test-environment equivalent of `BootstrapComponent`).
- `TestRepositoryRegistry` registers every entity against an in-memory
  `MemoryRepository` (see "Repository tests").
- Infrastructure is faked: `TestHttpClient`, `TestShopLogger`,
  `TestQueueService`, `TestEncryptor`, `JsonSerializer`, in-memory `Configuration`.

`tearDown()` calls `TestRepositoryRegistry::cleanUp()`, so in-memory state never
leaks between tests.

> **When you add a new service or controller to `src/`, register it in
> `BaseTestCase`** (and, for the production app, in `BootstrapComponent`). If a
> controller is missing from `BaseTestCase`'s map, the facade can't resolve it
> and the API test fails with an unsuccessful response.

### Overriding a dependency for one test

To swap a collaborator for a test double, register it on `TestServiceRegister` in
the test's `setUp()` — closures resolve lazily, so anything that depends on it
picks up the double:

```php
$this->bannerService = new MockBannerService();
TestServiceRegister::registerService(BannerServiceInterface::class, function () {
    return $this->bannerService;
});
```

## Test doubles — which kind to use

We use three kinds of doubles, in order of preference for a given collaborator:

### 1. In-memory repositories (don't mock repositories)

Repositories are **not** mocked. `BaseTestCase` registers each entity against a
real `MemoryRepository` via `TestRepositoryRegistry`, and the production
repository class runs on top of it. This exercises the real repository logic
(serialization, query filters, store scoping) against fast in-memory storage.

### 2. Hand-written `Mock*` components

For shop-integration interfaces (`*Interface`) and for domain services with
behaviour worth controlling, we keep hand-written doubles under
`tests/BusinessLogic/Common/MockComponents/` (and a few feature-local
`MockComponents/` folders). They implement the interface / extend the class and
expose `setMock…()` configuration setters (and sometimes capture call history):

```php
class MockProductService implements ProductServiceInterface
{
    private $productCategories = [];

    public function getProductCategoriesByProductId(string $productId): array
    {
        return $this->productCategories;
    }

    public function setMockProductCategories(array $productCategories): void
    {
        $this->productCategories = $productCategories;
    }
    // ...
}
```

Prefer a `Mock*` component when several tests need the same configurable
behaviour, or when you want to stub one method of a real service while keeping
the rest (extend the real class — e.g. `MockWidgetSettingsService extends
WidgetSettingsService`).

### 3. PHPUnit `createMock()`

For a quick, test-local stub of a concrete collaborator, use `createMock()` and
`->method()->willReturn()/willThrowException()`. This is the right tool for a
focused service test where you only need to script a couple of return values.

## Patterns by layer

### Service tests — isolate the service, mock its dependencies

Instantiate the service directly with mocked dependencies and assert the domain
model / behaviour it returns. Cover the success path, the empty/fallback path,
and the failure path. (Real example: `CheckoutInitializationServiceTest`.)

```php
protected function setUp(): void
{
    parent::setUp();
    $this->credentialsService    = $this->createMock(CredentialsService::class);
    $this->checkoutService       = $this->createMock(CheckoutService::class);
    $this->widgetConfigurator    = $this->createMock(WidgetConfiguratorInterface::class);
    $this->paymentMethodsService = $this->createMock(PaymentMethodsService::class);
}

public function testReturnsNullWhenCredentialsNotFound(): void
{
    // Arrange
    $this->credentialsService->method('getCredentialsByCountry')
        ->willThrowException(new CredentialsNotFoundException());

    // Act
    $data = $this->service()->getInitializationData('ES', 'ES');

    // Assert
    self::assertNull($data);
}
```

If the service caches state in static properties, reset them in `setUp()` so
tests don't bleed into each other (e.g.
`CheckoutService::$generalSettingsFetched = false;`).

### Repository tests — real repository over in-memory storage

Resolve the real repository from the container, write through it, read back, and
assert the round-trip (and store-scoping / overwrite semantics). No mocks.

```php
$this->repository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);
$this->repository->setConnectionData($connectionData);

$loaded = $this->repository->getConnectionDataByDeploymentId('sequra');
$this->assertEquals('test', $loaded->getMerchantId());
```

### Controller / API tests — drive the public facade, mock the domain service

Controllers are tested through their public entry point
(`CheckoutAPI::get()->group($storeId)->method($request)` /
`AdminAPI::get()->…`), with the underlying domain service replaced by a double
registered on `TestServiceRegister`. Assert on the `Response`:
`isSuccessful()` and `toArray()`. (Real example: `CheckoutApiTest`.)

```php
protected function setUp(): void
{
    parent::setUp();
    $this->checkoutInitializationService = $this->createMock(CheckoutInitializationService::class);
    TestServiceRegister::registerService(CheckoutInitializationService::class, function () {
        return $this->checkoutInitializationService;
    });
}

public function testGetInitializationDataToArray(): void
{
    $this->checkoutInitializationService->method('getInitializationData')->willReturn(
        new CheckoutInitializationData('assets1', 'merchant1', ['i1', 'pp3'], 'scriptUri.com', 'es-ES', 'EUR', ',', '.')
    );

    $response = CheckoutAPI::get()->checkout('1')
        ->getInitializationData(new CheckoutInitializationRequest('ES', 'ES'));

    self::assertEquals([
        'assetKey' => 'assets1', 'merchant' => 'merchant1', 'products' => ['i1', 'pp3'],
        'scriptUri' => 'scriptUri.com', 'locale' => 'es-ES', 'currency' => 'EUR',
        'decimalSeparator' => ',', 'thousandSeparator' => '.',
    ], $response->toArray());
}
```

API tests must also cover the **error contract**: a domain service returning
`null` (not configured) should yield a successful response with an empty array,
and a thrown exception should be turned into an *unsuccessful* response by the
`ErrorHandlingAspect`.

## Conventions

- **Arrange / Act / Assert** — separate the three with comments, as above.
- **One behaviour per test method**; name it after the behaviour
  (`testReturnsNullWhenCredentialsNotFound`).
- **No real I/O** — no live HTTP (use `TestHttpClient`), no real DB (use
  `MemoryRepository`), no `sleep`, no clock/`rand` dependence.
- **Deterministic** — tests must not depend on order; `tearDown` cleans state.
- **All PHP versions** — keep tests free of version-specific syntax; they run on
  7.4 through 8.5.

## Checklist for a new unit

- [ ] Test class added under the mirroring `tests/` package, extends `BaseTestCase`.
- [ ] Dependencies isolated: repos via `MemoryRepository`, collaborators via
      `Mock*` components or `createMock()`.
- [ ] New service/controller registered in `BaseTestCase` (and `BootstrapComponent`).
- [ ] Success, edge (empty/fallback), and failure paths covered.
- [ ] For controllers: assert `isSuccessful()` + `toArray()`, including the
      not-configured and exception cases.
- [ ] `vendor/bin/phpunit`, `phpcs`, and `phpstan` all green on every PHP version.
