# Laravel Toolbar Testing Plan

## Overview

This plan outlines the testing strategy for Laravel Toolbar using Pest. Priority is given to critical paths that could break in production.

## Current State

- Test framework: Pest v4 with Orchestra Testbench (properly configured)
- Tests: 161 passing (233 assertions)
- Helpers: `MockResponse.php` created
- TestCase includes `LaravelDataServiceProvider` for Spatie Data support

---

## Phase 1: Fix Test Infrastructure ✅ COMPLETE

### 1.1 Fix TestCase.php
- [x] Correct service provider reference to `ToolbarServiceProvider`
- [x] Add helper method to create mock HTTP responses
- [ ] Add helper to reset static state (Profiler)
- [x] Configure in-memory SQLite for query tests

### 1.2 Create Test Helpers
- [x] `tests/Helpers/MockResponse.php` - Factory for creating test responses
- [x] `pest-plugin-laravel` already in use

---

## Phase 2: Core Infrastructure Tests (High Priority)

### 2.1 ToolbarInjector Tests ✅ COMPLETE
**File:** `tests/Unit/ToolbarInjectorTest.php`

```
Priority: CRITICAL - This is the main entry point
```

Tests needed:
- [x] `it injects toolbar html before closing body tag`
- [x] `it does not inject into ajax requests`
- [x] `it does not inject into non-html responses`
- [x] `it does not inject into binary file responses`
- [x] `it does not inject into streamed responses`
- [x] `it does not inject when response has no body tag`
- [x] `it detects inertia requests correctly`
- [x] `it adds x-toolbar header for inertia requests`
- [x] `it base64 encodes toolbar data in header`
- [x] `it respects disabled toolbar state`
- [x] `it includes csp nonce when available`

### 2.2 CollectorManager Tests ✅ COMPLETE
**File:** `tests/Unit/CollectorManagerTest.php`

```
Priority: CRITICAL - Orchestrates all data collection
```

Tests needed:
- [x] `it collects data from all enabled collectors`
- [x] `it skips disabled collectors`
- [x] `it returns empty metadata when no collectors enabled`
- [x] `it tracks wall time per collector`
- [x] `it caches data for requests`
- [x] `it caches data with mcp request id when provided`
- [x] `it generates unique request ids`
- [x] `it includes debug metadata when debug mode enabled`
- [x] `it does not include debug metadata when debug mode disabled`
- [x] `it includes layout configuration in data`
- [x] `it accepts response in constructor`
- [ ] `it handles collector exceptions gracefully` (currently missing - add try/catch)

### 2.3 Toolbar Tests ✅ COMPLETE
**File:** `tests/Unit/ToolbarTest.php`

Tests needed:
- [x] `it is disabled when running in console by default`
- [x] `it can be enabled in console mode`
- [x] `it can be disabled via static method`
- [x] `it can be enabled via static method`
- [x] `it reports disabled when static property is false`
- [x] `it detects telescope installation`
- [x] `it initializes with toolbar config`
- [x] `it has empty collectors array by default`
- [x] `it has empty queries array by default`
- [x] `it initializes query memory to zero`

---

## Phase 3: Observer Tests (High Priority) ✅ COMPLETE

### 3.1 QueryObserver Tests ✅ COMPLETE
**File:** `tests/Unit/Observers/QueryObserverTest.php`

```
Priority: HIGH - Core feature, complex logic
```

Tests needed:
- [x] `it records queries from QueryExecuted events`
- [x] `it calculates total query time`
- [x] `it detects duplicate queries by sql hash`
- [x] `it marks slow queries over 100ms threshold`
- [x] `it does not mark fast queries as slow`
- [x] `it replaces bindings in sql correctly`
- [x] `it handles null bindings`
- [x] `it handles numeric bindings`
- [x] `it captures file and line in non-production`
- [x] `it tracks memory usage per query`
- [x] `it tracks connections`
- [x] `it tracks drivers`
- [x] `it tracks databases`
- [x] `it generates hash for queries`
- [x] `it detects duplicate hashes`
- [x] `it records query connection name`
- [x] `it records query driver name`

