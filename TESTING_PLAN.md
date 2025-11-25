# Laravel Toolbar Testing Plan

## Overview

This plan outlines the testing strategy for Laravel Toolbar using Pest. Priority is given to critical paths that could break in production.

## Current State

- Test framework: Pest with Orchestra Testbench
- Existing setup: `TestCase.php` configured but references wrong service provider (`LaravelToolbarServiceProvider` instead of `ToolbarServiceProvider`)
- Current tests: None (only `expect(true)->toBeTrue()`)

---

## Phase 1: Fix Test Infrastructure

### 1.1 Fix TestCase.php
- [ ] Correct service provider reference to `ToolbarServiceProvider`
- [ ] Add helper method to create mock HTTP responses
- [ ] Add helper to reset static state (Profiler)
- [ ] Configure in-memory SQLite for query tests

### 1.2 Create Test Helpers
- [ ] `tests/Helpers/MockResponse.php` - Factory for creating test responses
- [ ] Consider adding `pest-plugin-laravel` helpers if not already used

---

## Phase 2: Core Infrastructure Tests (High Priority)

### 2.1 ToolbarInjector Tests
**File:** `tests/Unit/ToolbarInjectorTest.php`

```
Priority: CRITICAL - This is the main entry point
```

Tests needed:
- [ ] `it injects toolbar html before closing body tag`
- [ ] `it does not inject into ajax requests`
- [ ] `it does not inject into non-html responses`
- [ ] `it does not inject into binary file responses`
- [ ] `it does not inject into streamed responses`
- [ ] `it does not inject when response has no body tag`
- [ ] `it detects inertia requests correctly`
- [ ] `it adds x-toolbar header for inertia requests`
- [ ] `it base64 encodes toolbar data in header`
- [ ] `it respects disabled toolbar state`
- [ ] `it includes csp nonce when available`

### 2.2 CollectorManager Tests
**File:** `tests/Unit/CollectorManagerTest.php`

```
Priority: CRITICAL - Orchestrates all data collection
```

Tests needed:
- [ ] `it collects data from all enabled collectors`
- [ ] `it skips disabled collectors`
- [ ] `it returns empty metadata when no collectors enabled`
- [ ] `it tracks wall time per collector in debug mode`
- [ ] `it caches data for mcp requests`
- [ ] `it generates unique request ids`
- [ ] `it handles collector exceptions gracefully` (currently missing - add try/catch)

### 2.3 Toolbar Tests
**File:** `tests/Unit/ToolbarTest.php`

Tests needed:
- [ ] `it is disabled when running in console`
- [ ] `it can be disabled via static property`
- [ ] `it detects telescope installation`
- [ ] `it initializes profiler on construction`

---

## Phase 3: Observer Tests (High Priority)

### 3.1 QueryObserver Tests
**File:** `tests/Unit/Observers/QueryObserverTest.php`

```
Priority: HIGH - Core feature, complex logic
```

Tests needed:
- [ ] `it records queries from QueryExecuted events`
- [ ] `it calculates total query time`
- [ ] `it detects duplicate queries by sql hash`
- [ ] `it marks slow queries over 100ms threshold`
- [ ] `it replaces bindings in sql correctly`
- [ ] `it handles null bindings`
- [ ] `it handles string bindings with quotes`
- [ ] `it handles numeric bindings`
- [ ] `it captures file and line in non-production`
- [ ] `it skips stack trace in production`
- [ ] `it tracks memory delta per query`

### 3.2 RequestObserver Tests
**File:** `tests/Unit/Observers/RequestObserverTest.php`

Tests needed:
- [ ] `it captures request start time`
- [ ] `it records request method and uri`

### 3.3 RoutingObserver Tests
**File:** `tests/Unit/Observers/RoutingObserverTest.php`

Tests needed:
- [ ] `it records routing checkpoint on RouteMatched event`

---

## Phase 4: Collector Tests (Medium Priority)

### 4.1 ProfilerCollector Tests
**File:** `tests/Unit/Collectors/ProfilerCollectorTest.php`

Tests needed:
- [ ] `it returns profiler data dto`
- [ ] `it calculates request stages from checkpoints`
- [ ] `it handles missing checkpoints gracefully`

### 4.2 QueriesCollector Tests
**File:** `tests/Unit/Collectors/QueriesCollectorTest.php`

