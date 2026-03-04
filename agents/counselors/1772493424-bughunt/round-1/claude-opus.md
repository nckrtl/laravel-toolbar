Now I have a thorough understanding of the codebase. Here is my correctness audit:

---

# Correctness Audit: laravel-toolbar

## Finding 1 — `ToolbarConfig::collectors(null)` causes TypeError

- **severity**: critical
- **confidence**: high
- **location**: `src/Data/ToolbarConfig.php:156-168` — `collectors()`
- **evidence**: When called with `null` (the default), line 158 evaluates `is_null(null) || empty(null)` → `true`, setting `$this->collectors = []`. But execution continues to line 162: `foreach ($collectors as $collector)` — iterating over `null` throws `TypeError: foreach() argument must be of type array|object, null given` in PHP 8.
- **impact**: Calling `->collectors(null)` or `->collectors()` (no args) crashes the entire toolbar initialization. While the default constructor always passes an array, any user customization calling `->collectors()` with no args will crash.
- **minimal fix**: Add `return $this;` after line 159, or restructure with early return:
  ```php
  if (is_null($collectors) || empty($collectors)) {
      $this->collectors = [];
      return $this;
  }
  ```
- **test idea**: Call `(new ToolbarConfig)->collectors(null)` and assert it doesn't throw. Assert `$config->collectors` is `[]`.

## Finding 2 — `QueriesCollector` null dereference when QueryObserver is removed

- **severity**: critical
- **confidence**: high
- **location**: `src/Collectors/QueriesCollector.php:53-61` — `setEntries()`
- **evidence**: `$toolbar->config->getObserver(QueryObserver::class)` returns `null` if the user customizes observers to exclude `QueryObserver`. Line 55: `$queryObserver->totalTime` then throws `Error: Attempt to read property on null`.
- **impact**: Crashes data collection for every request if the user removes `QueryObserver` from their config but leaves `QueriesCollector` enabled.
- **minimal fix**: Add null guard:
  ```php
  $queryObserver = $toolbar->config->getObserver(QueryObserver::class);
  if (!$queryObserver) { return; }
  ```
- **test idea**: Configure toolbar without `QueryObserver` in observers, trigger `QueriesCollector::collectData()`. Expect no crash, expect empty queries.

## Finding 3 — `ModelsCollector` null dereference when ModelObserver is removed

- **severity**: critical
- **confidence**: high
- **location**: `src/Collectors/ModelsCollector.php:35-37` — `setEntries()`
- **evidence**: Same pattern as Finding 2. `getObserver(ModelObserver::class)` returns `null`, then `$modelObserver->hydrationEntries` crashes.
- **impact**: Crashes data collection for every request if user removed `ModelObserver`.
- **minimal fix**: Add null guard before accessing `->hydrationEntries`.
- **test idea**: Configure toolbar without `ModelObserver`, call `ModelsCollector::collectData()`. Expect no crash.

## Finding 4 — `ProfilerCollector::getTotals()` throws uncaught exceptions on timing discrepancies

- **severity**: high
- **confidence**: high
- **location**: `src/Collectors/ProfilerCollector.php:171-176` — `getTotals()`
- **evidence**: Lines 172 and 176 throw raw `\Exception` for wall time overlaps and gaps. These exceptions propagate through `CollectorManager::collectData()` which has no try/catch, crashing the entire response. Due to floating-point arithmetic and process scheduling variability, sub-millisecond discrepancies between checkpoint timestamps are expected in production.
- **impact**: Intermittent request crashes when timing is slightly off. The toolbar — a dev tool — kills the application it's meant to observe.
- **minimal fix**: Either catch these exceptions in `CollectorManager::collectData()` and log a warning, or use a tolerance threshold (e.g., 0.1ms) before throwing.
- **test idea**: Create request stages with a tiny overlap (e.g., 0.001ms). Call `getTotals()`. Currently throws — should either tolerate or be caught.

## Finding 5 — `FetchesStackTrace` trait accesses undefined `$this->options`