### 3.2 RequestObserver Tests ✅ COMPLETE
**File:** `tests/Unit/Observers/RequestObserverTest.php`

Tests needed:
- [x] `it listens to RequestHandled event`
- [x] `it records REQUEST_HANDLED checkpoint`
- [x] `it injects toolbar into response`

### 3.3 RoutingObserver Tests ✅ COMPLETE
**File:** `tests/Unit/Observers/RoutingObserverTest.php`

Tests needed:
- [x] `it records BEFORE_ROUTING checkpoint on Routing event`
- [x] `it records AFTER_ROUTING checkpoint on RouteMatched event`
- [x] `it listens to Routing event`
- [x] `it listens to RouteMatched event`

---

## Phase 4: Collector Tests (Medium Priority) ✅ COMPLETE

### 4.1 ProfilerCollector Tests ✅ COMPLETE
**File:** `tests/Unit/Collectors/ProfilerCollectorTest.php`

Tests needed:
- [x] `it has correct key`
- [x] `it has correct config class`
- [x] `it implements collector interface`
- [x] `it extends base collector`
- [x] `it has default config`

Note: Full data collection tests require integration testing due to complex checkpoint dependencies.

### 4.2 QueriesCollector Tests ✅ COMPLETE
**File:** `tests/Unit/Collectors/QueriesCollectorTest.php`

Tests needed:
- [x] `it returns queries data dto`
- [x] `it has correct key`
- [x] `it has correct config class`
- [x] `it retrieves queries from observer`
- [x] `it calculates total time from queries`
- [x] `it calculates percentage per query`
- [x] `it calculates offset for timeline visualization`
- [x] `it tracks connections`
- [x] `it tracks drivers`
- [x] `it tracks databases`
- [x] `it filters session queries by default`
- [x] `it shows session queries when config enabled`

### 4.3 RequestCollector Tests ✅ COMPLETE
**File:** `tests/Unit/Collectors/RequestCollectorTest.php`

Tests needed:
- [x] `it returns request data dto`
- [x] `it has correct key`
- [x] `it has correct config class`
- [x] `it collects request method`
- [x] `it collects request uri`
- [x] `it collects ip address`
- [x] `it detects inertia requests`
- [x] `it detects non-inertia requests`
- [x] `it collects controller action when route is matched`
- [x] `it returns dash for controller action when no route matched`
- [x] `it collects middleware stack when route is matched`
- [x] `it returns empty middleware array when no route matched`

### 4.4 ResponseCollector Tests ✅ COMPLETE
**File:** `tests/Unit/Collectors/ResponseCollectorTest.php`

Tests needed:
- [x] `it returns response data dto`
- [x] `it has correct key`
- [x] `it has correct config class`
- [x] `it collects response status code`
- [x] `it collects different status codes`
- [x] `it collects response headers`
- [x] `it collects content size`
- [x] `it formats size correctly`

### 4.5 LaravelCollector Tests ✅ COMPLETE
**File:** `tests/Unit/Collectors/LaravelCollectorTest.php`

Tests needed:
- [x] `it returns laravel data dto`
- [x] `it has correct key`
- [x] `it has correct config class`
- [x] `it collects laravel version`
- [x] `it collects environment`
- [x] `it collects timezone`
- [x] `it collects locale`
- [x] `it collects debug mode`
- [x] `it respects config field toggles for version`
- [x] `it respects config field toggles for environment`
- [x] `it respects config field toggles for timezone`
- [x] `it respects config field toggles for locale`
- [x] `it respects config field toggles for debug`

### 4.6 PhpCollector Tests ✅ COMPLETE
**File:** `tests/Unit/Collectors/PhpCollectorTest.php`

Tests needed:
- [x] `it returns php data dto`
- [x] `it has correct key`
- [x] `it has correct config class`
- [x] `it collects php version`
- [x] `it collects memory limit`
- [x] `it collects max execution time`

---

## Phase 5: Profiler Service Tests (Medium Priority) ✅ COMPLETE

