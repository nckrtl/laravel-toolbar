### CRITICAL

**1. Severe State Leak and Unbounded Memory Growth in Octane Environments**
- **status**: refined (combines missing `reset()` calls and `Profiler` state clearing gaps)
- **severity**: critical
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php`, `src/Observers/ModelObserver.php`, `src/Services/ProfilerService/Profiler.php:198`, `src/Toolbar.php:10`
- **evidence**: The package maintains state across requests in several ways: 1) `QueryObserver` and `ModelObserver` have `reset()` methods, but they are *never called* anywhere in the package source. 2) `Profiler` clears its static arrays (`$requestCheckpoints`, etc.) only inside `getRequestStages()`. However, if `ToolbarInjector::shouldInjectHtml()` returns `false` (e.g., on JSON/AJAX requests), `getRequestStages()` is skipped, leaving the data in memory. 3) `Toolbar::$enabled` and `$visible` are static and persist across requests.
- **impact**: In long-running workers (Octane/FrankenPHP), data bleeds between requests. Request B will expose Request A's queries, models, and checkpoints. Over thousands of requests, the arrays grow indefinitely, leading to Out-Of-Memory (OOM) worker crashes. Furthermore, `Toolbar::disable()` in one request permanently disables the toolbar for all subsequent requests on that worker.
- **minimal fix**: Implement a terminating middleware or register an event listener (e.g., `RequestTerminated`) that explicitly resets static `Toolbar` state, calls `Profiler::clearState()`, and calls `->reset()` on all configured observers, regardless of whether injection occurred.
- **test idea**: In an Octane simulation, process an AJAX request (no injection) that fires a `QueryExecuted` event. Process a second HTML request. Assert the second request's injected HTML does not contain the first request's query.

**2. Null Dereference in Collectors when Observers are Omitted**
- **status**: confirmed
- **severity**: critical
- **confidence**: high
- **location**: `src/Collectors/QueriesCollector.php:53`, `src/Collectors/ModelsCollector.php:35`
- **evidence**: Both collectors resolve their respective observers via `$toolbar->config->getObserver()`, which is nullable if the user customizes the `observers` array to exclude them. The collectors immediately dereference the result without checking: `$this->totalTime = $queryObserver->totalTime;`.
- **impact**: If a user disables `QueryObserver` or `ModelObserver` in the config but leaves the collectors enabled, the application crashes entirely on every request with a fatal `Attempt to read property on null` error.
- **minimal fix**: Add an early return guard before property access: `if (! $queryObserver) { return; }`.
- **test idea**: Configure `ToolbarConfig` with `observers([])`, then execute `QueriesCollector::collectData()`. Assert it returns an empty `QueriesData` object rather than throwing an error.

**3. `ToolbarConfig` Throws TypeError on Null Overrides**
- **status**: refined (applies to both `collectors()` and `observers()`)
- **severity**: critical
- **confidence**: high
- **location**: `src/Data/ToolbarConfig.php:140, 156`
- **evidence**: The methods `collectors(?array $collectors = null)` and `observers(?array $observers = null)` are broken. `collectors()` checks for null, sets `$this->collectors = []`, but then falls through to `foreach ($collectors as $collector)` — attempting to iterate over the `null` parameter. `observers()` directly assigns `null` to the typed `array $observers` property.
- **impact**: If a user attempts to clear collectors or observers using `$config->collectors(null)`, the application crashes during boot with a PHP 8 `TypeError`.
- **minimal fix**: Normalize the parameter and return early:
  ```php
  if (empty($collectors)) { $this->collectors = []; return $this; }
  ```
- **test idea**: Call `(new ToolbarConfig)->collectors(null)->observers(null)` and assert no exceptions are thrown and both properties are empty arrays.

### HIGH

**4. Uncaught Profiler Exceptions Crash Requests**
- **status**: confirmed
- **severity**: high
- **confidence**: high
- **location**: `src/Collectors/ProfilerCollector.php:171-176, 198`
- **evidence**: The profiler throws raw `\Exception` objects when it detects "Wall time overlap" or "Wall time gap" (e.g., calculated substage durations exceeding total wall time). `CollectorManager::collectData()` does not wrap collector calls in a `try-catch` block, so these exceptions propagate to the framework.
- **impact**: Microsecond imprecision in process scheduling (especially under load) triggers these discrepancy checks, converting a minor dev-tool timing inconsistency into a 500 Server Error that kills the user's application request.
- **minimal fix**: Remove the `throw new \Exception(...)` calls. Instead, cap overlapping values using `min()`, or append the discrepancy to a debug metadata array on the `ProfilerData` object.
- **test idea**: Mock `Profiler::getRequestStages()` to return stages with slightly overlapping timestamps due to floating-point drift. Assert `collectData()` succeeds and does not throw.

**5. `DataSizeUnit` and `TimeUnit` Conversion Formulas are Mathematically Incorrect**
- **status**: refined (TimeUnit reverse-path also confirmed broken)
- **severity**: high
- **confidence**: high
- **location**: `src/Enums/DataSizeUnit.php:57-64`, `src/Enums/TimeUnit.php:66`
- **evidence**: In `DataSizeUnit`, converting larger to smaller units (e.g., Megabytes to Bytes) executes `$value / $this->factor() * $convertToUnit->factor()`. For 1 MB -> Bytes, this evaluates to `1 / 1048576 * 1`, returning `0.000000953` bytes instead of `1048576`. In `TimeUnit`, the reverse is true for some paths.
- **impact**: Any memory or time unit conversions traversing the broken branch calculate wildly incorrect fractional values, rendering performance metrics untrustworthy.
- **minimal fix**: Use a unified, mathematically sound formula for all conversions: `return $value * $this->factor() / $convertToUnit->factor();`.
- **test idea**: Execute `DataSizeUnit::MEGABYTES->convertValueTo(1, DataSizeUnit::BYTES)`. Assert the output is exactly `1048576`.

**6. `ToolbarServiceProvider` Uses `env()` Ignoring Config Cache**
- **status**: confirmed
- **severity**: high
- **confidence**: high
- **location**: `src/ToolbarServiceProvider.php:26-33`
- **evidence**: Evaluates `if (! env('LARAVEL_TOOLBAR_ENABLED', true))` directly instead of leveraging Laravel's config repository.
- **impact**: In environments where `php artisan config:cache` is executed (staging/production), `env()` returns `null`. The condition evaluates to `!null` (true), permanently disabling the toolbar regardless of the actual cached config value. Or conversely, if default is false, it forces the opposite behavior. It silently breaks the toolbar in cached environments.
- **minimal fix**: Utilize Laravel's config helper: `config('toolbar.enabled')`.
- **test idea**: Mock a cached configuration state where `env()` returns null but the config repository contains `toolbar.enabled = true`. Assert the toolbar service provider registers the package properly.

**7. Invalid UTF-8 Payload Crashes Response Injection via TypeError**
- **status**: refined
- **severity**: high
- **confidence**: high
- **location**: `src/ToolbarInjector.php:140, 212`
- **evidence**: `$data = json_encode($data, JSON_HEX_TAG | ...)`. If `$data` contains invalid UTF-8 sequences (common in raw SQL bindings or dumped objects), `json_encode` quietly returns `false`. This boolean is passed to `getToolbarHtml(string $data)`, violating the strict `string` type hint.
- **impact**: Requests containing binary data or malformed characters in queries/models will crash during the final response injection phase with a `TypeError`.
- **minimal fix**: Add the `JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR` flags to `json_encode`, with a fallback `try/catch`.
- **test idea**: Inject a mock `QueryData` containing the binary string `"\xB1\x31"`. Trigger `injectToolbarHtml()`. Assert injection completes successfully without throwing a TypeError.

**8. View Profiling Timestamp Recorded After View Render**
- **status**: confirmed
- **severity**: high
- **confidence**: high
- **location**: `src/Services/ProfilerService/Profiler.php:106-113`
- **evidence**: Inside the wrapped view engine's `get()` method, `parent::get($path, $data)` is called *before* `Profiler::record(RequestCheckpointId::BEFORE_VIEW_RENDERING)`. The "before" timestamp is captured after the view has already finished rendering.
- **impact**: The "Controller" stage (which ends at `BEFORE_VIEW_RENDERING`) incorrectly absorbs the entire render time of the first view. The "View rendering" stage underreports time.
- **minimal fix**: Move the `Profiler::record()` call above `parent::get()`.
- **test idea**: Render a view containing a `sleep(1)`. Compare the `BEFORE_VIEW_RENDERING` checkpoint timestamp against the timestamp taken right before the view call.

**9. Horizon Controller Unauthenticated Access & macOS-Hardcoded Paths**
- **status**: confirmed
- **severity**: high
- **confidence**: high
- **location**: `src/Controllers/HorizonController.php:28, 48-49`
- **evidence**: The routes lack authorization middleware. `guardEnvironment()` only restricts access to `local`/`development` environments. Furthermore, if `which php` fails, it falls back to `$_SERVER['HOME'].'/Library/Application Support/Herd/bin/php'` — a macOS Herd path.
- **impact**: Any user on the local network can manipulate background queues. On Linux/Docker environments, Horizon silently fails to start from the toolbar because the macOS fallback path does not exist.
- **minimal fix**: Add an authorization gate (e.g., `Gate::allows('viewToolbar')`) and use PHP's native `PHP_BINARY` constant instead of arbitrary OS fallbacks.
- **test idea**: Mock a Linux environment where `shell_exec('which php')` returns empty. Hit `/_toolbar/horizon/start` and assert it utilizes `PHP_BINARY`.

### MEDIUM

**10. Model and Query Observer Memory Metrics Accumulation Bugs**
- **status**: refined
- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/ModelObserver.php:74-78`, `src/Observers/QueryObserver.php:56`
- **evidence**: On subsequent hydrations of the same model class, `ModelObserver` calls `$model->memory_used->formatValue()`, completely discarding the new memory delta (`$memoryAfter - $memoryBefore`). `QueryObserver` fails to update `$this->currentMemory` after each query.
- **impact**: Memory usage per model class heavily under-reports usage in large loops, reflecting only the memory cost of the first hydration. Query memory deltas are skewed.
- **minimal fix**: For models, accumulate the delta: `$model->memory_used->value += ($memoryAfter - $memoryBefore);`. For queries, update the baseline.
- **test idea**: Fire `eloquent.retrieved` twice for the same model. Assert the `memory_used->value` equals the combined delta of both events.

