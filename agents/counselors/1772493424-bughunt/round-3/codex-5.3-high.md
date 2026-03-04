1. **severity:** critical  
**confidence:** high  
**status:** refined  
**location:** [RequestObserver::__construct](/home/nckrtl/projects/laravel-toolbar/src/Observers/RequestObserver.php:13), [ToolbarInjector::injectToolbarHtml](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:125), [ToolbarInjector::shouldInjectHtml](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:89), [QueryObserver::reset](/home/nckrtl/projects/laravel-toolbar/src/Observers/QueryObserver.php:45), [ModelObserver::reset](/home/nckrtl/projects/laravel-toolbar/src/Observers/ModelObserver.php:32)  
**evidence:** observer reset methods exist but are never invoked; for AJAX/non-HTML responses `injectToolbarHtml()` returns early and never runs collection/cleanup. In Octane, singleton/static state persists between requests.  
**impact:** cross-request query/model/profiler leakage and stale data shown to later users/requests.  
**minimal fix:** add unconditional request-boundary reset (observer resets + profiler reset) in a terminating hook, independent of HTML/header injection path.  
**test idea:** request A = JSON (no injection), request B = HTML in same process; B must not include A’s queries/checkpoints.

2. **severity:** high  
**confidence:** high  
**status:** confirmed  
**location:** [ProfilerCollector::getTotals](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ProfilerCollector.php:142), [ProfilerCollector::fillInMissingStartAndEnd](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ProfilerCollector.php:198), [CollectorManager::collectData](/home/nckrtl/projects/laravel-toolbar/src/CollectorManager.php:32)  
**evidence:** collector throws uncaught `\Exception` on timing inconsistencies/missing end stages.  
**impact:** toolbar can 500 the app request.  
**minimal fix:** fail open (graceful fallback values) or catch collector exceptions in `CollectorManager`.  
**test idea:** provide stages with missing terminal checkpoint; expect collection to succeed with degraded profiler data.

3. **severity:** high  
**confidence:** high  
**status:** confirmed  
**location:** [QueriesCollector::setEntries](/home/nckrtl/projects/laravel-toolbar/src/Collectors/QueriesCollector.php:49), [ModelsCollector::setEntries](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ModelsCollector.php:31), [ToolbarConfig::getObserver](/home/nckrtl/projects/laravel-toolbar/src/Data/ToolbarConfig.php:147)  
**evidence:** nullable `getObserver()` result is dereferenced directly.  
**impact:** removing an observer while keeping its collector enabled causes fatal null dereference.  
**minimal fix:** null-guard and return empty defaults.  
**test idea:** configure observers without `QueryObserver`/`ModelObserver`; collectors should return empty data, not crash.

4. **severity:** high  
**confidence:** high  
**status:** refined  
**location:** [TimeUnit::convertValueTo](/home/nckrtl/projects/laravel-toolbar/src/Enums/TimeUnit.php:66), [DataSizeUnit::convertValueTo](/home/nckrtl/projects/laravel-toolbar/src/Enums/DataSizeUnit.php:57)  
**evidence:** reverse-direction conversions are wrong. Runtime checks: `1000 ms -> s` returns `1000000`; `1 KB -> B` returns `0.0009765625`.  
**impact:** incorrect duration/memory values in toolbar output.  
**minimal fix:** use one formula both directions: `value * fromFactor / toFactor` (with same-unit early return).  
**test idea:** bidirectional assertions for bytes↔KB and ms↔s.

5. **severity:** medium  
**confidence:** high  
**status:** confirmed  
**location:** [Profiler::setupViewProfiling anonymous get()](/home/nckrtl/projects/laravel-toolbar/src/Services/ProfilerService/Profiler.php:106)  
**evidence:** `BEFORE_VIEW_RENDERING` is recorded after `parent::get()` returns.  
**impact:** controller/view stage timings are misattributed.  
**minimal fix:** record `BEFORE_VIEW_RENDERING` before calling `parent::get()`.  
**test idea:** render a deliberately slow view; “view rendering” stage should reflect that time.