### 5.1 Profiler Tests ✅ COMPLETE
**File:** `tests/Unit/Services/ProfilerTest.php`

```
Priority: MEDIUM - Static state needs careful testing
```

Tests needed:
- [x] `it records checkpoints`
- [x] `it records checkpoints with timestamp`
- [x] `it records checkpoints with memory`
- [x] `it creates default checkpoint data when none provided`
- [x] `it accepts custom checkpoint data`
- [x] `it silently ignores duplicate checkpoint recordings`
- [x] `it returns null for missing checkpoints`
- [x] `it gets checkpoint by id`
- [x] `it returns null for first view render when no views rendered`
- [x] `it returns null for last view render when no views rendered`
- [x] `it tracks view renders`
- [x] `it gets first view render`
- [x] `it gets last view render`
- [x] `it gets current memory usage from latest checkpoint with memory`
- [x] `it throws exception when no checkpoints with memory exist`
- [x] `it returns request stages array`
- [x] `it clears checkpoints after getting request stages`
- [x] `it can record checkpoint without memory measurement`
- [x] `it stores checkpoints by their enum value`

---

## Phase 6: Configuration Tests (Medium Priority) ✅ COMPLETE

### 6.1 ToolbarConfig Tests ✅ COMPLETE
**File:** `tests/Unit/Data/ToolbarConfigTest.php`

Tests needed:
- [x] `it can be instantiated`
- [x] `it has debug mode disabled by default`
- [x] `it enables debug mode`
- [x] `it disables debug mode`
- [x] `it toggles debug mode when called without argument`
- [x] `it returns self when setting debug for chaining`
- [x] `it sets observers`
- [x] `it returns self when setting observers for chaining`
- [x] `it retrieves observer by class`
- [x] `it returns null when observer not found`
- [x] `it sets collectors`
- [x] `it returns self when setting collectors for chaining`
- [x] `it validates collector implements interface`
- [x] `it throws when collector does not implement interface`
- [x] `it filters enabled collectors`
- [x] `it returns all collectors when all are enabled`
- [x] `it enables toolbar via config`
- [x] `it disables toolbar via config`
- [x] `it has console mode disabled by default`
- [x] `it enables console mode`
- [x] `it disables console mode`
- [x] `it returns self when enabling console mode for chaining`
- [x] `it updates collector config`
- [x] `it throws exception when updating non-existent collector config`
- [x] `it has layout configuration`
- [x] `it sets empty collectors when empty array passed`
- [x] `it has default collectors on initialization`
- [x] `it has default observers on initialization`

### 6.2 Collector Config Tests ✅ COMPLETE
**File:** `tests/Unit/Data/Configurations/CollectorConfigTest.php`

Tests needed:
- [x] `it CollectorConfig interface requires isEnabled method`
- [x] `it LaravelConfig implements CollectorConfig`
- [x] `it LaravelConfig is enabled by default`
- [x] `it LaravelConfig can be disabled`
- [x] `it LaravelConfig has version/environment/debug/timezone/locale fields enabled by default`
- [x] `it LaravelConfig can disable individual fields`
- [x] `it PhpConfig implements CollectorConfig`
- [x] `it PhpConfig is enabled by default`
- [x] `it PhpConfig can be disabled`
- [x] `it ProfilerConfig implements CollectorConfig`
- [x] `it ProfilerConfig is enabled by default`
- [x] `it ProfilerConfig can be disabled`
- [x] `it QueriesConfig implements CollectorConfig`
- [x] `it QueriesConfig is enabled by default`
- [x] `it QueriesConfig can be disabled`
- [x] `it QueriesConfig shows session queries by default`
- [x] `it QueriesConfig can hide session queries`
- [x] `it RequestConfig implements CollectorConfig`
- [x] `it RequestConfig is enabled by default`
- [x] `it RequestConfig can be disabled`
- [x] `it RequestConfig has null dataProvider by default`
- [x] `it RequestConfig can set dataProvider`
- [x] `it ResponseConfig implements CollectorConfig`
- [x] `it ResponseConfig is enabled by default`
- [x] `it ResponseConfig can be disabled`
- [x] `it ModelsConfig implements CollectorConfig`
- [x] `it ModelsConfig is enabled by default`
- [x] `it ModelsConfig can be disabled`

