---
name: workflow
description: Standard build, test, and linting commands for the SIREKA ASSI project.
---

# Build & Dev Environment
- Install dependencies: `composer install` and `npm install`.
- Local dev stack: Run `composer dev` to simultaneously start `php artisan serve`, `php artisan queue:listen --tries=1`, and `npm run dev`.
- Build assets: `npm run build`.

# Testing & Linting
- The `phpunit.xml` configuration uses in-memory SQLite, so tests run independently of the main database.
- Run all tests: `php artisan test` or `composer test`.
- Run a single test file: `php artisan test tests\Feature\ExampleTest.php`.
- Run a specific test: `php artisan test --filter=test_name`.
- Check formatting: `.\vendor\bin\pint --test`.
- Fix formatting: `.\vendor\bin\pint`.
- Baseline context: The stock feature test fails due to auth redirects to `/login`, and Pint currently reports existing style issues.