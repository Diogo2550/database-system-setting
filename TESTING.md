# Running Tests

This package uses PHPUnit and Orchestra Testbench for testing.

## Setup

Install dev dependencies:

```bash
composer install
```

## Running Tests

Run all tests:

```bash
./vendor/bin/phpunit
```

Run specific test suite:

```bash
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature
```

Run a specific test class:

```bash
./vendor/bin/phpunit tests/Unit/Models/SystemSettingTest.php
```

Run a specific test method:

```bash
./vendor/bin/phpunit --filter testCacheClearedOnSave
```

Generate coverage report:

```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Test Structure

- **tests/Unit** — Unit tests for individual components (models, utilities)
- **tests/Feature** — Integration tests for commands and service providers
- **tests/TestCase.php** — Base test case with package setup and configuration

## Test Coverage

The test suite covers:

- Model `SystemSetting` with fillable fields, casts, and cache invalidation
- Command `database-system-setting:sync` with settings creation and schema storage
- Command `database-system-setting:sync --prune` with orphaned settings deletion
- Service Provider with config merging, caching, and fallback behavior
- Handling of falsy values (0, false, empty string)