- **severity**: high
- **confidence**: high
- **location**: `src/Observers/FetchesStackTrace.php:34,46` — `ignoredPaths()` and `ignoredVendorPath()`
- **evidence**: Both methods access `$this->options['ignore_paths']` and `$this->options['ignore_packages']`. `QueryObserver` (which uses this trait) has no `$options` property. In PHP 8.2+, accessing undeclared dynamic properties triggers a deprecation warning. In PHP 9, it will be a fatal error. The `?? []` and `?? true` fallbacks mean the code works but emits warnings.
- **impact**: PHP deprecation warnings logged for every query when stack trace lookup is enabled (non-production). In PHP 9, this will be a fatal error.
- **minimal fix**: Add `protected array $options = [];` to `QueryObserver`, or refactor the trait to use a method like `getTraceOptions()` that the using class can override.
- **test idea**: Enable stack trace lookup in `QueryObserver`, execute a query, check that no deprecation warnings are emitted (`set_error_handler` to capture).

## Finding 6 — `DataSizeUnit::convertValueTo()` formula is inverted

- **severity**: high
- **confidence**: high
- **location**: `src/Enums/DataSizeUnit.php:57-64` — `convertValueTo()`
- **evidence**: When `$this->factor() > $convertToUnit->factor()` (e.g., KILOBYTES→BYTES): `value / 1024 * 1 = value / 1024`. This converts 1 KB to 0.000977 bytes — **wrong**, should be 1024 bytes. When `$this->factor() <= $convertToUnit->factor()` (e.g., BYTES→KILOBYTES): `value * 1 / 1024 = value / 1024`. This converts 1024 bytes to 1 KB — **correct**. So the "larger to smaller" branch is inverted.
- **impact**: Any data size conversion from a larger unit to a smaller unit produces wildly incorrect values. This affects `Measurement::convertTo(DataSizeUnit::KILOBYTES)` called in `QueryObserver::recordQuery()` line 90, which converts bytes to kilobytes for memory display. Since BYTES (factor=1) < KILOBYTES (factor=1024), it hits the correct branch. But if anyone converts KB→BYTES, it would be wrong.
- **minimal fix**: The correct formula for both directions is `value * thisFactor / targetFactor`:
  ```php
  public function convertValueTo(int|float $value, Unit $convertToUnit): int|float
  {
      return $value * $this->factor() / $convertToUnit->factor();
  }
  ```
  Note: `TimeUnit` already uses this formula for the "larger factor" case and is correct.
- **test idea**: `DataSizeUnit::KILOBYTES->convertValueTo(1, DataSizeUnit::BYTES)` — expected: 1024, actual: ~0.000977. `DataSizeUnit::MEGABYTES->convertValueTo(1, DataSizeUnit::KILOBYTES)` — expected: 1024, actual: ~0.000977.

## Finding 7 — `Profiler::setupViewProfiling()` records `BEFORE_VIEW_RENDERING` *after* the first view renders

- **severity**: high
- **confidence**: high
- **location**: `src/Services/ProfilerService/Profiler.php:106-113` — anonymous class `get()` method
- **evidence**: The wrapped `get()` method calls `parent::get($path, $data)` (which renders the view) on line 108, then records `BEFORE_VIEW_RENDERING` on line 112. The checkpoint timestamp is captured *after* the view has already rendered, not before.
- **impact**: The "Controller" stage (which ends at `BEFORE_VIEW_RENDERING`) includes the first view's render time. The "View rendering" stage underreports total view render time. Profiling data shown to the user is inaccurate.
- **minimal fix**: Record the checkpoint *before* calling `parent::get()`:
  ```php
  public function get($path, array $data = [])
  {
      if (empty(Profiler::$viewRenders)) {
          Profiler::record(RequestCheckpointId::BEFORE_VIEW_RENDERING);
      }
      $result = parent::get($path, $data);
      Profiler::$viewRenders[$path] = new RequestCheckpointData;
      return $result;
  }
  ```
- **test idea**: Render a view that takes measurable time (e.g., with a sleep). Compare the `BEFORE_VIEW_RENDERING` checkpoint timestamp against the timestamp before the view call. Currently `BEFORE_VIEW_RENDERING` is ~= view-end time instead of view-start time.

