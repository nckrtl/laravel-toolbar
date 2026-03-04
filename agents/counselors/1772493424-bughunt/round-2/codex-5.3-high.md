1. **Cross-request state leaks and stale profiler data in Octane**
status: refined (overlaps prior observer/profiler leak findings; new evidence is the non-HTML/no-collection path)  
severity: critical  
confidence: high  
location: [RequestObserver::__construct](/home/nckrtl/projects/laravel-toolbar/src/Observers/RequestObserver.php:13), [ToolbarInjector::injectToolbarHtml](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:125), [Profiler::getRequestStages](/home/nckrtl/projects/laravel-toolbar/src/Services/ProfilerService/Profiler.php:123), [QueryObserver::reset](/home/nckrtl/projects/laravel-toolbar/src/Observers/QueryObserver.php:45), [ModelObserver::reset](/home/nckrtl/projects/laravel-toolbar/src/Observers/ModelObserver.php:32)  
evidence: observer `reset()` methods exist but are never called in `src`; profiler state is cleared only inside `getRequestStages()`, but `collectData()` is skipped when HTML injection is skipped (`shouldInjectHtml() === false`, e.g. AJAX/JSON). In Octane, static state persists, so stale checkpoints/query/model data survive and contaminate later requests.  
impact: wrong toolbar data, cross-request leakage, and unbounded memory growth in long-running workers.  
minimal fix: add an unconditional request-boundary reset (terminating middleware/event) that always clears profiler state and calls `reset()` on configured observers, independent of whether toolbar HTML/header is injected.  
test idea: simulate two sequential requests in one process: request A JSON (no injection), request B HTML (injection). Assert request B shows only B’s checkpoints/queries/models.

2. **Uncaught profiler math exceptions can 500 requests**
status: confirmed  
severity: high  
confidence: high  
location: [ProfilerCollector::getTotals](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ProfilerCollector.php:142), [ProfilerCollector::fillInMissingStartAndEnd](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ProfilerCollector.php:198), [CollectorManager::collectData](/home/nckrtl/projects/laravel-toolbar/src/CollectorManager.php:32)  
evidence: multiple `throw new \Exception(...)` paths in profiler collector, with no catch in `CollectorManager`.  
impact: toolbar collection can crash request handling instead of failing open.  
minimal fix: convert hard throws to tolerant fallbacks (or catch collector exceptions centrally and continue).  
test idea: feed stages with tiny floating-point drift or missing terminal checkpoint; assert collection returns data instead of throwing.

3. **Observer-removal config causes null dereference in collectors**
status: confirmed  
severity: high  
confidence: high  
location: [QueriesCollector::setEntries](/home/nckrtl/projects/laravel-toolbar/src/Collectors/QueriesCollector.php:49), [ModelsCollector::setEntries](/home/nckrtl/projects/laravel-toolbar/src/Collectors/ModelsCollector.php:31), [ToolbarConfig::getObserver](/home/nckrtl/projects/laravel-toolbar/src/Data/ToolbarConfig.php:147)  
evidence: `getObserver(...)` is nullable, then immediately dereferenced (`$queryObserver->totalTime`, `$modelObserver->hydrationEntries`).  
impact: user customization that omits one observer crashes collection.  
minimal fix: null-guard and return empty dataset defaults.  
test idea: configure observers without `QueryObserver`/`ModelObserver`, keep respective collectors enabled, assert no exception.

4. **Horizon control endpoints are unauthenticated**
status: refined  
severity: high  
confidence: high  
location: [routes/toolbar.php](/home/nckrtl/projects/laravel-toolbar/routes/toolbar.php:7), [HorizonController::start](/home/nckrtl/projects/laravel-toolbar/src/Controllers/HorizonController.php:28), [HorizonController::stop](/home/nckrtl/projects/laravel-toolbar/src/Controllers/HorizonController.php:68)  
evidence: routes are only under `web` middleware; no auth/authorization gate. `start()` also reports success without verifying Horizon availability after spawn.  
impact: any local-environment web user can start/stop Horizon; misleading success responses when start fails.  
minimal fix: add auth/ability middleware and availability/start-result checks before success response.  
test idea: unauthenticated POST to `/_toolbar/horizon/start` should be forbidden; when Horizon is unavailable, `start()` should return failure.

