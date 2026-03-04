Here is a systematic correctness audit of the `laravel-toolbar` package, prioritized by severity.

### CRITICAL

**1. Severe State Leak in Octane Environments**
- **severity**: critical
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php` and `src/Observers/ModelObserver.php` — class instances
- **evidence**: Both observers maintain state arrays (e.g., `$this->queries = []`, `$this->hydrationEntries = []`) and explicitly provide a `reset()` method documented as "critical for long-running processes like Laravel Octane". However, a codebase search reveals that `->reset()` is *never called anywhere* in the package's source code. `ToolbarServiceProvider` registers the `Toolbar` instance as a singleton per Octane worker.
- **impact**: In long-running environments like Octane or RoadRunner, data completely bleeds between requests. Request 2 will display Request 1's queries and models. Eventually, the worker will crash due to Out-Of-Memory (OOM) errors as the arrays grow indefinitely over thousands of requests.
- **minimal fix**: Bind an event listener to Laravel's `RequestHandled` or `RequestReceived` events to call `$toolbar->config->getObserver(QueryObserver::class)?->reset();` at the start or end of the request cycle.
- **test idea**: In an Octane simulation, resolve `Toolbar` from the container, fire a `QueryExecuted` event, and simulate a second request cycle by firing a new `QueryExecuted` event. Assert that the second request's collected queries array has exactly 1 element, not 2.

### HIGH

**2. Null Dereferences in Collectors when Observers are Omitted**
- **severity**: high
- **confidence**: high
- **location**: `src/Collectors/QueriesCollector.php:53` and `src/Collectors/ModelsCollector.php:35` — `setEntries()`
- **evidence**: The collectors fetch their respective observers using `$toolbar->config->getObserver()`, which is nullable if a user customizes their `observers` array to omit them. The code immediately dereferences the result without a null check: `$this->totalTime = $queryObserver->totalTime;`.
- **impact**: If a user disables `QueryObserver` or `ModelObserver` in the config but leaves the collectors enabled, the application crashes entirely on every request with a fatal `Attempt to read property on null` error.
- **minimal fix**: Add an early return guard before property access: `if (! $queryObserver) { return; }`.
- **test idea**: Configure `ToolbarConfig` with an empty array for `observers()`, then execute `QueriesCollector::collectData()`. Assert it returns an empty `QueriesData` object rather than throwing an exception.

**3. Uncaught Profiler Exceptions Crashing Requests**
- **severity**: high
- **confidence**: high
- **location**: `src/Collectors/ProfilerCollector.php:171-176` — `getTotals()`
- **evidence**: The profiler throws raw `\Exception` objects when it detects "Wall time overlap" or "Wall time gap" (e.g., `$differenceFromStart... > $totalWallTime`). Because `CollectorManager::collectData()` executes directly within the request pipeline and does not wrap collector calls in a `try-catch` block, these exceptions propagate upward.
- **impact**: Microsecond imprecision in process scheduling can trigger these discrepancy checks, converting what should be a minor dev-tool timing inconsistency into a full 500 Server Error that kills the user's application request.
- **minimal fix**: Remove the `throw new \Exception(...)` calls. Instead, cap the overlapping values using `max()` or `min()` boundaries, or write the discrepancy to a debug metadata field.
- **test idea**: Mock `Profiler::getRequestStages()` to return two stages where the mathematical sum of durations is slightly less than the start-to-end difference. Assert `collectData()` succeeds and does not throw.

**4. `DataSizeUnit` Inverted Conversion Formula**
- **severity**: high
- **confidence**: high
- **location**: `src/Enums/DataSizeUnit.php:57-64` — `convertValueTo()`
- **evidence**: When `$this->factor() > $convertToUnit->factor()` (e.g., converting Megabytes to Bytes), the formula uses `$value / $this->factor() * $convertToUnit->factor()`. Mathematically, `1 MB` to `BYTES` results in `1 / 1048576 * 1`, returning `0.000000953` bytes instead of the expected `1048576` bytes. This is mathematically inverted compared to the correct logic used in `TimeUnit.php`.
- **impact**: Any memory unit conversions from a larger unit to a smaller unit calculate wildly incorrect fractional values, rendering memory profiling displays untrustworthy.
- **minimal fix**: Change the formula to multiply before dividing: `return $value * $this->factor() / $convertToUnit->factor();`.
- **test idea**: Execute `DataSizeUnit::MEGABYTES->convertValueTo(1, DataSizeUnit::BYTES)`. Assert the output is exactly `1048576`.

**5. `TypeError` on invalid UTF-8 during HTML Injection**
- **severity**: high
- **confidence**: high
- **location**: `src/ToolbarInjector.php:140` and `212` — `injectToolbarHtml()`
- **evidence**: The data is encoded using `json_encode($data, JSON_HEX_TAG | ...)`. If `$data` contains invalid UTF-8 sequences (extremely common in raw SQL query bindings or binary response bodies), `json_encode` quietly returns `false`. This boolean is directly passed into `$this->getToolbarHtml(string $data)`, violating strict type hints.
- **impact**: Requests that interact with binary data or invalid characters in the database will crash during the final injection phase with a `TypeError: getToolbarHtml(): Argument #1 ($data) must be of type string, bool given`.
- **minimal fix**: Add the `JSON_INVALID_UTF8_SUBSTITUTE` flag to `json_encode` to gracefully handle bad characters.
- **test idea**: Mock `$data` to include the binary string `"\xB1\x31"`. Trigger `injectToolbarHtml()`. Assert injection finishes successfully with substituted characters and does not throw a TypeError.

### MEDIUM

