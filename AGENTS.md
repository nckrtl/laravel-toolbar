# AGENTS.md - Laravel Toolbar

Guidelines for AI coding agents working in this repository.

## Build/Lint/Test Commands

### PHP (Backend)

```bash
# Run all tests
composer test

# Run single test file
./vendor/bin/pest tests/Unit/Observers/QueryObserverTest.php

# Run single test by name
./vendor/bin/pest --filter="records queries from QueryExecuted events"

# Run tests with coverage
composer test-coverage

# Static analysis (PHPStan level 5)
composer analyse

# Format code (Laravel Pint)
composer format
```

### JavaScript/Vue (Frontend)

```bash
# Development server with HMR
npm run dev

# Production build
npm run build

# Format code (Prettier)
npm run format

# Check formatting
npm run format:check

# Lint (oxlint)
npm run oxlint

# TypeScript type check
npm run typecheck

# Generate TS types from PHP DTOs
npm run generate-types
```

## Project Structure

```
src/                    # PHP source (NckRtl\Toolbar\)
  Collectors/           # Data collectors (interface + impl)
  Data/                 # Spatie Laravel Data DTOs
  Observers/            # Event listeners (Query, Model, etc.)
  Http/Middleware/      # Request lifecycle hooks
  Services/             # Profiler service
resources/js/           # Vue 3 frontend
  components/           # Reusable Vue components
  composables/          # Vue composables
  core/                 # Mount logic, interceptors
  tools/                # Toolbar tool components
tests/
  Feature/              # Integration tests
  Unit/                 # Unit tests
```

## PHP Code Style

### Formatting

- **Formatter**: Laravel Pint (PSR-12 + Laravel preset)
- **Static Analysis**: PHPStan level 5 with Larastan
- **PHP Version**: 8.4+
- **Indentation**: 4 spaces

### Naming Conventions

- Classes: `PascalCase`
- Methods/functions: `camelCase`
- Properties: `camelCase`
- Constants/Enums: `SCREAMING_SNAKE_CASE` for values, `PascalCase` for enum names

### Code Patterns

```php
<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\Data\QueriesData;  // Grouped imports: NckRtl first, then vendor

class QueriesCollector extends Collector implements CollectorInterface
{
    public float $totalTime = 0;           // Typed properties
    public array $queries = [];

    public function collectData(CollectorManager $manager): QueriesData  // Return types
    {
        return new QueriesData(
            totalTime: $this->totalTime,   // Named arguments
            queries: $this->queries,
        );                                  // Trailing comma in multi-line
    }
}
```

### Key Patterns

- Use Spatie Laravel Data for all DTOs
- Constructor property promotion preferred
- Enums for constant sets (see `src/Enums/`)
- Fluent method chaining (return `$this`)
- Arrow functions: `fn($x) => $x->property`
- Observers implement `reset()` for Octane compatibility

### Forbidden

- `dd()`, `dump()`, `ray()` - enforced via ArchTest
- Missing type declarations on public methods

## JavaScript/Vue Code Style

### Formatting

- **Prettier** with Tailwind plugin
- `printWidth: 100`, `singleQuote: true`
- **Linter**: oxlint

### Vue Components

```vue
<script setup>
import { ref, computed } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue'; // Path alias @/

const props = defineProps({
    isActive: { type: Boolean, default: false },
    class: { type: String, default: 'px-2' },
});
</script>

<template>
    <ToolbarItem :is-active="isActive">
        <slot />
    </ToolbarItem>
</template>
```

### Key Patterns

- Vue 3 Composition API with `<script setup>`
- No `lang="ts"` in Vue files (plain JS in components)
- TypeScript for `.ts` files only
- Path alias: `@/` = `resources/js/`
- TailwindCSS v4 for styling
- Props use kebab-case in templates (`:is-active`)

## Testing Conventions

### Pest PHP (Backend)

```php
<?php

use NckRtl\Toolbar\Observers\QueryObserver;

beforeEach(function () {
    Schema::create('test_users', fn ($t) => $t->id()->string('name'));
});

afterEach(function () {
    Schema::dropIfExists('test_users');
});

it('records queries from QueryExecuted events', function () {
    $observer = new QueryObserver;
    DB::table('test_users')->insert(['name' => 'John']);

    expect($observer->queries)->not->toBeEmpty();
    expect($observer->queries)->toHaveCount(1);
});

it('detects duplicate queries by sql hash', function () {
    $observer = new QueryObserver;

    DB::table('test_users')->where('id', 1)->first();
    DB::table('test_users')->where('id', 1)->first();

    expect($observer->queries[1]->is_duplicate)->toBeTrue();
});
```

### Test Patterns

- Use `it()` syntax with descriptive names
- `beforeEach`/`afterEach` for setup/teardown
- Pest expectation API: `expect()->toBe()`, `->toBeTrue()`, etc.
- Test files: `*Test.php`

## Architecture Notes

### Shadow DOM Isolation

The toolbar injects into user apps via Shadow DOM for CSS isolation. JavaScript runs in the same context (intentional for fetch/XHR interceptors).

### Observers (Event Listeners)

- `QueryObserver`: Records DB queries via `QueryExecuted` event
- `ModelObserver`: Tracks Eloquent hydrations
- `RoutingObserver`: Captures route timing
- All implement `reset()` for Octane compatibility

### Collectors (Data Gatherers)

- Implement `CollectorInterface`
- Return Spatie Data DTOs from `collectData()`
- Have corresponding `*Config.php` for field toggles

### Inertia.js Support

XHR responses include `x-toolbar` header (base64 JSON). Frontend intercepts fetch/XHR to update toolbar without page reload.

## Common Tasks

### Adding a Collector

1. Create `src/Collectors/MyCollector.php` implementing `CollectorInterface`
2. Create `src/Data/MyData.php` (Spatie Data DTO)
3. Create `src/Data/Configurations/MyConfig.php`
4. Create `resources/js/tools/MyTool.vue`
5. Register in `ToolbarConfig::collectors()`

### Running Single Test

```bash
# By file
./vendor/bin/pest tests/Unit/Observers/QueryObserverTest.php

# By name filter
./vendor/bin/pest --filter="detects duplicate"

# With verbose output
./vendor/bin/pest --filter="detects duplicate" -v
```
