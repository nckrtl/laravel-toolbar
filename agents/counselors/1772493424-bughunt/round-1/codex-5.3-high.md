1. **severity:** critical  
**confidence:** high  
**location:** [RequestObserver.php:13](/home/nckrtl/projects/laravel-toolbar/src/Observers/RequestObserver.php:13), [QueryObserver.php:45](/home/nckrtl/projects/laravel-toolbar/src/Observers/QueryObserver.php:45), [ModelObserver.php:32](/home/nckrtl/projects/laravel-toolbar/src/Observers/ModelObserver.php:32)  
**evidence:** `QueryObserver`/`ModelObserver` are long-lived listeners with mutable arrays, both expose `reset()`, but `reset()` is never called anywhere in `src` after a request completes.  
**impact:** In Octane workers, query/model data leaks across requests (wrong duplicates, inflated counts, cross-request data exposure).  
**minimal fix:** At request end, iterate configured observers and call `reset()` when present (eg in `RequestObserver` after injection, or in a dedicated terminating middleware).  
**test idea:** Simulate two requests with the same observer instance; assert second request starts with empty `queries`/`hydrationEntries`.

2. **severity:** high  
**confidence:** high  
**location:** [ProfilerCollector.php:142](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ProfilerCollector.php:142), [ProfilerCollector.php:198](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ProfilerCollector.php:198), [ProfilerCollector.php:221](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ProfilerCollector.php:221)  
**evidence:** `getTotals()` / `fillInMissingStartAndEnd()` / `findNextStageWithEnd()` throw uncaught `\Exception`. `CollectorManager::collectData()` does not catch collector exceptions.  
**impact:** Toolbar collection can 500 the request when checkpoints are missing/inconsistent.  
**minimal fix:** Convert these throws to graceful fallbacks (skip bad stages, zero totals, attach debug metadata instead of throwing).  
**test idea:** Configure profiler collector with missing terminal checkpoint and assert collection returns valid `ProfilerData` instead of exception.

3. **severity:** high  
**confidence:** high  
**location:** [QueriesCollector.php:53](/home/nckrtl/projects/laravel-toolbar/src/Collectors/QueriesCollector.php:53), [ModelsCollector.php:35](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ModelsCollector.php:35)  
**evidence:** `getObserver(...)` is nullable, but result is dereferenced immediately (`$queryObserver->totalTime`, `$modelObserver->hydrationEntries`).  
**impact:** If users customize observers and omit one, collection crashes with null dereference.  
**minimal fix:** Null-guard observer lookups and default to empty/zero dataset.  
**test idea:** `observers([])` + enable `QueriesCollector`/`ModelsCollector`; expect no exception and empty results.

4. **severity:** high  
**confidence:** high  
**location:** [routes/toolbar.php:7](/home/nckrtl/projects/laravel-toolbar/routes/toolbar.php:7), [HorizonController.php:28](/home/nckrtl/projects/laravel-toolbar/src/Controllers/HorizonController.php:28), [HorizonController.php:68](/home/nckrtl/projects/laravel-toolbar/src/Controllers/HorizonController.php:68)  
**evidence:** Horizon start/stop endpoints are under `web` only (no auth/authorization). `start()` also does not verify Horizon availability before returning success.  
**impact:** Any local-environment web user can control Horizon; `start()` can falsely report success when Horizon is absent.  
**minimal fix:** Add explicit auth/ability middleware and `isAvailable()` checks in `start()`/`stop()`.  
**test idea:** Unauthenticated POST to `/_toolbar/horizon/start` should be forbidden; when Horizon missing, `start()` should return failure.

5. **severity:** high  
**confidence:** medium  
**location:** [ToolbarInjector.php:140](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:140), [ToolbarInjector.php:212](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:212)  
**evidence:** `json_encode($data, ...)` can return `false` (invalid UTF-8). Result is passed to `getToolbarHtml(string $data)`, causing `TypeError`.  
**impact:** Certain collected payloads (eg binary/invalid UTF-8 strings) can crash response injection.  
**minimal fix:** Use `JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR` with try/catch fallback payload.  
**test idea:** Include invalid UTF-8 in collected data and assert injection still succeeds with safe fallback JSON.

6. **severity:** medium  
**confidence:** high  
**location:** [DataSizeUnit.php:57](/home/nckrtl/projects/laravel-toolbar/src/Enums/DataSizeUnit.php:57), [TimeUnit.php:66](/home/nckrtl/projects/laravel-toolbar/src/Enums/TimeUnit.php:66)  
**evidence:** Conversion formulas are inverted for some directions. Verified in runtime: `1KB->B=0.0009765625`, `1000ms->s=1000000` (both wrong).  
**impact:** Any reverse-direction conversion returns incorrect values.  
**minimal fix:** Use one formula in both directions: `$value * $this->factor() / $convertToUnit->factor()`, with same-unit fast return.  
**test idea:** Add bidirectional conversion tests for bytes↔KB and ms↔s.

7. **severity:** medium  
**confidence:** high  
**location:** [QueryObserver.php:56](/home/nckrtl/projects/laravel-toolbar/src/Observers/QueryObserver.php:56), [ModelObserver.php:63](/home/nckrtl/projects/laravel-toolbar/src/Observers/ModelObserver.php:63)  
**evidence:** `QueryObserver` never updates `$this->currentMemory` after each query; `ModelObserver` increments count but does not accumulate `memory_used` (calls `formatValue()` only).  
**impact:** Per-query/per-model memory metrics are incorrect (inflated or stuck at first value).  
**minimal fix:** Update memory baseline each event and add deltas to accumulated `Measurement` value before formatting.  
**test idea:** Two events with known deltas should produce cumulative model memory and non-cumulative per-query deltas.

8. **severity:** medium  
**confidence:** high  
**location:** [ToolbarInjector.php:280](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:280)  
**evidence:** JS condition `if("{$nonce}" !== null)` is always true (`"" !== null` when nonce absent).  
**impact:** Cached HTML style stripping always runs, even when CSP nonce is not present.  
**minimal fix:** Emit a real JS nullable variable and compare that, or emit boolean from PHP.  
**test idea:** With no CSP nonce and cached HTML containing inline styles, assert styles are preserved.

9. **severity:** medium  
**confidence:** high  
**location:** [ToolbarConfig.php:156](/home/nckrtl/projects/laravel-toolbar/src/Data/ToolbarConfig.php:156)  
**evidence:** `collectors(?array $collectors = null)` sets `[]` for null, but still `foreach ($collectors as ...)` and later assigns `$this->collectors = $collectors` (null).  
**impact:** `collectors(null)` produces runtime warnings/errors and invalid state.  
**minimal fix:** Normalize once (`$collectors ??= [];`) and return early for empty.  
**test idea:** Call `collectors(null)` and assert no warning/exception; collectors become empty array.

10. **severity:** medium  
**confidence:** medium  
**location:** [ToolbarServiceProvider.php:24](/home/nckrtl/projects/laravel-toolbar/src/ToolbarServiceProvider.php:24)  
**evidence:** Service provider reads `env()` directly (`LARAVEL_TOOLBAR_ENABLED/VISIBLE`) instead of config.  
**impact:** With config caching, expected `.env` toggles can be ignored, enabling toolbar unexpectedly.  
**minimal fix:** Move flags to config and read via `config('toolbar.enabled')` / `config('toolbar.visible')`.  
**test idea:** With cached config and toolbar disabled in config, assert provider does not enable/bind toolbar.