---

## Phase 7: Integration Tests (Lower Priority) ✅ COMPLETE

### 7.1 Full Request Cycle Tests ✅ COMPLETE
**File:** `tests/Feature/ToolbarIntegrationTest.php`

Tests needed:
- [x] `it injects toolbar into html response`
- [x] `it does not inject toolbar into json response`
- [x] `it adds x-toolbar header for inertia requests`
- [x] `it tracks queries during request`
- [x] `it respects disabled toolbar state`
- [x] `it handles multiple sequential requests`
- [x] `it includes request metadata in toolbar data`
- [x] `it collects laravel environment data`
- [x] `it collects php info data`

### 7.2 Middleware Tests ✅ COMPLETE
**File:** `tests/Feature/MiddlewareTest.php`

Tests needed:
- [x] `it MiddlewareStart records BEFORE_MIDDLEWARE checkpoint`
- [x] `it MiddlewareStart records AFTER_MIDDLEWARE checkpoint`
- [x] `it MiddlewareStart does not overwrite existing BEFORE_MIDDLEWARE checkpoint`
- [x] `it MiddlewareStart does not overwrite existing AFTER_MIDDLEWARE checkpoint`
- [x] `it MiddlewareStart returns response from next middleware`
- [x] `it MiddlewareEnd records BEFORE_CONTROLLER checkpoint`
- [x] `it MiddlewareEnd records AFTER_VIEW_RENDERING checkpoint`
- [x] `it MiddlewareEnd does not overwrite existing BEFORE_CONTROLLER checkpoint`
- [x] `it MiddlewareEnd does not overwrite existing AFTER_VIEW_RENDERING checkpoint`
- [x] `it MiddlewareEnd returns response from next middleware`
- [x] `it both middleware create proper checkpoint sequence`
- [x] `it checkpoint order is correct in middleware flow`

---

## Phase 8: Edge Cases & Regression Tests ✅ COMPLETE

### 8.1 Edge Cases ✅ COMPLETE
**File:** `tests/Unit/EdgeCasesTest.php`

Tests needed:
- [x] `it handles empty response body`
- [x] `it does not inject when content has no body tag`
- [x] `it injects when content-type header is missing but has body tag`
- [x] `it handles response with charset in content-type`
- [x] `it injects for xhtml content type with body tag`
- [x] `it handles html without body tag`
- [x] `it does not inject for uppercase BODY tag due to case sensitivity`
- [x] `it does not inject for mixed case Body tag due to case sensitivity`
- [x] `it handles multiple body tags - injects before last`
- [x] `it handles large html response`
- [x] `it handles html with special characters`
- [x] `it handles html with unicode content`
- [x] `it handles POST request`
- [x] `it handles PUT request`
- [x] `it handles DELETE request`
- [x] `it handles 404 response`
- [x] `it handles 500 response`
- [x] `it handles 301 redirect with html body`
- [x] `it CollectorManager handles empty collectors`
- [x] `it CollectorManager generates unique ids`
- [x] `it Profiler handles getting stages with no checkpoints`

---

## Test File Structure

```
tests/
├── Pest.php
├── TestCase.php
├── Helpers/
│   └── MockResponse.php
├── Unit/
│   ├── ToolbarTest.php
│   ├── ToolbarInjectorTest.php
│   ├── CollectorManagerTest.php
│   ├── Observers/
│   │   ├── QueryObserverTest.php
│   │   ├── RequestObserverTest.php
│   │   └── RoutingObserverTest.php
│   ├── Collectors/
│   │   ├── ProfilerCollectorTest.php
│   │   ├── QueriesCollectorTest.php
│   │   ├── RequestCollectorTest.php
│   │   ├── ResponseCollectorTest.php
│   │   ├── LaravelCollectorTest.php
│   │   └── PhpCollectorTest.php
│   ├── Services/
│   │   └── ProfilerTest.php
│   └── Data/
│       ├── ToolbarConfigTest.php
│       └── Configurations/
│           └── CollectorConfigTest.php
├── Feature/
│   ├── ToolbarIntegrationTest.php
│   └── MiddlewareTest.php
└── EdgeCasesTest.php
```

