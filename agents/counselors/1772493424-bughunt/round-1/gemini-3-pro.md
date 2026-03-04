Here is the systematic correctness audit of the `laravel-toolbar` package, prioritized by severity.

### CRITICAL

**1. Uncaught exceptions in ProfilerCollector crashing requests**
- **severity**: critical
- **confidence**: high
- **location**: `src/Collectors/ProfilerCollector.php` - `getTotals()` and `fillInMissingStartAndEnd()`
- **evidence**: Throws `Exception` when encountering edge cases like "Wall time overlap detected" and "No next stage with a end time found". 
- **impact**: Since this runs in the request lifecycle during `collectData()`, slight process scheduling anomalies or missing checkpoints will trigger an uncaught exception, resulting in a 500 server error for the user's application.
- **minimal fix**: Remove the `throw new \Exception(...)` calls. Instead, cap overlapping values to the total wall time and provide safe defaults (like setting the end time to the current time) when a checkpoint is missing.
- **test idea**: Mock `Profiler::getRequestStages()` to return stages with overlapping timestamps (e.g., stage 2 ends after stage 3 starts). Assert `collectData()` completes successfully without throwing an exception.

### HIGH

**2. DataSizeUnit inverted conversion formula**
- **severity**: high
- **confidence**: high
- **location**: `src/Enums/DataSizeUnit.php` - `convertValueTo()`
- **evidence**: `if ($this->factor() > $convertToUnit->factor()) { return $value / $this->factor() * $convertToUnit->factor(); }` When converting MEGABYTES to BYTES, it divides by 1024^2 instead of multiplying, which is mathematically inverted.
- **impact**: Memory sizes are calculated and displayed completely backwards (e.g., 1 MB becomes a fraction of a byte), severely confusing users about their application's memory consumption.
- **minimal fix**: Swap the operators to match the correct logic in `TimeUnit.php`: `return $value * $this->factor() / $convertToUnit->factor();`
- **test idea**: Call `DataSizeUnit::MEGABYTES->convertValueTo(1, DataSizeUnit::BYTES)`. Assert the result is exactly `1048576` and not `0.000000953`.

**3. ModelObserver fails to accumulate memory correctly**
- **severity**: high
- **confidence**: high
- **location**: `src/Observers/ModelObserver.php` - `recordHydrations()`
- **evidence**: On subsequent hydrations, the code calls `$model->memory_used->formatValue(); $model->count++;` but never mathematically adds the new `$memoryAfter - $memoryBefore` delta to the measurement.
- **impact**: The total memory usage shown for a specific model class only reflects the memory cost of its very first hydration, heavily under-reporting the true cumulative memory usage.
- **minimal fix**: Accumulate the memory explicitly: `$model->memory_used = new Measurement($model->memory_used->value + ($memoryAfter - $memoryBefore), DataSizeUnit::BYTES);`
- **test idea**: Fire `eloquent.retrieved` twice for the same model. Assert that `hydrationEntries[ModelClass]->memory_used->value` equals the sum of both memory deltas, not just the first one.

**4. Null dereferences in Collectors when observers are omitted**
- **severity**: high
- **confidence**: high
- **location**: `src/Collectors/QueriesCollector.php` - `setEntries()` (and `ModelsCollector.php`)
- **evidence**: Instantiates `$queryObserver = $toolbar->config->getObserver(QueryObserver::class);` and immediately accesses `$this->totalTime = $queryObserver->totalTime;`.
- **impact**: If the user disables or removes the `QueryObserver` or `ModelObserver` from the toolbar config, the application crashes with a null dereference error on every request.
- **minimal fix**: Add an early return guard: `if (! $queryObserver) return;` before accessing its properties.
- **test idea**: Configure `ToolbarConfig` with an empty array of observers, then call `collectData()` on `QueriesCollector`. Assert it returns empty data instead of crashing.

**5. ToolbarConfig iteration over null throws TypeError**
- **severity**: high
- **confidence**: high
- **location**: `src/Data/ToolbarConfig.php` - `collectors()`
- **evidence**: `if (is_null($collectors)) { $this->collectors = []; } foreach ($collectors as $collector) { ... }` When `$collectors` is passed as `null`, the `foreach` attempts to iterate over the `null` parameter.
- **impact**: Calling `collectors(null)` on the configuration object crashes the application on boot with a PHP 8 `TypeError`.
- **minimal fix**: Reassign the parameter if null: `if (is_null($collectors)) { $collectors = []; }`
- **test idea**: Instantiate `ToolbarConfig` and call `->collectors(null)`. Assert it does not throw an exception and `$config->collectors` is an empty array.

**6. FetchesStackTrace accesses undefined property**
- **severity**: high
- **confidence**: high
- **location**: `src/Observers/FetchesStackTrace.php` - `ignoredPaths()` and `ignoredVendorPath()`
- **evidence**: The trait directly accesses `$this->options['ignore_paths']` and `$this->options['ignore_packages']`, but `QueryObserver` (which consumes the trait) does not declare or initialize an `$options` property.
- **impact**: Triggers "Undefined property" PHP warnings/errors on every database query execution, potentially breaking strict applications or flooding error logs.
- **minimal fix**: Declare `public array $options = [];` on `QueryObserver`, or wrap the trait accesses with `property_exists($this, 'options')`.
- **test idea**: Execute a database query while `QueryObserver` is active. Assert no PHP undefined property warnings are generated during stack trace resolution.

