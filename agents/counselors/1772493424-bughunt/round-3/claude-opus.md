Now I have a thorough understanding of the codebase and all prior round findings. Let me compile my audit.

---

# Correctness Audit: laravel-toolbar — Round 3

## Finding 1 — `QueryObserver::recordQuery()` never updates `$currentMemory` baseline, causing cumulative memory attribution

- **status**: refined (prior rounds noted the issue but didn't trace the precise mechanism)
- **severity**: high
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php:56-66` — `recordQuery()`
- **evidence**: Line 58-63 sets `$this->currentMemory` once (when `== 0`), then line 65-66 uses `$memoryBefore = $this->currentMemory` and `$memoryAfter = memory_get_usage()`. Crucially, `$this->currentMemory` is **never updated to `$memoryAfter`** after the query is recorded. Compare to `ModelObserver::recordHydrations()` line 80 which correctly does `$this->currentMemory = $memoryAfter;`.
- **impact**: Each successive query's `memory_used` delta is measured from the original baseline, not the previous query. Query 1 shows `mem_now - baseline` (correct). Query 2 shows `mem_now - baseline` (WRONG — includes query 1's allocation). Query 50 shows `mem_now - baseline` (wildly inflated). The toolbar displays increasingly large memory values for later queries, making them look like memory hogs when they may be trivial.
- **minimal fix**: Add `$this->currentMemory = $memoryAfter;` after line 91 (end of `recordQuery()`).
- **test idea**: Execute 3 identical `INSERT` queries. Measure the `memory_used->value` on each `QueryData`. All three should be roughly equal. Currently query 3's value is approximately 3x query 1's.

## Finding 2 — `ProfilerCollector::getTotals()` exception messages are semantically swapped

- **status**: new
- **severity**: high (combined with the "throws at all" issue)
- **confidence**: high
- **location**: `src/Collectors/ProfilerCollector.php:171-177` — `getTotals()`
- **evidence**: Line 169 computes `$differenceFromStartToCurrentRequestStageEnd` (real elapsed wall clock time from first stage start to current stage end). Line 160 accumulates `$totalWallTime` (sum of individual stage durations). Line 171-173: when `$differenceFromStartToCurrentRequestStageEnd > $totalWallTime` (real time exceeds sum of parts → there's a **gap** between stages), the exception says "Wall time **overlap** detected". Line 175-177: when `$totalWallTime > $differenceFromStartToCurrentRequestStageEnd` (sum of parts exceeds real time → stages **overlap**), the exception says "Wall time **gap** detected". The error messages are backwards.
- **impact**: Combined with the prior-confirmed finding that these exceptions are uncaught and crash requests: not only does the toolbar crash on timing discrepancies, but the error message misdirects debugging (says "overlap" when the actual cause is a gap, and vice versa).
- **minimal fix**: Swap the message strings. Better: replace both throws with a tolerance threshold (e.g., 0.5ms) and degrade gracefully.
- **test idea**: Create stages with a deliberate 1ms gap between them (stage 1 ends at T=10ms, stage 2 starts at T=11ms). Trigger `getTotals()`. The error message should say "gap" not "overlap".

## Finding 3 — `ToolbarConfig::observers(null)` throws TypeError on non-nullable `array` property

- **status**: refined (codex round 2 mentioned it; confirming with precise mechanism)
- **severity**: medium
- **confidence**: high
- **location**: `src/Data/ToolbarConfig.php:140-145` — `observers()`
- **evidence**: Property `public array $observers;` (line 41) is typed as `array`, not `?array`. The `observers()` method (line 142) does `$this->observers = $observers;` where `$observers` can be `null` (parameter is `?array`). In PHP 8.x, assigning `null` to a non-nullable typed property throws `TypeError: Cannot assign null to property NckRtl\Toolbar\Data\ToolbarConfig::$observers of type array`.
- **impact**: Any user calling `->observers(null)` to clear observers crashes the toolbar initialization.
- **minimal fix**: `$this->observers = $observers ?? [];`
- **test idea**: Call `(new ToolbarConfig)->observers(null)`. Assert no TypeError. Assert `$config->observers` is `[]`.

## Finding 4 — `ToolbarConfig::collectors(null)` iterates over null after setting empty array

- **status**: confirmed
- **severity**: medium
- **confidence**: high
- **location**: `src/Data/ToolbarConfig.php:156-168` — `collectors()`
- **evidence**: Line 158-160 correctly detects null/empty and sets `$this->collectors = []`. But execution falls through to line 162: `foreach ($collectors as $collector)` — `$collectors` (the parameter) is still `null`. PHP 8 throws `TypeError: foreach() argument must be of type array|object, null given`. Line 166 then does `$this->collectors = $collectors` which also assigns `null` to a typed `array` property.
- **minimal fix**: Add `return $this;` after line 159, or normalize: `$collectors ??= [];` at the method start.
- **test idea**: Call `(new ToolbarConfig)->collectors(null)`. Assert no exception. Assert `$config->collectors` is `[]`.

## Finding 5 — `DataSizeUnit::convertValueTo()` formula is inverted for larger→smaller conversions

- **status**: confirmed with precise branch analysis
- **severity**: medium (latent — the broken branch is not exercised by current codebase)
- **confidence**: high
- **location**: `src/Enums/DataSizeUnit.php:59-60` — `convertValueTo()`, first branch
- **evidence**: When `$this->factor() > $convertToUnit->factor()` (e.g., KB→BYTES where 1024 > 1): formula is `$value / $this->factor() * $convertToUnit->factor()` = `1 / 1024 * 1 = 0.000977`. Expected: 1024. The formula should be `$value * $this->factor() / $convertToUnit->factor()`. The current codebase only exercises the CORRECT branch (BYTES→KB in `QueryObserver::recordQuery()` line 90), so this is latent.
- **impact**: Any future code converting larger→smaller data size units (MB→KB, KB→B) would get wildly incorrect values.
- **minimal fix**: Use uniform formula: `return $value * $this->factor() / $convertToUnit->factor();` for both branches (matching TimeUnit's correct branch).
- **test idea**: `DataSizeUnit::KILOBYTES->convertValueTo(1, DataSizeUnit::BYTES)` — expected 1024, currently returns ~0.000977.

## Finding 6 — `TimeUnit::convertValueTo()` formula is inverted for smaller→larger conversions

- **status**: refined (codex round 2 claimed this; confirming with precise branch analysis)
- **severity**: medium (latent — the broken branch is not exercised by current codebase)
- **confidence**: high
- **location**: `src/Enums/TimeUnit.php:76` — `convertValueTo()`, else branch
- **evidence**: When `$this->factor() <= $convertToUnit->factor()` (e.g., MILLISECONDS→SECONDS where 1000 < 1_000_000): formula is `$value / $this->factor() * $convertToUnit->factor()` = `1000 / 1000 * 1_000_000 = 1_000_000`. Expected: 1.0 seconds. The current codebase only exercises the CORRECT branch (SECONDS→MILLISECONDS in profiler code), so this is latent. Both DataSizeUnit and TimeUnit have the **same bug** but in **opposite branches**: DataSizeUnit's branch 1 is wrong, TimeUnit's branch 2 is wrong. The correct formula for both is uniformly `$value * $this->factor() / $convertToUnit->factor()`.
- **impact**: Any future code converting smaller→larger time units (ms→s, µs→ms) would get wildly incorrect values.
- **minimal fix**: `return $value * $this->factor() / $convertToUnit->factor();` for both branches.
- **test idea**: `TimeUnit::MILLISECONDS->convertValueTo(1000, TimeUnit::SECONDS)` — expected 1.0, currently returns 1,000,000.

## Finding 7 — `QueriesCollector`/`ModelsCollector` null dereference when observers are removed

- **status**: confirmed
- **severity**: high
- **confidence**: high
- **location**: `src/Collectors/QueriesCollector.php:53-63` — `setEntries()`, and `src/Collectors/ModelsCollector.php:35-37` — `setEntries()`
- **evidence**: `$toolbar->config->getObserver(QueryObserver::class)` returns `null` if the user customizes observers to exclude it. Line 55: `$queryObserver->totalTime` throws `Error: Attempt to read property on null`. Same pattern in ModelsCollector.
- **impact**: User customization removing an observer crashes data collection for every request.
- **minimal fix**: Add null guard: `if (!$queryObserver) { return; }` in both collectors.
- **test idea**: Configure toolbar with `->observers([])`, keep collectors enabled. Call `collectData()`. Assert no crash, empty data returned.

## Finding 8 — `ProfilerCollector` throws uncaught exceptions that crash requests

- **status**: confirmed
- **severity**: high
- **confidence**: high
- **location**: `src/Collectors/ProfilerCollector.php:172,176,203,232` — `getTotals()`, `fillInMissingStartAndEnd()`, `findNextStageWithEnd()`
- **evidence**: Four `throw new \Exception(...)` paths. `CollectorManager::collectData()` at line 60 has no try-catch around collector calls. Floating-point arithmetic and process scheduling can cause sub-millisecond timing discrepancies that trigger these throws.
- **impact**: The toolbar — a dev tool — kills the application request it's meant to observe.
- **minimal fix**: Either catch exceptions in `CollectorManager::collectData()` per-collector, or replace throws with tolerance thresholds and graceful degradation.
- **test idea**: Create request stages with 0.001ms overlap. Call `getTotals()`. Should not throw.

## Finding 9 — Observer `reset()` is never called; state leaks across Octane requests

- **status**: confirmed
- **severity**: critical
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php:45`, `src/Observers/ModelObserver.php:32` — `reset()` methods that are never called
- **evidence**: Both observers implement `reset()` documented as "critical for long-running processes like Laravel Octane," but grep across `src/` shows zero callers. `Profiler::getRequestStages()` clears profiler state, but observer state (queries, hydrations, hashes, memory baselines) accumulates indefinitely.
- **impact**: In Octane: queries and models from request 1 appear in request 2's toolbar. Duplicate detection flags queries from previous requests. Memory grows unboundedly, eventually OOM-killing the worker.
- **minimal fix**: Call `reset()` on all observers at request boundaries. Add to `Profiler::initialize()` or a terminating middleware:
  ```php
  foreach ($toolbar->config->observers as $observer) {
      if (method_exists($observer, 'reset')) {
          $observer->reset();
      }
  }
  ```
- **test idea**: With same observer instance, record a query, then simulate a new request (call reset or whatever boundary mechanism). Record another query. Assert second request's queries array has exactly 1 entry, not 2.

## Finding 10 — `ModelObserver::recordHydrations()` doesn't accumulate memory across hydrations

- **status**: confirmed
- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/ModelObserver.php:74-78` — `recordHydrations()` else branch
- **evidence**: On subsequent hydrations of the same model class, line 76 calls `$model->memory_used->formatValue()` which just reformats the existing value. `$model->count++` increments correctly. But the new memory delta (`$memoryAfter - $memoryBefore`) is completely discarded.
- **impact**: Total memory for a model class only reflects the first hydration. Hydrating 1000 User models shows memory of ~1 model.
- **minimal fix**:
  ```php
  $model->memory_used->value += ($memoryAfter - $memoryBefore);
  $model->memory_used->formatValue();
  $model->count++;
  ```
- **test idea**: Fire `eloquent.retrieved` 10 times for the same model. Assert `memory_used->value` is roughly 10x a single hydration, not 1x.

## Finding 11 — `Profiler::setupViewProfiling()` records `BEFORE_VIEW_RENDERING` after the first view renders

- **status**: confirmed
- **severity**: medium
- **confidence**: high
- **location**: `src/Services/ProfilerService/Profiler.php:106-117` — anonymous class `get()` method
- **evidence**: Line 108 calls `$result = parent::get($path, $data)` (renders the view). Line 111-113 then checks `if (empty(Profiler::$viewRenders))` and records `BEFORE_VIEW_RENDERING`. The checkpoint timestamp is captured *after* the view has already rendered.
- **impact**: The "Controller" stage (BEFORE_CONTROLLER → BEFORE_VIEW_RENDERING) includes the first view's render time. The "View rendering" stage underreports by exactly the first view's duration. Profiling data is inaccurate.
- **minimal fix**: Record checkpoint before `parent::get()`:
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
- **test idea**: Render a view with a `sleep(0.1)`. Assert `BEFORE_VIEW_RENDERING` timestamp is < `AFTER_VIEW_RENDERING` timestamp by at least 100ms (the view time). Currently it's close to 0ms because BEFORE is recorded after the first view.

## Finding 12 — `ToolbarServiceProvider` uses `env()` directly, broken with config cache

- **status**: confirmed
- **severity**: high
- **confidence**: high
- **location**: `src/ToolbarServiceProvider.php:26-33` — `packageRegistered()`
- **evidence**: `env('LARAVEL_TOOLBAR_ENABLED', true)` returns `null` when config is cached (`php artisan config:cache`). `! null` → `true`, so the toolbar is **disabled** regardless of the `.env` value. This means the toolbar silently breaks in any cached config environment, and `.env` toggles cannot be used to enable/disable it.
- **impact**: Toolbar is non-functional in staging/production environments that use config caching (standard Laravel practice).
- **minimal fix**: Publish config file with `'enabled' => env('LARAVEL_TOOLBAR_ENABLED', true)`, use `config('toolbar.enabled')` in provider.
- **test idea**: With `env()` returning `null` (simulating cached config), call `packageRegistered()`. Assert toolbar is enabled by default (since the default should be `true`).

## Finding 13 — `QueryObserver::replaceBindings()` backreference injection via `$` and `\` in bindings

- **status**: confirmed
- **severity**: medium
- **confidence**: high
- **location**: `src/Observers/QueryObserver.php:159-164` — `replaceBindings()`
- **evidence**: String bindings are passed as the replacement argument to `preg_replace()`. Characters `$0`, `$1`, `\1` etc. in bindings are interpreted as PCRE backreferences. A binding of `"$100 credit"` becomes garbled because `$1` references capture group 1.
- **impact**: SQL display is corrupted for queries with dollar-sign or backslash values.
- **minimal fix**: Use `preg_replace_callback()` to return the binding directly, or escape: `str_replace(['\\', '$'], ['\\\\', '\\$'], $binding)` before passing to `preg_replace()`.
- **test idea**: Insert `['name' => '$100 credit']`. Assert `replaceBindings()` output contains the literal string `'$100 credit'`.

## Finding 14 — `json_encode()` can return `false` in two injection paths, causing TypeError

- **status**: refined (prior rounds identified HTML path; Inertia path is additional)
- **severity**: medium
- **confidence**: high
- **location**: `src/ToolbarInjector.php:141` — `injectToolbarHtml()` and `src/ToolbarInjector.php:80` — `injectToolbarData()`
- **evidence**: **HTML path** (line 141): `json_encode($data, JSON_HEX_TAG|...)` can return `false` for invalid UTF-8. Passed to `getToolbarHtml(string $data)` → TypeError. **Inertia path** (line 80): `json_encode($data)` has NO encoding flags at all, and the result is passed directly to `base64_encode()`. `base64_encode(false)` emits deprecation warning in PHP 8.1+ and returns `""`, which the frontend's `JSON.parse("")` would throw on (caught by try-catch in interceptors.ts:64). The Inertia path is additionally missing `JSON_HEX_TAG` etc., though this is less critical since the data goes into a header, not HTML.
- **impact**: Binary data or invalid UTF-8 in collected data (query bindings, response content) crashes toolbar injection.
- **minimal fix**: Add `JSON_INVALID_UTF8_SUBSTITUTE` flag to both `json_encode()` calls. Add `false` check with fallback empty-object JSON.
- **test idea**: Include `"\xB1\x31"` in query bindings. Trigger both injection paths. Assert no TypeError, toolbar renders with substituted characters.

## Finding 15 — `FetchesStackTrace` trait accesses undeclared `$this->options` property

- **status**: refined (prior rounds disagreed on severity; verifying PHP 8.2+ behavior)
- **severity**: medium
- **confidence**: medium
- **location**: `src/Observers/FetchesStackTrace.php:34,46` — `ignoredPaths()` and `ignoredVendorPath()`
- **evidence**: Accesses `$this->options['ignore_paths'] ?? []` and `$this->options['ignore_packages'] ?? true`. `QueryObserver` uses this trait but declares no `$options` property. In PHP 8.2, accessing an undeclared property emits a deprecation warning. The `??` fallback means the code *works* but logs warnings. In PHP 9.0, this will be a fatal error. However, the `lookUpCallerFromStackTrace` flag (line 34 of QueryObserver) is only `true` in non-production, and the `getCallerFromStackTrace()` call (line 71) is guarded by that flag. So the warnings only emit in development.
- **impact**: Deprecation warnings in development per query (can flood logs). Fatal error when PHP 9.0 is adopted.
- **minimal fix**: Add `protected array $options = [];` to `QueryObserver`.
- **test idea**: Execute a query in non-production mode. Assert no deprecation warnings from `QueryObserver` stack trace lookup.

## Finding 16 — CSP nonce check in injected JavaScript is always truthy

- **status**: confirmed
- **severity**: low
- **confidence**: high
- **location**: `src/ToolbarInjector.php:280` — inline JavaScript
- **evidence**: `if("{$nonce}" !== null)` — when `$nonce` is PHP `null`, this renders as `if("" !== null)` in JS, which is always `true`. The style-stripping regex runs unconditionally.
- **impact**: Cached HTML inline styles are stripped even without CSP nonce. Harmless since Vue re-adds them during hydration, but adds unnecessary processing.
- **minimal fix**: `if("{$nonce}" !== "")` or conditionally emit the block from PHP.
- **test idea**: Render toolbar without CSP nonce. Assert the JS condition doesn't strip styles from cached HTML unnecessarily.

## Finding 17 — `Toolbar::$enabled` and `$visible` static state leaks in Octane

- **status**: confirmed
- **severity**: medium
- **confidence**: high
- **location**: `src/Toolbar.php:10-11`
- **evidence**: Static properties persist across Octane requests. `Toolbar::disable()` in one request (e.g., health check endpoint) disables toolbar for all subsequent requests in that worker.
- **impact**: Intermittent toolbar disappearance in Octane, difficult to debug.
- **minimal fix**: Reset these at the start of `Toolbar::__construct()`, or in `Profiler::initialize()`:
  ```php
  self::$enabled = true;
  self::$visible = true;
  ```
- **test idea**: Call `Toolbar::disable()`, then simulate new request by re-resolving Toolbar. Assert `$enabled` is `true`.

## Finding 18 — `QueryData::setType()` session detection only works for PostgreSQL/SQLite

- **status**: confirmed
- **severity**: medium
- **confidence**: high
- **location**: `src/Data/QueryData.php:39-44` — `setType()`
- **evidence**: String matching uses double-quoted identifiers (`"sessions"`, `"id"`, `"payload"`). MySQL uses backticks (`` `sessions` ``), SQL Server uses brackets (`[sessions]`). The majority of Laravel installations use MySQL.
- **impact**: Session queries are never identified/badged for MySQL users.
- **minimal fix**: Use regex: `preg_match('/from\s+["`\[]?sessions["`\]]?\s/i', $this->sql)`
- **test idea**: Create `QueryData` with SQL `` "select * from `sessions` where `id` = ?" ``. Assert `$type === QueryType::SESSION`.

## Finding 19 — `HorizonController` PHP binary fallback is macOS-only, uses unauthenticated routes

- **status**: confirmed
- **severity**: low
- **confidence**: high
- **location**: `src/Controllers/HorizonController.php:48-49` — `start()`, and `routes/toolbar.php:7`
- **evidence**: When `which php` returns empty (some Docker images), fallback is `$_SERVER['HOME'].'/Library/Application Support/Herd/bin/php'` — macOS-only. Routes are under `web` middleware with no auth gate beyond `guardEnvironment()` checking for `local`/`development`.
- **impact**: On Linux Docker, Horizon start silently fails. Any local network user can toggle Horizon.
- **minimal fix**: Use `PHP_BINARY` constant as fallback. Add authorization gate.
- **test idea**: Mock `shell_exec('which php')` returning empty on Linux. Assert `PHP_BINARY` is used.

## Finding 20 — `ModelObserver::given()` is dead code

- **status**: confirmed
- **severity**: low
- **confidence**: high
- **location**: `src/Observers/ModelObserver.php:83-97`
- **evidence**: No callers found in the entire codebase.
- **impact**: Maintenance burden only.
- **minimal fix**: Delete the method.

## Invalidated/Narrowed Prior Claims

- **`FetchesStackTrace` causing "fatal error" (round 1 gemini #6)**: Narrowed. In PHP 8.2-8.4, it's a deprecation warning, not fatal. The `?? []` fallback ensures correct behavior. Only becomes fatal in PHP 9.0.
- **`Profiler::initialize()` stacking duplicate callbacks in Octane (round 1 claude #13)**: Invalidated. `app()->instance(Toolbar::class, new Toolbar)` runs in `packageRegistered()`, which executes once per Octane worker boot. `Profiler::initialize()` is only called once. Callbacks don't stack.
- **`Toolbar::isEnabled()` throwing when unbound (round 1 gemini implicit)**: Invalidated. Line 53 uses `app()->bound(Toolbar::class) && ...` — short-circuit prevents resolution when unbound.
- **`AssetController` path traversal (round 1 claude #19)**: Narrowed to negligible. Laravel's route constraint `[^/]+` prevents `/` in `{asset}`. `..` alone resolves to a directory, and `file_get_contents()` on a directory returns `false`/empty on most OS. No actionable traversal path exists.
- **`ResponseCollector` `getContent()` returning `false` (round 1 claude #17)**: Narrowed. `CollectorManager`'s type-hint restricts to `Response|JsonResponse|RedirectResponse|null`. `Response::getContent()` returns `string|false`, but `false` only occurs for `StreamedResponse` which is excluded by the type system. For `RedirectResponse`, `getContent()` returns the empty string by default.

## Summary

| Severity | Count | Key Findings |
|----------|-------|------|
| Critical | 1 | Observer `reset()` never called — Octane state leaks (#9) |
| High | 5 | QueryObserver cumulative memory (#1), swapped exception messages (#2), collector null derefs (#7), uncaught profiler exceptions (#8), `env()` vs `config()` (#12) |
| Medium | 9 | `observers(null)` TypeError (#3), `collectors(null)` TypeError (#4), DataSizeUnit inverted (#5), TimeUnit inverted (#6), model memory accumulation (#10), view profiling timing (#11), backreference injection (#13), json_encode false (#14), FetchesStackTrace (#15) |
| Low | 4 | CSP nonce (#16), static state leak (#17), session detection (#18), HorizonController (#19), dead code (#20) |
