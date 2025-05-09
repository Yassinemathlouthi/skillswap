# SkillSwap Testing Guide

This document provides an overview of the testing structure for SkillSwap, a peer-to-peer skill sharing platform.

## Test Structure

The tests are organized into the following categories:

### Unit Tests (`/tests/Unit/`)
- Tests individual components in isolation
- Fast execution, no database or external service dependencies
- Located in:
  - `/tests/Unit/Entity/` - Tests entity methods and properties
  - `/tests/Unit/Service/` - Tests service class logic

### Functional Tests (`/tests/Functional/`)
- Tests entire features through the HTTP layer
- Simulates user interactions with the application
- Tests routes, forms, and responses

### Integration Tests (`/tests/Integration/`)
- Tests multiple components working together
- Ensures different parts of the application integrate properly
- Verifies end-to-end workflows like user registration and session booking

### Performance Tests (`/tests/Performance/`)
- Benchmarks application performance under various conditions
- Tests response times for critical functionality
- Identifies potential bottlenecks

## Running Tests

### Prerequisites

Make sure you have:
1. A test database configured in your `.env.test` file
2. All dependencies installed via Composer

### Running All Tests

```bash
php bin/phpunit
```

### Running Specific Test Categories

```bash
# Run only unit tests
php bin/phpunit --testsuite=unit

# Run only functional tests
php bin/phpunit --testsuite=functional

# Run only integration tests
php bin/phpunit --testsuite=integration

# Run only performance tests
php bin/phpunit --testsuite=performance
```

### Running a Single Test Class

```bash
php bin/phpunit tests/Unit/Entity/UserTest.php
```

### Running a Specific Test Method

```bash
php bin/phpunit --filter=testUserRoles tests/Unit/Entity/UserTest.php
```

## Test Database Setup

The tests use a separate database specified in your `.env.test` file. Before running tests, ensure your test database is properly configured:

```
# .env.test
DATABASE_URL="mongodb://localhost:27017/skillswap_test"
```

## Coverage Reports

Generate test coverage reports with:

```bash
php bin/phpunit --coverage-html var/coverage
```

Then open `var/coverage/index.html` in your browser to view the coverage report.

## Writing New Tests

When adding new functionality to the application, follow these guidelines:

1. **For entities and services**: Add unit tests in the appropriate directory under `/tests/Unit/`
2. **For controllers and routes**: Add functional tests under `/tests/Functional/`
3. **For complex workflows**: Add integration tests under `/tests/Integration/`
4. **For performance-critical features**: Add performance tests under `/tests/Performance/`

## Test Data

Most tests create their own test data. After tests complete, this data is cleaned up to prevent test pollution. If you need to use specific test fixtures, place them in a `fixtures` directory within the appropriate test category.

## Best Practices

- Keep unit tests fast and focused
- Use mocks and stubs for external dependencies in unit tests
- In integration tests, test the entire workflow from start to finish
- Include both happy path and error cases in your tests
- Don't rely on specific database IDs or timestamps
- Use unique identifiers for test data to prevent test conflicts
- Clean up created test data in tearDown() methods 