## Finding 8 — `ToolbarServiceProvider` uses `env()` directly, broken with config cache

- **severity**: high
- **confidence**: high
- **location**: `src/ToolbarServiceProvider.php:26-33` — `packageRegistered()`
- **evidence**: `env('LARAVEL_TOOLBAR_ENABLED', true)` and `env('LARAVEL_TOOLBAR_VISIBLE', true)` are called in `packageRegistered()`. After `php artisan config:cache`, `env()` returns `null` for all values.
- **impact**: In production with cached config: `env('LARAVEL_TOOLBAR_ENABLED', true)` returns `null`, `! null` is `true`, so the `if` block executes and **disables the toolbar** — the opposite of the intended default. Conversely, `LARAVEL_TOOLBAR_ENABLED=false` cannot disable the toolbar when config is cached because `env()` returns `null`, `! null` = `true`, so it still disables. Actually wait: `! env('LARAVEL_TOOLBAR_ENABLED', true)` with cached config where `env()` returns `null`: `! null` = `true`, so the toolbar gets disabled. This means **the toolbar silently breaks in any cached config environment**, including staging/production.
- **minimal fix**: Publish a config file (`config/toolbar.php`) with `'enabled' => env('LARAVEL_TOOLBAR_ENABLED', true)` and use `config('toolbar.enabled')` in the service provider.
- **test idea**: Mock `env()` to return `null` (simulating cached config). Call `packageRegistered()`. Assert toolbar is still enabled by default.

## Finding 9 — `QueryObserver::replaceBindings()` ignores `preg_replace()` returning null

- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php:159-164` — `replaceBindings()`
- **evidence**: `preg_replace()` returns `null` on PCRE errors (e.g., backtrack limit exceeded on very long queries). The result is assigned directly to `$sql` with no null check. On the next iteration of the foreach loop, `preg_replace()` receives `null` as `$subject`, which causes a deprecation warning in PHP 8.1+ and incorrect `null` SQL display.
- **impact**: Queries with very complex SQL or many bindings could produce `null` SQL strings. The query will appear with no SQL text in the toolbar.
- **minimal fix**: `$sql = preg_replace(...) ?? $sql;`
- **test idea**: Create a query with enough complexity to hit PCRE backtrack limit. Call `replaceBindings()`. Assert result is not null.

## Finding 10 — `QueryObserver::replaceBindings()` backreference injection via `$` and `\` in bindings

- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php:159-164` — `replaceBindings()`
- **evidence**: String bindings containing `$1`, `$2`, `\1`, etc., are passed as the replacement string to `preg_replace()`. These are interpreted as backreferences. A binding like `"$100 credit"` would produce garbled SQL output because `$1` references capture group 1 from the regex.
- **impact**: Queries with dollar-sign or backslash values in bindings show corrupted SQL in the toolbar. Data display is wrong.
- **minimal fix**: Escape the replacement string: `$binding = preg_quote_replacement($binding)` or use `str_replace('$', '\\$', str_replace('\\', '\\\\', $binding))` before passing to `preg_replace()`. Alternatively, use `preg_replace_callback()` instead.
- **test idea**: Insert a row with `name = '$100 value'`. Check that `replaceBindings()` output contains `'$100 value'` literally, not a backreference substitution.

## Finding 11 — `QueryObserver::quoteStringBinding()` escape order is wrong

- **severity**: medium
- **confidence**: medium
- **location**: `src/Observers/QueryObserver.php:186-192` — `quoteStringBinding()` fallback
- **evidence**: The `strtr()` call maps `'\\' => '\\\\'` along with `'"' => '\\"'` and `"'" => "\\'"`. However, `strtr()` replaces longest match first and does not re-scan replaced text, so the order issue that would exist with sequential `str_replace()` calls does **not** apply to `strtr()`. This is actually correct.
- **impact**: No bug here — `strtr()` handles this correctly. Lower confidence on the prompt's claim.

## Finding 12 — `ModelObserver::recordHydrations()` doesn't accumulate memory correctly

- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/ModelObserver.php:74-78` — `recordHydrations()` else branch
- **evidence**: On subsequent hydrations of the same model class, line 76 calls `$model->memory_used->formatValue()` which just recalculates the formatted string of the *original* memory value — it does not add the new memory delta (`$memoryAfter - $memoryBefore`). The `Measurement` object's `value` is never updated.
- **impact**: Memory usage per model class only reflects the first hydration's memory, regardless of how many models are hydrated. The count increments correctly, but `memory_used` is wrong.
- **minimal fix**:
  ```php
  $model->memory_used->value += ($memoryAfter - $memoryBefore);
  $model->memory_used->formatValue();
  $model->count++;
  ```
- **test idea**: Hydrate 100 instances of a model. Check that `memory_used->value` is roughly 100x a single hydration's memory, not equal to a single hydration.

## Finding 13 — `Profiler::initialize()` stacks duplicate `booting`/`booted` callbacks in Octane

- **severity**: medium
- **confidence**: medium
- **location**: `src/Services/ProfilerService/Profiler.php:79-88` — `initialize()`
- **evidence**: `app()->booting()` and `app()->booted()` register callbacks on the application instance. In Octane, the same application instance handles multiple requests. `Toolbar::__construct()` is called each request (since it's bound as a new instance in `packageRegistered()`), calling `Profiler::initialize()` each time. Each call adds another `booting` and `booted` callback. After N requests, there are N callbacks.
- **impact**: The callbacks re-record the same checkpoint IDs (`BEFORE_SERVICES_PROVIDERS`, `AFTER_SERVICES_PROVIDERS`), and `Profiler::record()` silently drops duplicates, so the *data* is correct. But `setupViewProfiling()` is called N times, re-registering the blade engine wrapper each time. This likely has no visible effect since the engine resolver overwrites, but it's wasteful and could cause issues if the resolver stores multiple registrations.
- **minimal fix**: Add a static flag: `private static bool $initialized = false;` and check it before registering callbacks. Reset it in `initialize()` only when the app instance changes.
- **test idea**: Call `Profiler::initialize()` 3 times. Check that `app()->booted()` callback count doesn't triple.

## Finding 14 — `Toolbar::$enabled` and `$visible` static state leaks across Octane requests

- **severity**: medium
- **confidence**: high
- **location**: `src/Toolbar.php:10-11` — static `$enabled` and `$visible`
- **evidence**: `Toolbar::$enabled` and `$visible` are static properties that persist across all requests in an Octane worker. If user code calls `Toolbar::disable()` or `Toolbar::hide()` conditionally for one request (e.g., for a health check endpoint), all subsequent requests in that worker will have the toolbar disabled/hidden.
- **impact**: Intermittent toolbar disappearance in Octane. Hard to debug because it depends on which request the worker processed previously.
- **minimal fix**: Reset these in `Profiler::initialize()` or at the start of `Toolbar::__construct()`:
  ```php
  public function __construct()
  {
      self::$enabled = true;
      self::$visible = true;
      // ...
  }
  ```
- **test idea**: In Octane context, call `Toolbar::disable()` then create new `Toolbar`. Assert `Toolbar::$enabled` is `true`.

## Finding 15 — `QueryData::setType()` session detection only works for PostgreSQL/SQLite

- **severity**: medium
- **confidence**: high
- **location**: `src/Data/QueryData.php:39-44` — `setType()`
- **evidence**: The session query detection checks for double-quoted identifiers (`"sessions"`, `"id"`, `"payload"`). MySQL uses backticks (`` `sessions` ``), SQL Server uses brackets (`[sessions]`). Only PostgreSQL and SQLite use double-quote identifiers by default.
- **impact**: MySQL users (the majority of Laravel installations) never see session queries identified. The session badge doesn't appear for their queries.
- **minimal fix**: Check for multiple quoting styles:
  ```php
  if (preg_match('/(?:select \* from|update|delete from)\s+[`"\[]?sessions[`"\]]?\s/i', $this->sql)) {
      $this->type = QueryType::SESSION;
  }
  ```
- **test idea**: Create a `QueryData` with MySQL-style SQL: `` "select * from `sessions` where `id` = ?" ``. Assert `$queryData->type === QueryType::SESSION`.

## Finding 16 — `RoutingObserver` and `RequestObserver` missing `reset()` method for Octane

- **severity**: medium
- **confidence**: medium
- **location**: `src/Observers/RoutingObserver.php`, `src/Observers/RequestObserver.php`
- **evidence**: Both observers register event listeners in their constructor but have no `reset()` method. Other observers (`QueryObserver`, `ModelObserver`) implement `reset()` for Octane compatibility. While these observers don't accumulate state themselves (they delegate to `Profiler`), the event listener registrations happen in the constructor. If these observers are re-instantiated per request (via `ToolbarConfig`'s constructor in `Toolbar::__construct()`), they add duplicate event listeners.
- **impact**: In Octane, duplicate event listeners fire for `Routing`, `RouteMatched`, and `RequestHandled` events. Since `Profiler::record()` drops duplicate checkpoint IDs, the data is correct, but `RequestObserver` creates a new `ToolbarInjector` and calls `inject()` — meaning the toolbar injection runs multiple times per request.
- **minimal fix**: Either add `reset()` methods (even if empty, for consistency) or guard against double-registration. More importantly, `RequestObserver`'s listener should be idempotent or guarded.
- **test idea**: After 3 Octane request cycles, trigger a `RequestHandled` event. Count how many times `ToolbarInjector::inject()` is called. Should be 1, not 3.

## Finding 17 — `ResponseCollector` calls `getContent()` which can return `false`

- **severity**: medium
- **confidence**: medium
- **location**: `src/Collectors/ResponseCollector.php:33` — `collectData()`
- **evidence**: `$collectorManager->response->getContent()` can return `false` for some Symfony response types (e.g., `StreamedResponse`). `strlen(false)` emits a deprecation warning in PHP 8.1+ and returns 0. However, the `CollectorManager` type-hints the response as `Response|JsonResponse|RedirectResponse|null`, and `StreamedResponse` wouldn't match, so this is partially guarded by the type system. `RedirectResponse` extends `Response` and its `getContent()` can return `false` if content was never set.
- **impact**: PHP deprecation warning logged. Minor.
- **minimal fix**: `strlen((string) $collectorManager->response->getContent())` or `strlen($collectorManager->response->getContent() ?: '')`
- **test idea**: Create a `RedirectResponse` with no content set. Pass to `ResponseCollector::collectData()`. Assert no deprecation warning.

## Finding 18 — CSP nonce check in JavaScript is always truthy

- **severity**: low
- **confidence**: high
- **location**: `src/ToolbarInjector.php:280` — inline JavaScript
- **evidence**: `if("{$nonce}" !== null)` — `$nonce` is a PHP string or null. When null, this renders as `if("" !== null)` in JavaScript. The string `""` is not `null`, so the condition is always true. The style-stripping regex always runs, even when there's no CSP policy.
- **impact**: The regex `cached.replace(/\s*style="[^"]*"/g, '')` always strips inline styles from cached HTML. This is likely harmless since Vue re-adds them during hydration, but it adds unnecessary processing.
- **minimal fix**: Change to `if("{$nonce}" !== "")` or better yet, conditionally include the entire `if` block from PHP:
  ```php
  $styleStrip = $nonce ? "cached = cached.replace(/\\s*style=\"[^\"]*\"/g, '');" : '';
  ```
