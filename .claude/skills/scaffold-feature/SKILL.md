---
name: scaffold-feature
description: Scaffold a new SeQura integration-core feature end-to-end following repo conventions — Domain service/contracts/models, a store-scoped DataAccess repository + ORM entity, the BootstrapComponent registration block, and (when API-exposed) a facade controller with Request/Response DTOs plus a facade method. Use when adding a new persisted and/or API-exposed feature under src/BusinessLogic/Domain/<Feature>.
---

# Scaffold an integration-core feature

This repo adds a feature in a fixed, convention-heavy shape spread across three to
four places. This skill generates that skeleton so nothing is forgotten and every
file matches the existing style. **`CountryConfiguration` is the canonical reference**
— read those files when a template detail is unclear.

## Before you start

1. Get the feature name in `PascalCase` (e.g. `ShippingRules`) and decide:
   - **Persisted?** → needs a `DataAccess/<Feature>` repository + ORM entity.
   - **API-exposed?** → needs a controller under a facade (`AdminAPI`, `CheckoutAPI`,
     `WebhookAPI`, or `ConfigurationWebhookAPI`) + a facade method.
   - **Talks to SeQura's HTTP API?** → also needs a `ProxyContracts/` interface and a
     `SeQuraAPI` proxy (see `OrderProxy`). Out of scope for the basic skeleton — flag it.
2. Hard constraints (these are non-negotiable in `src/`) — the binding source of truth
   is `.claude/docs/codingStandard.md` (and `.claude/docs/unitTests.md` for tests); the
   list below is the summary:
   - **PHP 7.2 syntax only.** No arrow fns, no `?->`, no named args, no enums, no
     constructor promotion, no union/typed-property syntax newer than 7.2.
   - **PSR-12** (`.phpcs.xml.dist`) and **PHPStan level 6** clean.
   - Namespaces are PSR-4: `SeQura\Core\BusinessLogic\...` → `src/BusinessLogic/...`.
   - Every class/interface/method gets a docblock matching the surrounding style.

## Layers to generate

For feature `<Feature>` with a domain model `<Model>`:

### 1. Domain (`src/BusinessLogic/Domain/<Feature>/`)
- `Models/<Model>.php` — plain immutable-ish model: constructor + getters, no framework deps.
- `RepositoryContracts/<Feature>RepositoryInterface.php` — the persistence contract.
  Docblocks say **"for current store context"** — the store scoping is implicit, never a param.
- `Services/<Feature>Service.php` — business logic. Depends on the **`...RepositoryInterface`**
  (and other `*Interface`/`*Service` collaborators), **never** on a concrete repository,
  the ORM, or `HttpClient`.
- `Exceptions/*.php` — domain exceptions. If the error must surface to an API caller as a
  translatable message, extend the translatable base (see `Domain/Translations`) so
  `ErrorHandlingAspect` can turn it into a `TranslatableErrorResponse`.

### 2. DataAccess (`src/BusinessLogic/DataAccess/<Feature>/`) — only if persisted
- `Entities/<Feature>.php` — `extends Entity`. Must declare `public const CLASS_NAME = __CLASS__;`,
  a `protected $storeId;`, and implement `inflate()`, `toArray()`, `getConfig()`. `getConfig()`
  returns an `EntityConfiguration` whose `IndexMap` adds at least `addStringIndex('storeId')`.
- `Repositories/<Feature>Repository.php` — `implements <Feature>RepositoryInterface`.
  Constructor takes `(RepositoryInterface $repository, StoreContext $storeContext)`. **Every**
  read/write filters by `storeContext->getStoreId()` via a `QueryFilter` and stamps `storeId`
  on save/update. This is the multistore contract — never skip it.

### 3. BootstrapComponent registration (`src/BusinessLogic/BootstrapComponent.php`)
Add a `ServiceRegister::registerService(...)` lazy-callable block in the matching `init*()` method:
- repository interface → concrete repo in **`initRepositories()`** (resolve the ORM repo with
  `RepositoryRegistry::getRepository(<Feature>::getClassName())` and inject `StoreContext`).
- service → in **`initServices()`** (inject the repo interface + collaborators via `ServiceRegister::getService(...)`).
- controller → in **`initControllers()`** (inject the service(s)).
- Also ensure the ORM **entity class is registered** wherever the entity list lives
  (host platform / `RepositoryRegistry`); the core references it via `getClassName()`.
- If the feature emits/handles events or webhook topics, wire `initEvents()` / `initTopicHandlers()` too.

### 4. API facade (only if API-exposed) — e.g. `src/BusinessLogic/AdminAPI/<Feature>/`
- `<Feature>Controller.php` — **returns `Response` objects and NEVER throws** to the caller.
  Each method returns a typed `*Response`; mutating methods take a typed `*Request`.
- `Requests/*.php` — `extends Request`, expose `transformToDomainModel()`.
- `Responses/*.php` — `extends Response`, implement `toArray()`.
- Add a facade method on the facade class (e.g. `AdminAPI::<feature>(string $storeId): Aspects`)
  returning `Aspects::run(new ErrorHandlingAspect())->andRun(new StoreContextAspect($storeId))
  ->beforeEachMethodOfService(<Feature>Controller::class)`.

### 5. Tests (`tests/BusinessLogic/<Feature>/`)
Follow `.claude/docs/unitTests.md` (layout mirrors `src/`, collaborators replaced by test doubles).
Mirror an existing controller/service test (see `tests/BusinessLogic/AdminAPI/CountryConfiguration/`).
Register the ORM entity in the test bootstrap so the in-memory repository can resolve it.

## Verify (the project gate — required before calling it done)

The Docker `php` service must be up (`./setup.sh`). Then, per `CLAUDE.md`:
```bash
./bin/php-syntax-check --php=7.2     # 7.2 syntax sanity on new files
./bin/phpcbf && ./bin/phpcs          # PSR-12
./bin/phpstan                        # level 6 over src/
./bin/phpunit                        # full suite (bin/phpunit ignores args)
```
Single test while iterating:
`docker compose exec php vendor/bin/phpunit --configuration phpunit.xml --filter <TestName>`

## Output discipline

Generate only the files the feature actually needs — skip DataAccess if not persisted,
skip the facade if not API-exposed. Don't invent configurability or extra methods beyond
what was asked (CLAUDE.md: simplicity first, surgical changes). After generating, list every
file created/edited and confirm the registration block was added to `BootstrapComponent`.
