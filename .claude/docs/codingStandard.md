# Coding Standard

The conventions for `sequra/integration-core`. This is a **platform-agnostic PHP
library**: it holds the shared SeQura business logic and is consumed by every
platform integration (Magento, WooCommerce, PrestaShop, …). Code here must never
assume a particular shop platform, framework, or runtime.

> This file is the authoritative standard for CORE. It supersedes any generic /
> framework-flavoured notes (e.g. Laravel/Shopify `app/...` conventions) that do
> not apply to this library.

## 0. The quality gate (non-negotiable)

Every change must pass, on **every supported PHP version**:

```bash
./run-tests.sh    # phpunit on PHP 7.4–8.5  +  phpcs (PSR-12, 7.2-compat)  +  phpstan (level 6)
```

- **PHPUnit** — all tests green. New code ships with tests; see `.claude/docs/unitTests.md`.
- **PHPCS** — `PSR12` ruleset + `PHPCompatibility` with `testVersion 7.2-`
  (`.phpcs.xml.dist`). `./bin/phpcbf` auto-fixes most style issues.
- **PHPStan** — **level 6**, `src/` (`phpstan.neon`). No new errors, no blanket
  ignores; fix the type, don't silence the analyser.
- **Syntax floor PHP 7.2** — even though unit tests run on 7.4+, the syntax must
  parse on 7.2 (`./bin/php-syntax-check --php=7.2`). See §3.

A PR that doesn't pass all four is not done.

## 1. Architecture (Clean / Onion)

Two top-level layers, dependencies point **inward only**:

```
            ┌─────────────────────────────────────────────┐
   outer    │  Platform integration (Magento, Woo, …)      │  <- separate repos
            │  implement Domain/Integration interfaces,    │
            │  wire services in their own Bootstrap        │
            └───────────────▲─────────────────────────────┘
                            │ implements / calls
  src/BusinessLogic ┌───────┴───────────────────────────────┐
   API / facade     │ AdminAPI · CheckoutAPI · WebhookAPI ·  │  controllers + Requests/Responses
                    │ ConfigurationWebhookAPI               │
   adapters         │ DataAccess (Entities, Repositories) · │  implement Domain contracts
                    │ SeQuraAPI (Proxies)                   │
   core      ┌──────┴───────────────────────────────────────┐
   Domain    │ Domain/{Feature}/{Models, Services,           │  ← depends on NOTHING outward
             │ RepositoryContracts, ProxyContracts,          │
             │ Exceptions}  +  Domain/Integration/*Interface │
             └──────────────────────────────────────────────┘
  src/Infrastructure: technical foundation (HTTP, ORM, ServiceRegister/DI, queue,
  events, serializer, logger) — depended on by BusinessLogic, depends on no domain.
```

**Dependency rule:** an inner layer must never reference an outer one. The
**Domain** is the innermost core and the most protected — see §2.

### Where things live (`src/BusinessLogic/...`)

| What | Location |
|------|----------|
| Domain models / value objects | `Domain/{Feature}/Models/` |
| Domain services | `Domain/{Feature}/Services/` |
| Domain exceptions | `Domain/{Feature}/Exceptions/` |
| Repository **contracts** (interfaces) | `Domain/{Feature}/RepositoryContracts/{Foo}RepositoryInterface.php` |
| Proxy **contracts** (interfaces) | `Domain/{Feature}/ProxyContracts/` |
| **Platform** integration contracts | `Domain/Integration/{Feature}/{Foo}Interface.php` |
| ORM entities | `DataAccess/{Feature}/Entities/` |
| Concrete repositories | `DataAccess/{Feature}/Repositories/` |
| API proxies (SeQura HTTP) | `SeQuraAPI/{Feature}/` |
| AdminAPI / CheckoutAPI / Webhook controllers + Requests/Responses | `{AdminAPI,CheckoutAPI,...}/{Feature}/` |
| DI wiring (composition root) | `BootstrapComponent.php` |

## 2. Domain layer must be independent

