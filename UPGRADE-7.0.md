# Upgrading from integration-core 6.x to 7.0.0

integration-core 7.0.0 replaces the core's task model with contracts a platform can implement itself. Business work is now written as jobs (`BusinessTaskInterface`) that do not depend on the built-in task runner, and the core hands them to a `TaskExecutorInterface`. By default that executor is the built-in queue and task runner, so a platform that keeps using it changes little. A platform that has its own scheduler can now run the core's jobs on it.

Shops move once, from 6.x to 7.0.0, together with the plugin versions that adopt it. This guide lists what every platform has to check, what each known platform has to change, and how to plug in a native scheduler.

- [Every platform](#every-platform)
  - [Recurring work](#recurring-work)
  - [Stored queue items](#stored-queue-items)
  - [Runner ids](#runner-ids)
  - [Transaction log](#transaction-log)
  - [Removed and changed classes](#removed-and-changed-classes)
- [Per platform](#per-platform)
- [Plugging in a native scheduler](#plugging-in-a-native-scheduler)

## Every platform

### Recurring work

The daily order report and the daily order cleanup are no longer started by `TickEvent` listeners of their own. One service, `RecurringWorkRunner`, decides what is due and enqueues it through the `TaskExecutorInterface`: `RecurringWorkRunner::runDue()` enqueues an `OrderReporter` for every store whose report time has come (`OrderReportScheduler`) and an `OrderCleanupTask` when the cleanup is due (`OrderCleanupScheduler`).

- On the built-in runner nothing has to be done: `SeQura\Core\BusinessLogic\BootstrapComponent::initEvents()` registers a `TickEvent` handler that calls `runDue()` on every tick of the task runner.
- A platform that schedules work itself, or that switches the task runner's wake-up off, must call `runDue()` from its own scheduler at least hourly:

  ```php
  ServiceRegister::getService(RecurringWorkRunner::class)->runDue();
  ```

  Calling it more often is harmless: work that is not due is not enqueued again.
- `runDue()` holds a `RecurringWorkLockInterface` lock while it runs, so two overlapping calls do not enqueue the same work twice. The default lock, `FileRecurringWorkLock`, is a file lock in the system temporary directory, so it only covers one server. A platform whose recurring work can be triggered on more than one server must register its own `RecurringWorkLockInterface` (for example backed by the database or a shared cache) after `parent::init()`.

### Stored queue items

Queue items stored by 6.x are translated when they are read, so nothing has to be drained before upgrading. The translation is done by `LegacyStoredTaskUpgrader` (registered as `StoredTaskUpgrader`) and covers the 6.x items that can still be queued in a shop:

- an `OrderReporter` item continues as an `OrderReporter` job on the page it had reached;
- a `TransactionalOrderUpdateTask` item continues as the same job, still linked to its transaction log row.

Only items written with the `JsonSerializer` are translated, because the upgrader reads the stored JSON (`class_name` and `storeId`). A platform that registers another serializer must let its 6.x queue run empty before upgrading.

A stored item whose class no longer exists cannot be rebuilt. The runner marks it `failed` at once instead of retrying it. No released plugin bundles core 6.1 or later, so no shop holds a queued `OrderCleanupTask` item today. One queued by a plugin that shipped on 6.1 or 6.2 before 7.0.0 would not be translated: it would be marked failed, and the cleanup would run again at its next due time.

### Runner ids

`GuidProvider::generateGuid()` now returns `bin2hex(random_bytes(32))`, so the ids of task runner processes are 64 characters long (6.x ids were shorter). They are stored in two places:

- the `guid` index of the `Process` entity;
- the `taskRunnerStatus` configuration value, stored as `['guid' => ..., 'timestamp' => ...]`.

Check that the column your platform uses for the `Process` `guid` index, and the column that holds configuration values, can hold a 64 character `guid`. A column that truncates it makes the runner fail to recognise its own process.

### Transaction log

The transaction log is now written by `TransactionLogRecorder` around each job, instead of by listeners on queue item events:

- It also shows jobs with no order, such as `OrderReporter` and `OrderCleanupTask`. Their rows have an empty merchant reference and the job's class name as the event code. A failed row of such a job is removed once the same job succeeds.
- It has no `aborted` status any more. A row is `queued`, `in_progress` or `failed`, and it is deleted when its job succeeds. Rows written as `aborted` by 6.x stay until they are purged after a month.
- A job is linked to its row through the job itself (`TransactionLogAwareInterface::getTransactionLogId()` / `setTransactionLogId()`), not through the queue item. The row's execution id is now its own id.

### Removed and changed classes

| 6.x | 7.0.0 |
| --- | --- |
| `Domain\OrderReport\Tasks\OrderReportTask` | Deleted. Send a report directly with `OrderReportService::sendReport()` (`OrderReportService::sendReport`), passing a `ReportData`. |
| `OrderReporter` (an `Orchestrator` that created `OrderReportTask` sub-jobs) | A `BusinessTaskInterface` job holding a `page` (`new OrderReporter(int $page = 1)`). It sends one page with `OrderReportService::sendReport()` and enqueues the next page itself. |
| `OrderReport\Listeners\TickEventListener` (`TickEventListener`) | Deleted. `RecurringWorkRunner::runDue()` (`runDue()`) enqueues the due reports. |
| `OrderCleanup\Listeners\OrderCleanupTickListener` (`OrderCleanupTickListener`) | Deleted. `runDue()` enqueues the cleanup when it is due. |
| `OrderCleanup\Tasks\OrderCleanupTask` (`OrderCleanupTask`) | Moved to `Domain\OrderCleanup\Tasks\OrderCleanupTask`, now a `BusinessTaskInterface` job. |
| `OrderCleanupService::cleanup()` returned `?int` and took an `$afterEachBatch` callback | `OrderCleanupService::cleanup()` returns a `Generator` that yields after each batch and returns the count. |
| `Webhook\Tasks\OrderUpdateTask` (`OrderUpdateTask`) | Deleted. `WebhookHandler::handle()` updates the shop order itself. |
| `StoreIntegrationMigrateTask` and `DeploymentMigrateTask` extended `Task` | No longer Tasks. Call `execute()` the same way as before. |
| `TransactionLogAwareInterface` with `getTransactionLog()` / `setTransactionLog()` / `getStoreId()` | Extends `BusinessTaskInterface`, with `getTransactionLogId()` / `setTransactionLogId()` / `getTransactionData()`. `TransactionLogAwareTrait` changed the same way. |
| `TransactionalTask` and `TransactionalOrderUpdateTask` extended `Task` | `BusinessTaskInterface` jobs; `TransactionalOrderUpdateTask` no longer stores a `storeId`. |
| The transaction log listeners: `Listener`, `CreateListener`, `LoadListener`, `UpdateListener`, `FailedListener`, `AbortedListener` | Deleted. Schedule a logged job with `TransactionLogRecorder::schedule()` (`TransactionLogRecorder::schedule`); jobs are logged by `TransactionLogRecorder` while they run. |
| `TransactionLogService::create()`, `load()`, `hasQueueItem()` | Deleted. Use `TransactionLogService::createOrderLog()`, `createJobLog()`, `findById()` and `deleteFailedWithoutOrder()`. |
| `TransactionLogRepositoryInterface::getItemByExecutionId(int $executionId)` | Takes a second `TransactionData` argument; `getItemById()` and `findWithoutOrder()` are new. A platform that implements the interface must add them. |
| `QueueNameProvider` and `QueueNameProviderInterface` | Deleted. The queue name, priority and context of a job come from `TaskMetadataProviderInterface` (default `DefaultTaskMetadataProvider`). |
| `QueueService::enqueue()` | Takes a new optional last argument, `earliestStartTimestamp` (`?int $earliestStartTimestamp = null`): the item is not started before that time. |
| `Orchestrator`, `ExecutionDetails`, `QueueService::create()`, `QueueService::batchStatusUpdate()` | Deleted. Replacement: none. `OnReportProgress` no longer forwards progress to a parent item. |
| `GuidProvider::generateGuid()` | Returns a 64 character id; see [Runner ids](#runner-ids). |

## Per platform

### magento2-core

Stays on the built-in runner. No code change is needed beyond replacing the removed classes it uses (see the table above). Check that the `Process` `guid` index column and the configuration value column hold a 64 character `guid`.

### prestashop-sequra

Stays on the built-in runner. No code change is needed beyond replacing the removed classes it uses (see the table above). Check that the `Process` `guid` index column and the configuration value column hold a 64 character `guid`.

### bigcommerce

- Drop its own tick listener that enqueues `OrderReporter`: `runDue()` now enqueues the order reports, and keeping the listener would send them twice.
- Schedule order updates with `TransactionLogRecorder::schedule` instead of `QueueService::enqueue()`:

  ```php
  ServiceRegister::getService(TransactionLogRecorder::class)->schedule(
      new TransactionalOrderUpdateTask($orderUpdateData, $transactionData)
  );
  ```

  `TransactionLogRecorder::schedule()` creates the order's transaction log row and enqueues the job through the `TaskExecutorInterface`.

### woocommerce-sequra

Replace `new OrderReportTask(...)` with a direct call to `OrderReportService::sendReport`:

```php
ServiceRegister::getService(OrderReportService::class)->sendReport(
    new ReportData($merchantId, $reportOrderIds, $statisticsOrderIds)
);
```

### wix-sequra

- Its `DeploymentMigrateTask` subclass keeps working: `DeploymentMigrateTask` is called with `execute()` as before. Since `DeploymentMigrateTask` no longer extends `Task`, the subclass must not call `parent::__construct()` or methods inherited from `Task`.
- With the task runner's wake-up off, nothing ticks for it, so it must call `runDue()` from its own scheduler at least hourly (see [Recurring work](#recurring-work)).

### integration-middleware

`QueueNameProviderInterface` and `QueueNameProvider` are gone from the core. integration-middleware must own its queue-name provider interface (and its implementation) in its own code base. To choose the queue of the core's jobs, register a `TaskMetadataProviderInterface` instead.

## Plugging in a native scheduler

A platform that has its own job scheduler (a message queue, cron jobs, a framework's queue) can run the core's jobs on it instead of on the built-in task runner.

1. **Implement `TaskExecutorInterface`.** Its `enqueue()` and `scheduleDelayed()` receive a `BusinessTaskInterface` job. Ask `TaskMetadataProviderInterface` for the job's `TaskExecutionConfig` (queue name, priority from 0 to 100, and context), then store the payload built by `TaskPayload::fromTask`:

   ```php
   class NativeTaskExecutor implements TaskExecutorInterface
   {
       public function enqueue(BusinessTaskInterface $task): void
       {
           $this->scheduleDelayed($task, 0);
       }

       public function scheduleDelayed(BusinessTaskInterface $task, int $delaySeconds): void
       {
           $config = ServiceRegister::getService(TaskMetadataProviderInterface::class)->getExecutionConfig($task);
           $payload = TaskPayload::fromTask($task, $config->getContext())->toArray();

           $this->platformQueue->push($config->getQueueName(), $payload, $config->getPriority(), $delaySeconds);
       }
   }
   ```

   The payload (`TaskPayload::fromTask($task, $context)->toArray()`) is a plain array of the job's class, its `toArray()` data and the context (store id), so it can be stored as JSON.

2. **Register it after `parent::init()`**, so it replaces the core's default executor (`TaskRunnerExecutor`, which enqueues on the built-in queue):

   ```php
   class PlatformBootstrap extends \SeQura\Core\BusinessLogic\BootstrapComponent
   {
       public static function init(): void
       {
           parent::init();

           ServiceRegister::registerService(TaskExecutorInterface::class, static function () {
               return new NativeTaskExecutor();
           });
       }
   }
   ```

   A platform that wants other queue names or priorities registers its own `TaskMetadataProviderInterface` the same way.

3. **Run each stored payload with `BusinessTaskRunner::run`** from the worker:

   ```php
   ServiceRegister::getService(BusinessTaskRunner::class)->run($payload);
   ```

   `BusinessTaskRunner::run(array $payload)` rebuilds the job, runs it in its store context and records it in the transaction log. It throws when the job fails.

4. **Let the scheduler own retries.** The core does not retry a job run through `BusinessTaskRunner::run`; when it throws, the scheduler decides whether and when to run the payload again.

5. **Call `runDue()` at least hourly.** Without the built-in runner's tick, nothing calls `RecurringWorkRunner::runDue()` (`runDue()`) for the platform, so the daily order report and the order cleanup are only enqueued when the scheduler calls it. A platform on more than one server also registers its own `RecurringWorkLockInterface` (see [Recurring work](#recurring-work)).