Tests needed:
- [ ] `it returns queries data dto`
- [ ] `it calculates percentage per query`
- [ ] `it calculates offset for timeline visualization`
- [ ] `it retrieves queries from observer`

### 4.3 RequestCollector Tests
**File:** `tests/Unit/Collectors/RequestCollectorTest.php`

Tests needed:
- [ ] `it collects request method`
- [ ] `it collects request uri`
- [ ] `it collects controller action`
- [ ] `it collects middleware stack`

### 4.4 ResponseCollector Tests
**File:** `tests/Unit/Collectors/ResponseCollectorTest.php`

Tests needed:
- [ ] `it collects response status code`
- [ ] `it collects response headers`
- [ ] `it collects content length`

### 4.5 LaravelCollector Tests
**File:** `tests/Unit/Collectors/LaravelCollectorTest.php`

Tests needed:
- [ ] `it collects laravel version`
- [ ] `it collects environment`
- [ ] `it collects debug mode`
- [ ] `it respects config field toggles`

### 4.6 PhpCollector Tests
**File:** `tests/Unit/Collectors/PhpCollectorTest.php`

Tests needed:
- [ ] `it collects php version`

---

## Phase 5: Profiler Service Tests (Medium Priority)

### 5.1 Profiler Tests
**File:** `tests/Unit/Services/ProfilerTest.php`

```
Priority: MEDIUM - Static state needs careful testing
```

Tests needed:
- [ ] `it records checkpoints with timestamp and memory`
- [ ] `it throws on duplicate checkpoint`
- [ ] `it initializes laravel start from constant`
- [ ] `it registers booting and booted callbacks`
- [ ] `it calculates request stages correctly`
- [ ] `it returns null for missing checkpoints`
- [ ] `it tracks view renders`
- [ ] `it resets state between requests` (needs implementation)

---

## Phase 6: Configuration Tests (Medium Priority)

### 6.1 ToolbarConfig Tests
**File:** `tests/Unit/Data/ToolbarConfigTest.php`

Tests needed:
- [ ] `it enables debug mode`
- [ ] `it toggles debug mode`
- [ ] `it registers prepend middleware`
- [ ] `it registers append middleware to groups`
- [ ] `it sets observers`
- [ ] `it retrieves observer by class`
- [ ] `it sets collectors`
- [ ] `it validates collector interface`
- [ ] `it filters enabled collectors`
- [ ] `it enables toolbar`
- [ ] `it disables toolbar`

### 6.2 Collector Config Tests
**File:** `tests/Unit/Data/Configurations/CollectorConfigTest.php`

Tests needed:
- [ ] `it has enabled property`
- [ ] Each specific config (LaravelConfig, QueriesConfig, etc.) validates fields

---

## Phase 7: Integration Tests (Lower Priority)

### 7.1 Full Request Cycle Tests
**File:** `tests/Feature/ToolbarIntegrationTest.php`

Tests needed:
- [ ] `it injects toolbar into html response`
- [ ] `it tracks queries during request`
- [ ] `it records all profiler checkpoints`
- [ ] `it works with inertia requests`
- [ ] `it handles multiple sequential requests`

### 7.2 Middleware Tests
**File:** `tests/Feature/MiddlewareTest.php`

Tests needed:
- [ ] `middleware start records before_middleware checkpoint`
- [ ] `middleware start records after_middleware checkpoint`
- [ ] `middleware end records before_controller checkpoint`
- [ ] `middleware end records after_view checkpoint`

---

## Phase 8: Edge Cases & Regression Tests

### 8.1 Edge Cases
**File:** `tests/Unit/EdgeCasesTest.php`

Tests needed:
- [ ] `it handles empty response body`
- [ ] `it handles response without content-type header`
- [ ] `it handles malformed html`
- [ ] `it handles very large responses`
- [ ] `it handles concurrent requests` (static state issue)

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
   - Remove `ray()` call from `QueriesCollector.php:58`
   - Remove or fix broken `handleTelescopeEntries()` method
   - Delete `VueConfig copy.php`

2. Add try/catch in `CollectorManager` around collector execution

3. Consider adding state reset for `Profiler` static properties

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

- [ ] All critical paths tested (ToolbarInjector, CollectorManager, QueryObserver)
- [ ] 80%+ code coverage on src/ directory
- [ ] No failing tests in CI
- [ ] Edge cases documented and tested
