# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`sequra/integration-core` is a **platform-agnostic PHP library** (PHP >= 7.2, pure PHP + vanilla JS, no framework deps) that holds the shared business logic and SeQura API communication layer for all SeQura e-commerce integrations (WooCommerce, PrestaShop, etc.). It is consumed as a Composer library — there is no app to "run". Host platforms wire it up, implement a set of integration interfaces, and call its API facades.

Architecture follows the Onion model with two top-level namespaces (PSR-4):
- `SeQura\Core\Infrastructure\` → `src/Infrastructure` — technical foundation (HTTP, ORM abstraction, logger, task runner, service registry, serializer).
- `SeQura\Core\BusinessLogic\` → `src/BusinessLogic` — SeQura domain logic and the public API facades.

## Authoritative standards — read before writing code

These two documents are binding and are the single source of truth; the summaries elsewhere in this file (and in the agents/skills) defer to them:

- **Before writing or changing any code in `src/`** → read `.claude/docs/codingStandard.md` (architecture, PHP 7.2 syntax floor, PSR-12, PHPStan level 6, the quality gate).
- **Before writing or changing tests** → read `.claude/docs/unitTests.md` (test layout mirroring `src/`, test doubles, isolation rules).

## Working principles

General working guidelines (adapted from [andrej-karpathy-skills/CLAUDE.md](https://github.com/forrestchang/andrej-karpathy-skills/blob/main/CLAUDE.md)). They bias toward caution over speed; for trivial tasks, use judgment.

1. **Think before coding.** State assumptions explicitly and ask when uncertain. If multiple interpretations exist, surface them instead of silently picking one. If a simpler approach exists, say so. If something is unclear, stop and name what's confusing.
2. **Simplicity first.** Write the minimum code that solves the problem — nothing speculative. No features beyond what was asked, no abstractions for single-use code, no unrequested "configurability", no error handling for impossible scenarios. This matters here: the core is deliberately platform-agnostic and dependency-free, so resist pulling in new deps or framework-isms.
3. **Surgical changes.** Touch only what the request requires. Don't refactor working code, reformat adjacent lines, or "improve" comments. Match the existing style (PSR-12) even if you'd do it differently. Remove imports/variables your change orphaned, but leave pre-existing dead code alone — mention it rather than deleting it. Every changed line should trace to the request.
4. **Goal-driven execution.** Turn the task into a verifiable goal and loop until it's met, using this project's gates: a failing/repro test under `./bin/phpunit`, then green `./bin/phpcs` and `./bin/phpstan` (all in the PHP 7.2 container). "Add validation" → write tests for invalid inputs, then make them pass. "Fix the bug" → write a test that reproduces it, then make it pass. For multi-step work, state a brief plan with a verify step for each item.

## Commands

The `bin/` scripts are **Docker wrappers**, not local binaries. `phpunit`, `phpcs`, `phpcbf`, and `phpstan` run via `docker compose exec php …`, so the PHP 7.2 container **must be running first**: `./setup.sh` (or `docker compose up -d --build`). `composer` and `php-syntax-check` instead launch their own throwaway Docker images and don't need the container.

```bash
./bin/composer install           # install deps (runs in composer:latest image)
./bin/phpunit                    # full test suite, PHP 7.2 (uses phpunit.xml, --testdox)
./bin/phpcs                      # PSR-12 style check (config: .phpcs.xml.dist — passed explicitly; the repo's bare .phpcs.xml is ignored by all tooling)
./bin/phpcbf                     # auto-fix style violations
./bin/phpstan                    # static analysis, level 6 over src/ (config: phpstan.neon)
./bin/php-syntax-check --php=7.2 # syntax-only check at a target PHP version (php:<ver>-cli-alpine)
```

**Running a single test:** `bin/phpunit` ignores any arguments you pass it and always runs the whole suite. To target one test, invoke PHPUnit inside the container directly — e.g. `docker compose exec php vendor/bin/phpunit --configuration phpunit.xml --filter testSomeMethod` (see `DEVELOPER_GUIDE.md` for more forms). Note `bin/phpstan` and `bin/composer` *do* forward arguments; `bin/phpcs`/`bin/phpcbf`/`bin/phpunit` do not.

`run-tests.sh` is the local multi-version CI gate — it runs the suite against **local** `/usr/bin/php7.4`–`php8.5` (not Docker) then phpcs + phpstan, so it only runs on a machine that has all those PHP versions installed, not a typical dev box. See `DEVELOPER_GUIDE.md`.

Tests are split into two PHPUnit suites: `tests/Infrastructure` and `tests/BusinessLogic`. The lowest supported version is **PHP 7.2** — `composer.json` pins the platform to 7.2, so do not use syntax newer than 7.2 in `src/`. The container runs XDebug 2.9.8 on port 9003; see `DEVELOPER_GUIDE.md` for IDE setup.

## How the pieces fit together

**Service locator, not a DI framework.** `Infrastructure\ServiceRegister` is a static singleton mapping a class/interface name → a lazy callable that builds the instance. Resolve with `ServiceRegister::getService(SomeInterface::class)`. Everything is wired through this; there is no autowiring.

**`BootstrapComponent` is the wiring manifest.** `BusinessLogic\BootstrapComponent::init()` (extends `Infrastructure\BootstrapComponent`) registers every repository, service, controller, proxy, event listener, and webhook topic handler. When you add a new service/controller/repository, you must register it here, injecting its collaborators via `ServiceRegister::getService(...)`. The host platform calls `init()` once at startup after registering its own platform-specific implementations.

**Four public API facades are the entry points** (`BusinessLogic/AdminAPI`, `CheckoutAPI`, `WebhookAPI`, `ConfigurationWebhookAPI`). Pattern, e.g. `AdminAPI::get()->connection($storeId)->validateConnection($request)`:
- The facade returns controllers wrapped by **Aspects**, which apply cross-cutting behavior before each method call. The aspect *runner* (`Aspect`/`Aspects`/`CompositeAspect`) lives in `BusinessLogic/Bootstrap/Aspect`, but the concrete aspects below live in `BusinessLogic/AdminAPI/Aspects` (namespace `SeQura\Core\BusinessLogic\AdminAPI\Aspects`) and are reused by all four facades.
- `ErrorHandlingAspect` wraps every call so controllers **return Response objects and never throw** to the caller — domain exceptions become `TranslatableErrorResponse`. Keep this contract: controllers return `Request`/`Response` DTOs.
- `StoreContextAspect($storeId)` sets the active store for the duration of the call.

**Multi-store is pervasive.** `BusinessLogic\Domain\Multistore\StoreContext` holds the current store id; `StoreContext::doWithStore($storeId, $callback)` scopes execution. Store-scoped `DataAccess` repositories take `StoreContext` in their constructor and filter persistence by it. Anything reading/writing per-store config must respect the active store.

**Domain layer shape** (`BusinessLogic/Domain/<Feature>/`): `Services/` (logic), `RepositoryContracts/` and `ProxyContracts/` (interfaces), `Models/`. Concrete repository implementations and ORM entities live separately under `BusinessLogic/DataAccess/<Feature>/{Repositories,Entities}`.

**`BusinessLogic/Domain/Integration/` is the host platform's contract.** These interfaces (e.g. `Order`, `Product`, `Category`, `Store`, `Version`, `Log`, `SellingCountries`) are **implemented by the integrating platform**, not the core. The core registers and calls them; the platform supplies the implementations during its own bootstrap. When core code needs platform-specific data, it depends on one of these interfaces.

**Talking to SeQura's HTTP API** goes through `BusinessLogic/SeQuraAPI` proxies (`OrderProxy`, `MerchantProxy`, etc.), built via `AuthorizedProxyFactory`/`ConnectionProxyFactory` on top of `Infrastructure\Http\HttpClient`. Domain services depend on `*ProxyInterface` contracts, never on the HTTP client directly.

**Persistence is abstracted.** `Infrastructure\ORM` provides the repository pattern (`RepositoryRegistry`, `Entity`, `QueryFilter`); the host platform registers concrete storage repositories. The core never assumes a database.

**Async work** runs through `Infrastructure\TaskExecution` (`QueueService`, `Task`, `TaskRunner`). Queue item state transitions emit events on `QueueItemStateTransitionEventBus`; `BootstrapComponent::initEvents()` wires `TransactionLog` listeners to those events.

## Conventions

- Match PSR-12 (`.phpcs.xml.dist`) and keep PHPStan level 6 clean before considering a change done; run `./bin/phpcs` and `./bin/phpstan`.
- New feature work typically touches three places: a `Domain/<Feature>` service + contracts, a `DataAccess/<Feature>` repository (if persisted), and a registration block in `BootstrapComponent`. API-exposed features also need a controller under the relevant `*API` facade and a method on the facade class.