This is the most important rule in the library. A domain class
(`Domain/**`) may depend **only** on:

- other Domain models / services in this library,
- interfaces it **owns**: `RepositoryContracts`, `ProxyContracts`,
- `Domain/Integration/**` interfaces (the platform boundary),
- pure PHP and `src/Infrastructure` primitives that carry no platform assumption
  (e.g. serializer, logger contracts).

A domain class must **never**:

- reference a concrete repository, a concrete proxy, or anything in
  `DataAccess/`, `SeQuraAPI/`, or an API controller — depend on the **contract**,
  not the implementation;
- import or assume a shop platform / framework (no Magento, Woo, Symfony, Laravel,
  global `$_GET`/superglobals, sessions, filesystem, `echo`, HTTP specifics);
- perform I/O directly (DB, network, clock, randomness) — that belongs behind a
  Proxy/Repository/Integration interface so it can be mocked in a unit test;
- be constructed with `new` from inside another domain service — collaborators are
  **injected** (see §4).

Why: the domain is shared by all platforms and must be unit-testable in isolation
with in-memory test doubles (`.claude/docs/unitTests.md`). If a platform need leaks in,
every other integration inherits the coupling.

### The platform boundary: `Domain/Integration`

Anything the library needs **from the shop** is expressed as an interface in
`Domain/Integration/{Feature}/` (e.g. `ProductServiceInterface`,
`OrderCreationInterface`, `WidgetConfiguratorInterface`,
`ExpressCheckoutIntegrationInterface`). The platform repo implements these and
registers them in its own bootstrap. Domain code calls the interface; it has no
idea which platform answers.

When the domain needs new information from the shop, **add a method to the
appropriate Integration interface** — never reach for platform code.

## 3. PHP language floor (7.2)

CORE runs on PHP **7.2 → 8.x**. Do **not** use features newer than 7.2:

- ❌ typed properties, ❌ union/intersection types, ❌ `readonly`,
  ❌ promoted constructor properties, ❌ native `enum`, ❌ typed class constants,
  ❌ named arguments, ❌ first-class callable syntax, ❌ `match`, ❌ nullsafe `?->`,
  ❌ arrow-fn-only constructs that don't exist in 7.2.
- ✅ Declare properties as `protected $foo;` / `private $foo;` with a `@var`
  docblock; initialise in the constructor body.
- ✅ Untyped class constants: `public const FOO = '...';`.
- ✅ Scalar + nullable param/return type hints (`?string`) are fine (7.1+).
- ✅ Express closed value sets with the **value-object (Capability) pattern**
  (§5), not native enums.

Type information that the language can't carry goes in docblocks — see §6.

## 4. Dependencies & DI

- **Composition root is `BootstrapComponent`.** Services and repositories are
  registered there as factory closures and resolved by class-name constant via
  `ServiceRegister::getService(Foo::class)`.
- **Constructor injection only.** A class declares its collaborators as
  constructor params (typed by interface where an abstraction exists). No service
  locator calls or `new` inside business logic.
- **Depend on interfaces** for anything with more than one implementation or any
  platform/IO concern (repositories, proxies, integration services).
- Mirror every new registration in `tests/BusinessLogic/Common/BaseTestCase`
  (test composition root) so units resolve in tests too.

## 5. Domain models, value objects, exceptions

- **Models** are immutable in spirit: `protected` properties set in the
  constructor, state exposed through explicit getters (`getX()`, `isX()`).
  `toArray()` is **allowed** on CORE domain models (legacy convention — do not add
  a separate Mapper layer).
- **Value objects for closed sets (Capability pattern)** — e.g.
  `ExpressCheckoutPage`: `private const` per value, `private __construct`, a
  `public static function {value}(): self` factory each, a `get{Concept}()` getter,
  and a `parse(string $raw): self` that throws `Invalid{Concept}Exception` on
  unknown input. Callers hold the object, not the raw string.