---

## Execution Order

1. **Phase 1** - Fix test infrastructure (required first)
2. **Phase 2** - Core infrastructure (ToolbarInjector, CollectorManager)
3. **Phase 3** - Observers (QueryObserver is highest value)
4. **Phase 4** - Collectors (one by one)
5. **Phase 5** - Profiler service
6. **Phase 6** - Configuration
7. **Phase 7** - Integration tests
8. **Phase 8** - Edge cases

---

## Prerequisites Before Starting

1. Delete dead code:
   - [ ] Remove `ray()` call from `ModelObserver.php:51` (commented out but still present)
   - [ ] Remove or fix broken `handleTelescopeEntries()` method
   - [ ] Delete `VueConfig copy.php` (still exists at `src/Data/Configurations/VueConfig copy.php`)

2. [ ] Add try/catch in `CollectorManager` around collector execution

3. [ ] Consider adding state reset for `Profiler` static properties

---

## Running Tests

```bash
# Run all tests
composer test

# Run specific file
./vendor/bin/pest tests/Unit/ToolbarInjectorTest.php

# Run with coverage
composer test-coverage

# Run specific group (after adding groups)
./vendor/bin/pest --group=collectors
```

---

## Success Criteria

- [x] ToolbarInjector fully tested (11 tests passing)
- [x] CollectorManager tested (11 tests passing)
- [x] Toolbar tested (10 tests passing)
- [x] QueryObserver tested (17 tests passing)
- [x] RequestObserver tested (3 tests passing)
- [x] RoutingObserver tested (4 tests passing)
- [x] All Collectors tested (56 tests passing)
- [x] Profiler Service tested (19 tests passing)
- [x] ToolbarConfig tested (28 tests passing)
- [x] Collector Configs tested (32 tests passing)
- [x] Integration tests completed (9 tests passing)
- [x] Middleware tests completed (12 tests passing)
- [x] Edge cases documented and tested (21 tests passing)
- [ ] 80%+ code coverage on src/ directory
- [ ] No failing tests in CI

---

## Progress Summary

| Phase | Status | Tests |
|-------|--------|-------|
| Phase 1: Infrastructure | ✅ Complete | - |
| Phase 2.1: ToolbarInjector | ✅ Complete | 11 |
| Phase 2.2: CollectorManager | ✅ Complete | 11 |
| Phase 2.3: Toolbar | ✅ Complete | 10 |
| Phase 3.1: QueryObserver | ✅ Complete | 17 |
| Phase 3.2: RequestObserver | ✅ Complete | 3 |
| Phase 3.3: RoutingObserver | ✅ Complete | 4 |
| Phase 4.1: ProfilerCollector | ✅ Complete | 5 |
| Phase 4.2: QueriesCollector | ✅ Complete | 12 |
| Phase 4.3: RequestCollector | ✅ Complete | 12 |
| Phase 4.4: ResponseCollector | ✅ Complete | 8 |
| Phase 4.5: LaravelCollector | ✅ Complete | 13 |
| Phase 4.6: PhpCollector | ✅ Complete | 6 |
| Phase 5: Profiler Service | ✅ Complete | 19 |
| Phase 6.1: ToolbarConfig | ✅ Complete | 28 |
| Phase 6.2: Collector Configs | ✅ Complete | 32 |
| Phase 7.1: Integration Tests | ✅ Complete | 9 |
| Phase 7.2: Middleware Tests | ✅ Complete | 12 |
| Phase 8: Edge Cases | ✅ Complete | 21 |

**Total Tests:** 235 (233 Unit/Feature + 1 Example + 1 Arch)
