Now I have all the data I need. Here's the full verification:

---

**Finding 1: Observer `reset()` never called — CONFIRMED**
- Evidence: `grep` for `->reset()` across `src/` returns zero results. The methods exist on `QueryObserver` and `ModelObserver` but nothing calls them. `Profiler::initialize()` resets its own static state, but `Toolbar::__construct()` creates *new* observer instances rather than resetting existing ones. Under Octane, if the `Toolbar` singleton is reused without re-instantiation, state accumulates. However, `ToolbarServiceProvider::packageRegistered()` calls `new Toolbar` each time, so the severity depends on whether Octane re-runs `packageRegistered`. **In a standard Octane setup with `app()->instance()`, the singleton persists, but `Profiler::initialize()` is called in `Toolbar::__construct()` which also creates fresh observers.** The real risk: the event listeners registered in observer constructors (`app('events')->listen(...)`) are never removed and will stack up if `new Toolbar` is called again.

**Finding 2: ProfilerCollector throws uncaught exceptions — CONFIRMED**
- Evidence: `getTotals()` (lines 172, 176) and `fillInMissingStartAndEnd()` (line 203, 232) throw raw `\Exception`. `CollectorManager::collectData()` has no try-catch. The exception propagates to `ToolbarInjector::inject()` → `RequestObserver` event listener → will crash the request.

**Finding 3: Null dereference when observers removed — CONFIRMED**
- Evidence: `getObserver()` returns `null` (line 153: `?? null`). `QueriesCollector::setEntries()` (line 55) does `$queryObserver->totalTime` with no null check. Same for `ModelsCollector` (line 37). If a user customizes config and removes the observer, this crashes.

**Finding 4: DataSizeUnit::convertValueTo() inverted for larger→smaller — CONFIRMED**
- Evidence: `KILOBYTES->convertValueTo(1, BYTES)`: factor 1024 > 1 → `1 / 1024 * 1 = 0.000976` instead of 1024. However, the only actual `convertTo` call in the codebase is BYTES→KILOBYTES (the else branch, which is correct). **The bug is real but currently not exercised.**

**Finding 5: TimeUnit::convertValueTo() inverted for smaller→larger — CONFIRMED**
- Evidence: `MILLISECONDS->convertValueTo(1000, SECONDS)`: factor 1000 < 1000000 → else branch: `1000 / 1000 * 1000000 = 1,000,000` instead of 1. However, all actual conversions in the codebase go SECONDS→MILLISECONDS (the if branch, which is correct). **The bug is real but currently not exercised.**

**Finding 6: env() used directly in service provider — CONFIRMED**
- Evidence: `ToolbarServiceProvider::packageRegistered()` line 26: `env('LARAVEL_TOOLBAR_ENABLED', true)`. When config is cached, `env()` returns `null`. `! null` → `true` → `Toolbar::$enabled = false` is **not reached**, so the toolbar stays enabled. The claim that "toolbar is disabled" is **wrong** — it's the opposite: `env()` returning `null` means `! null` = `true`, skipping the `if` body. The *actual* bug is that the env var is ignored when cached, not that it's disabled. **PARTIALLY CONFIRMED** — using `env()` is wrong, but the impact is inverted from what was claimed.

**Finding 7: BEFORE_VIEW_RENDERING recorded after first view renders — CONFIRMED**
- Evidence: In the anonymous class (Profiler.php line 106-118), `parent::get()` runs first (rendering the view), *then* `Profiler::record(BEFORE_VIEW_RENDERING)` is called. The checkpoint timestamp is taken after the first view already rendered.

**Finding 8: json_encode() can return false — PARTIALLY CONFIRMED**
- Evidence: `ToolbarInjector::injectToolbarHtml()` line 141-145 calls `json_encode($data, ...)` and passes result to `getToolbarHtml(string $data)`. If json_encode returns `false`, PHP 8.x will throw a `TypeError` on the string parameter. Likelihood is low since Spatie Data objects typically produce valid UTF-8, but there's no guard.

**Finding 9: QueryObserver never updates $currentMemory — CONFIRMED**
- Evidence: `recordQuery()` sets `$memoryBefore = $this->currentMemory` and `$memoryAfter = memory_get_usage()` but never assigns `$this->currentMemory = $memoryAfter`. Compare to `ModelObserver::recordHydrations()` line 80: `$this->currentMemory = $memoryAfter`. This means all query memory deltas are measured from the same baseline.

**Finding 10: ModelObserver discards memory delta on subsequent hydrations — CONFIRMED**
- Evidence: The else branch (lines 75-78) calls `$model->memory_used->formatValue()` (just reformats) and `$model->count++`, but never adds the new `$memoryAfter - $memoryBefore` delta to `memory_used->value`. Subsequent hydrations of the same model class have their memory impact discarded.

**Finding 11: collectors(null) and observers(null) TypeErrors — PARTIALLY CONFIRMED**
- Evidence: `collectors(null)` — line 158: `is_null(null) || empty(null)` → true, sets `$this->collectors = []`. But then line 162: `foreach (null as $collector)` → **TypeError**. `observers(null)` — line 142: `$this->observers = null` assigned to `array` property — **TypeError** in PHP 8.4 with strict typing. However, neither method is called with `null` in default config — it's only a risk for user customization.

**Finding 12: preg_replace backreference injection — CONFIRMED**
- Evidence: `replaceBindings()` line 159: `preg_replace($regex, $binding, ...)` — `$binding` is the replacement string and is not passed through `preg_quote()` or escaped for backreferences. A binding value containing `$1` or `\1` would be interpreted as a backreference.

**Finding 13: setType() only matches PostgreSQL/SQLite quoting — CONFIRMED**
- Evidence: `QueryData::setType()` lines 39-43 use `"sessions"` with double quotes. MySQL uses backtick identifiers (`\`sessions\``). The session detection won't work for MySQL.

**Finding 14: Static $enabled/$visible leaks in Octane — PARTIALLY CONFIRMED**
- Evidence: `Toolbar::$enabled` and `$visible` are static. `Profiler::initialize()` doesn't reset them. However, `ToolbarServiceProvider::packageRegistered()` checks `env()` and sets them on each boot. Under Octane, if `packageRegistered` doesn't re-run, a `Toolbar::disable()` call in request A would persist. The `Toolbar::__construct()` doesn't reset statics either.

**Finding 15: CSP nonce check always truthy — CONFIRMED**
- Evidence: Line 280: `if("{$nonce}" !== null)` — when `$nonce` is null, this renders as `if("" !== null)` in JavaScript. A JS string `""` is never `null`, so this condition is always true. The strip-inline-styles logic always runs.

**Finding 16: HorizonController PHP fallback is macOS-only — CONFIRMED**
- Evidence: Lines 48-49 and 82-83: fallback path is `$_SERVER['HOME'].'/Library/Application Support/Herd/bin/php'` — macOS Herd-specific. Routes at `/_toolbar/horizon/*` use `web` middleware only, no auth guard. The `guardEnvironment()` check limits to `local`/`development` environments, which mitigates the auth concern somewhat.