- **Models wrapping collections** validate in the constructor (`assertX()` /
  `validateConfigs()`), fail loudly (don't silently filter/dedupe), and drop
  setters once invariants are enforced.
- **Exceptions from domain logic extend `BaseTranslatableException`** with a
  `TranslatableLabel(human, i18n.key)` and an HTTP-style `protected $code`
  (`400/404/409/...`). Do **not** throw built-in `\InvalidArgumentException` from
  domain code — wrap contract violations in a translatable domain exception too.
  Naming: `Invalid{Concept}Exception` (wrong shape), `Duplicated{Concept}Exception`
  (uniqueness), etc.

## 6. Type safety & docblocks

- Scalar/nullable type hints on every parameter and return type that 7.2 allows.
- Array shapes **always** documented: `@param string[] $ids`,
  `@return array<string, mixed>` — never a bare `array` without a docblock type.
- **Docblocks are house style throughout `src/`**: a class docblock (1–3 lines on
  purpose), and `@param` / `@return` / `@throws` on methods. `@throws` must list
  every exception a method can propagate (PHPStan level 6 + callers rely on it).
  `@inheritDoc` is fine on overrides where the parent doc suffices.
- Keep `@throws` honest when you refactor — if a delegated call newly throws
  `DeploymentNotFoundException`, surface it on the caller's docblock.

## 7. API / facade layer

- Public entry points are the `…API` facades (e.g.
  `CheckoutAPI::get()->checkout($storeId)->getInitializationData($request)`),
  which wrap controller methods with **Aspects** (`ErrorHandlingAspect`,
  `StoreContextAspect`). A thrown exception is converted by `ErrorHandlingAspect`
  into an **unsuccessful `Response`** — controllers/services should throw
  meaningful exceptions rather than returning error flags.
- Each endpoint takes a **Request** object and returns a **Response** object
  (`extends Response`, implements `toArray()`); `isSuccessful()` + `toArray()` are
  the contract the platform consumes.
- A **Request** is a plain value object (constructor + getters) unless it genuinely
  needs `fromArray()`/`toArray()` deserialization — only extend
  `DataTransferObject` when those are actually used.
- Controllers are thin: translate Request → domain call → Response. No business
  logic in controllers.

## 8. Repository pattern

- Domain owns `{Foo}RepositoryInterface`; `DataAccess/` provides the implementation
  backed by `RepositoryRegistry::getRepository({Entity}::getClassName())`.
- Repositories are **store-scoped**: inject `StoreContext`, filter every query by
  `storeId`, stamp `setStoreId(...)` on writes.
- `setX()` is **upsert** (select existing by store → update, else create).
- Wire the repository in `BootstrapComponent` **and** `BaseTestCase`; register the
  entity against `MemoryRepository` in `BaseTestCase` for tests.

## 9. General style

- KISS / DRY / YAGNI. Extract shared logic to a base class or a lower-level service
  rather than copy-pasting; don't build for hypothetical needs.
- One class per file. `Interface` suffix for interfaces, `Abstract` prefix for
  abstract classes. Prefer composition over inheritance.
- Methods: verb-noun (`getScriptUri`, `solicit`); booleans `is/has/should/can`.
  Keep them short, use early returns, ≤ ~4 params (use a value object beyond that).
- Constants for magic strings/numbers, scoped to a class. No global constants.
- Match the surrounding code's formatting; `./bin/phpcbf` settles PSR-12 disputes.

## 10. Checklist for a new class / feature

- [ ] Placed in the correct layer (§1 table); domain code obeys §2 (no platform/IO,
      depends only on contracts).
- [ ] New shop dependency expressed as a `Domain/Integration` interface, not platform code.
- [ ] PHP 7.2-safe syntax (§3); type hints + docblocks with `@throws` (§6).
- [ ] Registered in `BootstrapComponent` **and** `BaseTestCase`.
- [ ] Isolated unit test added (mock collaborators, in-memory repos) — `.claude/docs/unitTests.md`.
- [ ] `./run-tests.sh` green: phpunit (all PHP versions) + phpcs + phpstan L6.