**6. `ModelObserver` Memory Leak / Wrong Accumulation**
- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/ModelObserver.php:74-78` — `recordHydrations()`
- **evidence**: On subsequent hydrations of the same model class, the `else` block executes `$model->memory_used->formatValue();` and `$model->count++;`. It completely ignores the new `$memoryAfter - $memoryBefore` delta, failing to accumulate the memory.
- **impact**: The total memory displayed for a model class only reflects the memory consumed by its *first* hydration, vastly under-reporting actual usage for models hydrated in large loops.
- **minimal fix**: Add the delta to the existing measurement: `$model->memory_used->value += ($memoryAfter - $memoryBefore); $model->memory_used->formatValue();`.
- **test idea**: Fire `eloquent.retrieved` twice for the same model. Assert the `memory_used->value` equals the combined delta of both events.

**7. `preg_replace` Backreference Injection in SQL Bindings**
- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php:159-164` — `replaceBindings()`
- **evidence**: `$sql = preg_replace($regex, $binding, $sql, ...)` uses user-provided `$binding` as the replacement string. If a user's SQL binding contains literal strings like `"$1"` or `"\\1"`, PHP's PCRE engine interprets them as backreferences.
- **impact**: Legitimate SQL bindings containing `$` characters will corrupt the rendered SQL shown in the toolbar, silently failing to replace or inserting empty strings.
- **minimal fix**: Escape backreferences in the replacement string: `preg_replace($regex, addcslashes((string)$binding, '\\$'), $sql, ...)`.
- **test idea**: Record a query where a binding contains the string `"$100"`. Assert that `replaceBindings()` outputs exactly `"$100"` literally and does not corrupt the output.

**8. `ToolbarConfig::collectors(null)` Throws TypeError**
- **severity**: medium
- **confidence**: high
- **location**: `src/Data/ToolbarConfig.php:156-168` — `collectors()`
- **evidence**: If `null` is passed, line 158 correctly initializes `$this->collectors = []`, but execution falls through to the `foreach` loop, which tries to iterate over the `null` parameter.
- **impact**: If a user attempts to disable all collectors in their config by chaining `->collectors(null)`, the application will crash during boot with a PHP 8 `TypeError`.
- **minimal fix**: Add an early return: `if (empty($collectors)) { $this->collectors = []; return $this; }`.
- **test idea**: Call `(new ToolbarConfig)->collectors(null)` and assert it does not throw an exception.

**9. `QueryData` Session Detection is PostgreSQL-specific**
- **severity**: medium
- **confidence**: high
- **location**: `src/Data/QueryData.php:39-44` — `setType()`
- **evidence**: The string matching `str_contains($this->sql, 'from "sessions"')` hardcodes double-quote table identifiers. MySQL uses backticks (`` `sessions` ``).
- **impact**: Session queries are completely missed and uncategorized for any developer using MySQL, the most popular database for Laravel.
- **minimal fix**: Use a regex capable of matching multiple SQL dialects: `preg_match('/from ["`]?sessions["`]?/i', $this->sql)`.
- **test idea**: Instantiate `QueryData` with MySQL syntax: ``select * from `sessions` where `id` = ?``. Assert `$query->type` correctly maps to `QueryType::SESSION`.

**10. `FetchesStackTrace` Accesses Undefined Property**
- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/FetchesStackTrace.php:34,46` and `src/Observers/QueryObserver.php`
- **evidence**: The `FetchesStackTrace` trait accesses `$this->options['ignore_paths']`. However, `QueryObserver` uses this trait but never declares an `$options` array.
- **impact**: Emits a PHP "Undefined property" warning on *every single database query* executed when stack trace collection is active, flooding strict error logs.
- **minimal fix**: Declare `public array $options = [];` on the `QueryObserver` class.
- **test idea**: Execute a DB query with `QueryObserver` active and a custom error handler attached. Assert no "Undefined property" warnings are raised.

**11. `ToolbarServiceProvider` Uses `env()` Ignoring Cached Configurations**
- **severity**: medium
- **confidence**: high
- **location**: `src/ToolbarServiceProvider.php:26-33` — `packageRegistered()`
- **evidence**: `if (! env('LARAVEL_TOOLBAR_ENABLED', true))` is evaluated directly instead of leveraging Laravel's config repository.
- **impact**: If a user explicitly sets `LARAVEL_TOOLBAR_ENABLED=false` in `.env` to disable the toolbar, but then runs `php artisan config:cache` (common in staging/testing), `env()` will bypass `.env` and return the default `true`. The toolbar becomes permanently and un-disably active in cached environments.
- **minimal fix**: Utilize Laravel's config helper: `config('toolbar.enabled')`.
- **test idea**: Mock a cached configuration state where the environment array is empty but a config setting specifies it should be disabled. Assert the toolbar does not mount itself.

### LOW

**12. `HorizonController` Unauthenticated Access & macOS-Hardcoded Paths**
- **severity**: low
- **confidence**: high
- **location**: `src/Controllers/HorizonController.php:28,48-49`
- **evidence**: `guardEnvironment()` checks only if the app is in `local`/`development`. Furthermore, when `which php` fails, it falls back to `$_SERVER['HOME'].'/Library/Application Support/Herd/bin/php'`, a hardcoded macOS path.
- **impact**: Any user on the same local network can manipulate background queues without auth. If the developer runs Docker/Linux locally, attempting to start Horizon via the toolbar silently fails because the fallback path does not exist.
- **minimal fix**: Add authorization gate checks, and use PHP's native `PHP_BINARY` constant instead of arbitrary OS fallbacks.
- **test idea**: Mock a Linux environment where `shell_exec('which php')` returns empty. Attempt to hit `/horizon/start` and assert it falls back to `PHP_BINARY`.