6. **severity:** medium  
**confidence:** high  
**status:** confirmed  
**location:** [QueryObserver::recordQuery](/home/nckrtl/projects/laravel-toolbar/src/Observers/QueryObserver.php:56), [ModelObserver::recordHydrations](/home/nckrtl/projects/laravel-toolbar/src/Observers/ModelObserver.php:63)  
**evidence:** query baseline memory is never updated after each query; model hydration increments count but never adds new memory delta.  
**impact:** memory metrics shown to users are wrong.  
**minimal fix:** update query baseline per event; accumulate model `memory_used->value += delta` then format.  
**test idea:** two known memory events should yield per-query deltas and cumulative model memory.

7. **severity:** medium  
**confidence:** high  
**status:** refined  
**location:** [ToolbarConfig::observers](/home/nckrtl/projects/laravel-toolbar/src/Data/ToolbarConfig.php:140), [ToolbarConfig::collectors](/home/nckrtl/projects/laravel-toolbar/src/Data/ToolbarConfig.php:156)  
**evidence:** `observers(null)` assigns `null` to typed `array`; `collectors(null)` iterates `null` and then assigns `null` to typed `array`.  
**impact:** runtime type/warning failures during valid “clear config” style usage.  
**minimal fix:** normalize null to `[]` and early return.  
**test idea:** call both methods with `null`; expect no exception and empty arrays.

8. **severity:** medium  
**confidence:** high  
**status:** refined  
**location:** [QueryObserver::replaceBindings](/home/nckrtl/projects/laravel-toolbar/src/Observers/QueryObserver.php:144)  
**evidence:** replacement uses raw binding in `preg_replace`; `$1`/`\1` in bindings are treated as backreferences. Also no guard for `preg_replace()` returning `null`.  
**impact:** displayed SQL can be corrupted; rare PCRE failures can propagate bad state/type issues.  
**minimal fix:** switch to `preg_replace_callback()` with literal replacement handling and null-safe fallback.  
**test idea:** binding `'$100'` must remain literal in rendered SQL; force PCRE failure case and ensure no crash.

9. **severity:** medium  
**confidence:** medium  
**status:** refined  
**location:** [ToolbarInjector::injectToolbarHtml](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:140), [ToolbarInjector::toolbarHtml](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:270)  
**evidence:** `json_encode()` failure returns `false`; coerced into empty string in `getToolbarHtml(string $data)`, yielding JS like `window.__LARAVEL_TOOLBAR_DATA__ = ;`.  
**impact:** injected script becomes invalid JS; toolbar fails to boot for that response.  
**minimal fix:** `JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE` with fallback payload on exception.  
**test idea:** include invalid UTF-8 in collected payload; injected HTML should still contain valid JS assignment.

10. **severity:** high  
**confidence:** high  
**status:** refined  
**location:** [routes/toolbar.php](/home/nckrtl/projects/laravel-toolbar/routes/toolbar.php:7), [HorizonController::start](/home/nckrtl/projects/laravel-toolbar/src/Controllers/HorizonController.php:28), [HorizonController::stop](/home/nckrtl/projects/laravel-toolbar/src/Controllers/HorizonController.php:68)  
**evidence:** Horizon control routes are under `web` only (no authz). `start()` returns success without confirming availability/start outcome.  
**impact:** local-environment users on network can start/stop Horizon; false-positive success responses.  
**minimal fix:** add authorization middleware/gate and verify Horizon availability + post-start state before success.  
**test idea:** unauthenticated POST should be forbidden; when Horizon missing, `start()` should return failure.

11. **severity:** low  
**confidence:** high  
**status:** confirmed  
**location:** [ToolbarInjector::toolbarHtml](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:280)  
**evidence:** `if("{$nonce}" !== null)` is always true in JS (`"" !== null`).  
**impact:** cached inline styles are stripped even when no CSP nonce exists.  
**minimal fix:** emit boolean/null JS variable and branch on that real value.  
**test idea:** no CSP nonce + cached HTML with style attrs should preserve styles.

**Invalidated / narrowed prior leads**
1. `FetchesStackTrace` undefined `$this->options` fatal: invalidated (null-coalescing access does not error here).  
2. `AssetController` traversal via `/{asset}`: not confirmed with current single-segment route shape.  
3. `Toolbar::isEnabled()` unbound-container crash: invalidated (short-circuit `app()->bound(...) && ...`).  
4. `quoteStringBinding()` escape-order bug: not confirmed (`strtr` replacement semantics avoid claimed double-escape sequence issue).

I could not run the full test suite in this environment (read-only sandbox).