- **test idea**: Render toolbar HTML without CSP nonce. Inspect the generated JavaScript. The condition should not strip styles unnecessarily.

## Finding 19 — `AssetController` potential path traversal

- **severity**: low
- **confidence**: medium
- **location**: `src/Controllers/AssetController.php:15` — `__invoke()`
- **evidence**: `$asset` comes from the URL route parameter. While Laravel's default route regex `[^/]+` prevents `/` in the parameter, `..` is still allowed. The path `__DIR__.'/../../build/assets/'.'..%2F..%2Fsrc%2Fsome-file.php'` is decoded by the web server before Laravel sees it. A value like `../../src/ToolbarServiceProvider.php` would resolve to `src/ToolbarServiceProvider.php` and serve its contents. However, the route parameter typically doesn't allow `/`, so `../..` contains `/` and would not match the route. The risk depends on the route regex definition.
- **impact**: Low risk due to Laravel's default route constraints, but defense-in-depth is missing.
- **minimal fix**: Add `basename()` to ensure only the filename is used: `$asset = basename($asset);`
- **test idea**: Request `/_toolbar/../../composer.json`. Verify 404, not file contents.

## Finding 20 — `HorizonController` PHP binary fallback assumes macOS Herd

- **severity**: low
- **confidence**: high
- **location**: `src/Controllers/HorizonController.php:48-49` — `start()` and line 82-83 in `stop()`
- **evidence**: When `which php` returns empty, the fallback path is `$_SERVER['HOME'].'/Library/Application Support/Herd/bin/php'` — a macOS-only path. On Linux (Docker, VMs, servers), this path doesn't exist. The result is `popen()` receiving a non-existent binary path, failing silently.
- **impact**: Horizon start/stop doesn't work on Linux when `which php` fails (e.g., in some Docker containers where `which` isn't installed).
- **minimal fix**: Try multiple fallback paths, or use `PHP_BINARY` constant:
  ```php
  $php = trim(shell_exec('which php') ?? '') ?: PHP_BINARY;
  ```