5. **`replaceBindings()` corrupts SQL for `$`/`\` bindings**
status: confirmed  
severity: medium  
confidence: high  
location: [QueryObserver::replaceBindings](/home/nckrtl/projects/laravel-toolbar/src/Observers/QueryObserver.php:144)  
evidence: binding is used as raw `preg_replace` replacement string. Example: replacement `"$1"` is interpreted as backreference and disappears.  
impact: displayed SQL is wrong for valid inputs containing `$`/backslashes.  
minimal fix: use `preg_replace_callback()` or escape replacement meta chars before replacement.  
test idea: execute query with binding `'$100'`; expected SQL contains literal `'$100'`, actual output is mangled.

6. **`collectors(null)` and `observers(null)` are broken**
status: refined (collectors issue confirmed; observers null crash is additional)  
severity: medium  
confidence: high  
location: [ToolbarConfig::collectors](/home/nckrtl/projects/laravel-toolbar/src/Data/ToolbarConfig.php:156), [ToolbarConfig::observers](/home/nckrtl/projects/laravel-toolbar/src/Data/ToolbarConfig.php:140)  
evidence: `collectors(null)` does `foreach ($collectors as ...)` then assigns null to typed `array` property; `observers(null)` directly assigns null to typed `array`.  
impact: runtime warnings/type errors during legitimate “clear config” style calls.  
minimal fix: normalize null to `[]` immediately and return early.  
test idea: call both methods with null and assert no warning/exception, arrays become empty.

7. **Unit conversions are mathematically wrong in common directions**
status: refined (prior DataSize bug confirmed; TimeUnit reverse-path bug additionally confirmed)  
severity: medium  
confidence: high  
location: [DataSizeUnit::convertValueTo](/home/nckrtl/projects/laravel-toolbar/src/Enums/DataSizeUnit.php:57), [TimeUnit::convertValueTo](/home/nckrtl/projects/laravel-toolbar/src/Enums/TimeUnit.php:66)  
evidence: runtime check: `1 KB -> BYTES` returns `0.0009765625`; `1000 ms -> seconds` returns `1000000`.  
impact: wrong values whenever conversions go through affected direction/branch.  
minimal fix: use one formula for both: `value * fromFactor / toFactor` (with same-unit fast return).  
test idea: assert bidirectional conversions for bytes↔KB and ms↔s.

8. **Memory metrics are incorrect in query/model observers**
status: refined  
severity: medium  
confidence: high  
location: [QueryObserver::recordQuery](/home/nckrtl/projects/laravel-toolbar/src/Observers/QueryObserver.php:56), [ModelObserver::recordHydrations](/home/nckrtl/projects/laravel-toolbar/src/Observers/ModelObserver.php:63)  
evidence: `QueryObserver` never updates `$currentMemory` after each query; `ModelObserver` increments count but does not add new delta to existing `memory_used` value.  
impact: per-query/per-model memory shown to users is misleading.  
minimal fix: update query baseline each event; accumulate model `memory_used->value += delta` before reformatting.  
test idea: two known-memory events should produce per-event query deltas and cumulative model memory.

9. **Invalid UTF-8 in payload can break injected script**
status: refined  
severity: medium  
confidence: medium  
location: [ToolbarInjector::injectToolbarHtml](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:140), [ToolbarInjector::getToolbarHtml](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:212)  
evidence: `json_encode($data, ...)` can return `false`; that value is passed into JS assignment (`window.__LARAVEL_TOOLBAR_DATA__ = ...`), yielding invalid script content.  
impact: toolbar script can fail to parse on affected responses.  
minimal fix: use `JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE` and fallback safely on encode failure.  
test idea: include invalid UTF-8 in collected data and assert injected HTML remains syntactically valid JS.

10. **CSP nonce check in injected JS is always true**
status: confirmed  
severity: low  
confidence: high  
location: [ToolbarInjector::toolbarHtml](/home/nckrtl/projects/laravel-toolbar/src/ToolbarInjector.php:280)  
evidence: condition is `if("{$nonce}" !== null)`; when nonce is absent this becomes `if("" !== null)` (true).  
impact: inline-style stripping runs unconditionally, which can remove cached inline styles even without CSP nonce requirements.  
minimal fix: emit a real JS nullable/boolean variable and branch on that.  
test idea: no CSP nonce + cached HTML with inline style; assert styles are preserved before hydration.

Invalidated prior leads:
- `FetchesStackTrace` undefined `$this->options` causing fatal: invalidated; null-coalescing access does not raise runtime error here.
- `Profiler::initialize()` callback stacking as an every-request Octane bug: not confirmed from current lifecycle wiring.
- `Toolbar::isEnabled()` throwing when unbound: invalidated; short-circuit `app()->bound(...) && ...` prevents resolution when unbound.
- `AssetController` traversal via `/../`: not confirmed with current single-segment route shape.