**11. `preg_replace` Backreference Injection in SQL Bindings**
- **status**: confirmed
- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php:159-164`
- **evidence**: `preg_replace($regex, $binding, $sql, ...)` uses user-provided `$binding` as the replacement string. If a string binding contains `"$1"` or `"\\1"`, PHP's PCRE engine evaluates it as a regex backreference.
- **impact**: Valid SQL bindings containing `$` characters corrupt the rendered SQL shown in the toolbar.
- **minimal fix**: Escape backreferences in the replacement string: `preg_replace($regex, addcslashes((string)$binding, '\\$'), $sql, ...)`, or use `preg_replace_callback`.
- **test idea**: Record a query where a binding contains the string `"$100"`. Assert `replaceBindings()` outputs exactly `"$100"`.

**12. Session Query Detection is PostgreSQL/SQLite Specific**
- **status**: confirmed
- **severity**: medium
- **confidence**: high
- **location**: `src/Data/QueryData.php:39-44`
- **evidence**: `str_contains($this->sql, 'from "sessions"')` hardcodes double-quote table identifiers. MySQL wraps identifiers in backticks (`` `sessions` ``).
- **impact**: Session queries are completely missed and uncategorized for MySQL databases.
- **minimal fix**: Use a regex capable of matching multiple SQL dialects: `preg_match('/from ["`]?sessions["`]?/i', $this->sql)`.
- **test idea**: Instantiate `QueryData` with MySQL syntax: ``select * from `sessions` where `id` = ?``. Assert `$query->type === QueryType::SESSION`.

### LOW

**13. CSP Nonce Check in JavaScript is Always Truthy**
- **status**: confirmed
- **severity**: low
- **confidence**: high
- **location**: `src/ToolbarInjector.php:280`
- **evidence**: The injected inline script contains `if("{$nonce}" !== null)`. When `$nonce` is empty, this renders as `if("" !== null)`, which is always true.
- **impact**: The regex that strips inline styles from cached HTML runs unconditionally, even when CSP nonces are not being utilized.
- **minimal fix**: Change the condition to check for truthiness or emit a boolean from PHP: `if("{$nonce}")`.
- **test idea**: Render the toolbar HTML without a CSP nonce. Inspect the generated JS and ensure styles aren't stripped unnecessarily.

**14. `FetchesStackTrace` Accesses Undefined Property**
- **status**: refined (downgraded to low impact based on language semantics)
- **severity**: low
- **confidence**: high
- **location**: `src/Observers/FetchesStackTrace.php:34,46`
- **evidence**: The trait accesses `$this->options['ignore_paths']`. However, `QueryObserver` uses this trait but never declares an `$options` property.
- **impact**: Emits a PHP "Undefined property" deprecation warning on database queries when stack trace collection is active (PHP 8.2+).
- **minimal fix**: Declare `protected array $options = [];` on `QueryObserver`.
- **test idea**: Execute a query with a custom error handler attached. Assert no "Undefined property" deprecation notices are triggered.