- **test idea**: Mock `shell_exec('which php')` to return `null` on Linux. Assert that `PHP_BINARY` is used instead.

## Finding 21 — `Profiler::getRequestStages()` is destructive — calling it twice returns empty data

- **severity**: low
- **confidence**: high
- **location**: `src/Services/ProfilerService/Profiler.php:198-202` — `getRequestStages()`
- **evidence**: After building the return array, lines 199-202 clear all static state. Any second call to `getRequestStages()` within the same request returns empty stages.
- **impact**: If any code path calls `getRequestStages()` twice (e.g., if both HTML injection and Inertia header injection somehow fire), the second call gets empty profiler data. Currently `ProfilerCollector::collectData()` is the only caller, and `CollectorManager::collectData()` is called once, so this is a latent bug.
- **minimal fix**: Document this as intentional, or separate the "get" and "clear" operations.
- **test idea**: Call `Profiler::getRequestStages()` twice. Assert second call returns non-empty data (currently fails).

## Finding 22 — `ModelObserver::given()` method is dead code

- **severity**: low
- **confidence**: high
- **location**: `src/Observers/ModelObserver.php:83-97` — `given()`
- **evidence**: Grep for `given(` across the codebase finds no callers.
- **impact**: Dead code adds maintenance burden, no functional impact.
- **minimal fix**: Remove the method.
- **test idea**: N/A — code deletion.

## Finding 23 — Frontend: `handleToolbarHeader` can throw and reject a successful fetch

- **severity**: low
- **confidence**: medium
- **location**: `resources/js/core/interceptors.ts:22` — `setupFetchInterceptor()`
- **evidence**: Line 22 calls `handleToolbarHeader(response)` *after* the await. If `handleToolbarHeader` throws (e.g., malformed base64 despite the try/catch — perhaps on an unexpected header encoding), the promise would reject even though the fetch itself succeeded. However, `handleToolbarHeader` has a try/catch around the decode/parse (lines 51-66), so only an unexpected throw from `response.headers.get()` could escape. Very unlikely.
- **impact**: Extremely unlikely — the try/catch in `handleToolbarHeader` covers the risky operations.

## Summary by Severity

| Severity | Count | Key Findings |
|----------|-------|------|
| Critical | 3 | `collectors(null)` TypeError, `QueriesCollector` null deref, `ModelsCollector` null deref |
| High | 5 | Profiler exceptions crash requests, `FetchesStackTrace` undefined property, `DataSizeUnit` inverted formula, view profiling timing wrong, `env()` vs `config()` |
| Medium | 8 | `preg_replace` null, backreference injection, memory accumulation, Octane state leaks, session detection, missing reset(), `getContent()` false |
| Low | 5 | CSP nonce logic, path traversal, Herd fallback, destructive `getRequestStages`, dead code |
