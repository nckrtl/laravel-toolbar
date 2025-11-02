# Contributing

Thank you for considering contributing to Laravel Toolbar! This document provides guidelines and instructions for contributing.

## Development Setup

### Requirements

- PHP 8.4+
- Node.js 18+ (for frontend development)
- Composer

### Installation

1. Fork and clone the repository:
   ```bash
   git clone https://github.com/YOUR_USERNAME/laravel-toolbar.git
   cd laravel-toolbar
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies (for frontend development):
   ```bash
   npm install
   ```

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run specific test file
vendor/bin/pest tests/Unit/YourTest.php
```

### Code Quality

Before submitting a PR, ensure your code passes all quality checks:

```bash
# Static analysis (PHPStan Level 5)
composer analyse

# Code style (Laravel Pint)
composer format
```

### Frontend Development

For working on the Vue.js toolbar UI:

```bash
# Start Vite dev server with HMR
npm run dev

# Build for production
npm run build
```

## Pull Request Guidelines

1. **Create a feature branch** from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Write tests** for new functionality

3. **Follow existing code style** - run `composer format` before committing

4. **Update documentation** if your changes affect the public API

5. **Keep PRs focused** - one feature or fix per PR

6. **Write clear commit messages** describing what changed and why

## Architecture Overview

- `src/Collectors/` - Data collection modules (implement `CollectorInterface`)
- `src/Observers/` - Event listeners for Laravel events
- `src/Http/Middleware/` - Request lifecycle middleware
- `src/Data/` - DTOs using Spatie Laravel Data
- `resources/js/` - Vue 3 frontend (Shadow DOM isolated)
- `resources/css/` - TailwindCSS styles

## Adding a New Collector

1. Create collector class in `src/Collectors/`:
   ```php
   class MyCollector extends Collector implements CollectorInterface
   {
       public function key(): string
       {
           return 'my_collector';
       }

       public function collectData(CollectorManager $manager): MyData
       {
           return new MyData(/* ... */);
       }
   }
   ```

2. Create DTO in `src/Data/`
3. Create config class in `src/Data/Configurations/`
4. Create Vue component in `resources/js/collectors/`
5. Add tests in `tests/Unit/Collectors/`

## Questions?

Feel free to open an issue for questions or discussions about contributing.