**7. Profiler clears shared state prematurely**
- **severity**: high
- **confidence**: high
- **location**: `src/Services/ProfilerService/Profiler.php` - `getRequestStages()`
- **evidence**: At the end of the method, it clears its own static arrays: `self::$requestCheckpoints = []; self::$profileMarkers = [];` etc.
- **impact**: If `getRequestStages()` is called multiple times (e.g., by different collectors or middleware), all subsequent calls will return completely empty profiling data, effectively destroying the payload.
- **minimal fix**: Remove the state-clearing logic from `getRequestStages()`. Rely exclusively on `initialize()` or a dedicated `reset()` method to clear state per request cycle.
- **test idea**: Call `Profiler::getRequestStages()` twice sequentially. Assert the second call returns the identical request stages as the first call, rather than an empty array.

### MEDIUM

**8. Octane state leak via singletons**
- **severity**: medium
- **confidence**: high
- **location**: `src/Toolbar.php` and `src/ToolbarInjector.php`
- **evidence**: `Toolbar::$enabled` and `Toolbar::$visible` are mutated statically but not reset. `ToolbarInjector::$cachedViteUrl` persists across requests up to its TTL.
- **impact**: State bleeds across requests handled by the same Octane worker. If one user's request dynamically calls `Toolbar::disable()`, the toolbar remains disabled for completely unrelated users routed to that worker.
- **minimal fix**: Implement a listener for Octane's `RequestReceived` event that explicitly resets `Toolbar::$enabled`, `Toolbar::$visible`, and clears the cache for `ToolbarInjector`.
- **test idea**: In a simulated Octane environment, call `Toolbar::disable()` in request 1. Trigger the Octane boundary reset, process request 2, and assert `Toolbar::$enabled` is back to its default `true`.

**9. Preg_replace backreference injection in SQL bindings**
- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php` - `replaceBindings()`
- **evidence**: `$sql = preg_replace($regex, $binding, $sql, ...)` uses the user-provided `$binding` as the replacement string. If a binding contains `$1` or `\\1`, PHP evaluates it as a backreference.
- **impact**: Legitimate SQL bindings containing `$` characters could accidentally corrupt the rendered SQL shown in the toolbar or cause replacement errors.
- **minimal fix**: Escape the binding string before replacement: `str_replace('$', '\$', $binding)`, or switch to `preg_replace_callback()`.
- **test idea**: Record a query with a string binding containing a literal backreference like `"$100"`. Assert `replaceBindings()` outputs the exact string `"$100"` and does not alter the text based on regex match groups.

**10. Session Query Detection is PostgreSQL/SQLite specific**
- **severity**: medium
- **confidence**: high
- **location**: `src/Data/QueryData.php` - `setType()`
- **evidence**: `str_contains($this->sql, 'select * from "sessions" where "id" =')` hardcodes double quotes around table and column identifiers.
- **impact**: Session queries are completely ignored when using MySQL (which wraps identifiers in backticks `` `sessions` `` or leaves them unquoted).
- **minimal fix**: Use a regex approach that handles various quoting strategies: `preg_match('/from ["`]?sessions["`]? where/i', $this->sql)`.
- **test idea**: Create a `QueryData` instance with MySQL syntax: ``select * from `sessions` where `id` = ?``. Assert `$query->type` correctly resolves to `QueryType::SESSION`.

**11. AssetController Path Traversal Risk**
- **severity**: medium
- **confidence**: medium
- **location**: `src/Controllers/AssetController.php` - `__invoke()`
- **evidence**: `$assetPath = __DIR__.'/../../build/assets/'.$asset;` is used without explicitly validating that the resolved path stays within the intended `assets` directory.
- **impact**: Depending on router configurations and server normalization, an attacker could theoretically pass `../../../` payloads to read arbitrary files from the server.
- **minimal fix**: Use `basename($asset)` or validate using `realpath()` bounds: `if (!str_starts_with(realpath($assetPath), realpath(__DIR__.'/../../build/assets/'))) abort(404);`
- **test idea**: Attempt to fetch the asset parameter `../../../.env`. Assert the controller returns a 404 rather than attempting to read the file.

**12. HorizonController Unauthenticated Access & Invalid Shell Paths**
- **severity**: medium
- **confidence**: high
- **location**: `src/Controllers/HorizonController.php` - `start()` and `stop()`
- **evidence**: `guardEnvironment()` only checks if the app is in `local`/`development`. Furthermore, the `$_SERVER['HOME']` fallback for the PHP binary is macOS Herd-specific and will fail on Linux.
- **impact**: Any unauthenticated user accessing the network can toggle Horizon. On Linux/Docker, Horizon silently fails to start from the toolbar because the macOS hardcoded fallback path does not exist.
- **minimal fix**: Add an authorization gate (e.g., `Gate::allows('viewToolbar')`) and utilize `PHP_BINARY` constant instead of relying on `shell_exec('which php')` and Herd fallbacks.
- **test idea**: Mock a Linux environment where `shell_exec('which php')` returns empty. Call `start()`, assert it resolves `PHP_BINARY` properly instead of failing on a hardcoded macOS path.

### LOW

**13. ServiceProvider uses env() instead of config()**
- **severity**: low
- **confidence**: high
- **location**: `src/ToolbarServiceProvider.php` - `packageRegistered()`
- **evidence**: Evaluates `if (! env('LARAVEL_TOOLBAR_ENABLED', true))` directly.
- **impact**: In environments where `config:cache` is executed, `env()` returns `null`. This bypasses any `.env` settings, forcing the toolbar into an inconsistent state versus the actual cached configuration.
- **minimal fix**: Leverage Laravel's config repository: `config('toolbar.enabled')` instead.
- **test idea**: Cache the configuration with `toolbar.enabled = false` and remove `LARAVEL_TOOLBAR_ENABLED` from the environment. Assert the toolbar does not register itself.
