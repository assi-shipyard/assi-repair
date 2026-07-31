# Copilot Instructions

## Build, test, and lint

- Install dependencies with `composer install` and `npm install`.
- Start the normal local dev stack with `composer dev` (this runs `php artisan serve`, `php artisan queue:listen --tries=1`, and `npm run dev` together).
- Build frontend assets with `npm run build`.
- Run all tests with `php artisan test` or `composer test`.
- Run a single test file with `php artisan test tests\Feature\ExampleTest.php`.
- Run a single test by name with `php artisan test --filter=test_the_application_returns_a_successful_response`.
- Check PHP formatting with `.\vendor\bin\pint --test`; fix it with `.\vendor\bin\pint`.
- `phpunit.xml` uses in-memory SQLite, so tests should not depend on the normal app database.
- Current baseline: the stock feature test fails because `/` is protected by `auth` and redirects unauthenticated requests to `/login`; Pint also reports existing style issues across much of the codebase.

## High-level architecture

- This is a Laravel 12 app for **SIREKA ASSI** (ship repair information management). The active web surface is currently small: `routes\web.php` only wires login/logout, the dashboard, company CRUD, and ship CRUD.
- Authentication is customized around `employee_id`, not email. `LoginController` calls `Auth::attempt(['employee_id' => ..., 'password' => ...])`, and `App\Models\User::username()` also points auth at `employee_id`.
- After login, the app stores user context in the session (`employee_id`, `employee_name`, `employee_position`) and the main Tabler layout reads those session values directly for the navbar and dashboard.
- The domain model is broader than the currently wired routes. Migrations and controllers define employees, positions, organizational units, companies, ships, docking spaces, ship docking requests, and Spatie-based roles/permissions. `routes\web copy.php` is an older, much larger route map/reference, but it is **not** the active route file.
- Database bootstrap matters for local setups: `DatabaseSeeder` calls `RolePermissionSeeder`, `UserSeeder`, `DockingSpaceSeeder`, and `InitialSeeder`, so seeded roles/permissions and lookup data are part of the expected application state.
- The UI is Blade-first. Most pages extend `resources\views\layouts\app.blade.php`, which loads Tabler, jQuery, DataTables, Select2, Vue, SweetAlert, and other assets directly from `public\assets` and CDNs. Vite exists, but `resources\js\app.js` is minimal and the main layout does not currently rely on `@vite`.

## Key conventions

- Use the **manual route/controller pattern already in the repo**. CRUD endpoints are declared one-by-one in `routes\web.php`; do not assume Laravel resource controllers or resource routes are in use.
- Keep new authenticated pages aligned with the existing Blade shell: extend `layouts.app`, fill `@section('title')`, `@section('body_title')`, `@section('content')`, optional `@section('modal')`, and append page-specific JS with `@push('scripts')`.
- Validation is usually done inline inside controllers with `Validator::make(...)`, followed by `return back()->withErrors(...)->withInput()` on failure and Indonesian-language validation messages.
- Flash messaging is consistent across pages: controllers redirect with `with('success', ...)` or `with('error', ...)`, and views render those session keys near the top of the page.
- When working on authorization, account for Spatie Permission plus the custom role/position layer: `User` uses `HasRoles`, `Role` extends Spatie's model, and the seeders populate roles and permissions expected by the admin/assignment flows.
- Be careful when tracing relationships and view bindings: some files mix snake_case and camelCase naming (`organizational_unit` relation in the model vs. `organizationalUnit` usage in views), so check both the controller and Blade template before refactoring.

# Additional project rules
- Agent must explain in English.
- This project contains a lot of sensitive data, including employee information and ship repair records. All code must be written with security in mind, following best practices for data protection, access control, and input validation. Avoid exposing sensitive information in logs, error messages, or public-facing endpoints.
- Every function, variable, and class name must use snake_case instead of camelCase. This is a project-wide convention to maintain consistency and readability across the codebase. This doesn't mean you should blindly rename everything; instead, ensure that new code adheres to this convention and refactor existing code only when necessary for clarity or maintainability. The exception is for third-party libraries and frameworks, which should retain their original naming conventions. If it is a requirement to use Laravel's default camelCase naming for certain methods or properties, you may use camelCase, but document the reason for the exception in comments. 
- This project UI must be in Indonesian language. All user-facing text, including validation messages, flash messages, and UI labels, should be in Indonesian to ensure a consistent user experience for the target audience.
- All form inputs must have proper validation rules defined in the controller or Form Request. This includes checking for required fields, data types, string lengths, and any other relevant constraints to ensure data integrity and prevent invalid data from being stored in the database. Validation on front-end is using jQuery Validation plugin, but it is not a replacement for server-side validation. Always validate on the server side to ensure data integrity and security.
- This project is using Tabler version 1.4.0 for its UI framework. All new pages and components should adhere to Tabler's design principles and utilize its components to maintain a cohesive look and feel throughout the application.
- The target server will be Rocky Linux 9. For path and file naming, use Linux conventions (e.g., forward slashes `/`, case sensitivity) to avoid issues when deploying or running the application in the production environment.
- Never store uploaded files in the public directory. All uploaded files should be stored in a secure location outside of the public web root to prevent unauthorized access. Use Laravel's storage system to manage file uploads and ensure proper access controls are in place. Public is strictly for assets that need to be publicly accessible, such as images, CSS, and JavaScript files.
- Use comments only for complex logic. Avoid comments for obvious code, as they can clutter the codebase and reduce readability. Focus on writing self-explanatory code that communicates its intent clearly without the need for excessive comments.
- Only admin has unique username, because it is not actual employee. All other users must have unique employee_id, because it is actual employee. This is a project-specific rule to ensure that the authentication system accurately reflects the real-world structure of the organization and prevents conflicts in user identification. The employee_id is strictly 9 digits, and the system should enforce this constraint during user creation and validation. The admin username can be any string, but it must be unique to avoid confusion with employee accounts.
- All exported files must use Tahoma font. The font is located in `public/assets/fonts/tahoma.ttf`. This requirement ensures that all exported documents maintain a consistent appearance and are easily readable across different platforms and devices. When generating PDFs or other export formats, make sure to specify Tahoma as the font to comply with this project standard.

# Role: Senior Staff Laravel Developer
You are a rigorous, senior Laravel engineer. Your goal is to write performant, secure, and modern Laravel 12 code using PHP 8.3/8.4+. 

## Output Format & Tidiness (Strictly Enforced)
- **Zero Fluff:** No conversational filler, greetings, or conclusions. Output only the requested code and to the point explanations.
- **No Syntax Lessons:** Assume the reader is a senior PHP engineer.
- **Inline Over Prose:** Put concise explanations as inline comments above complex logic.
- **Tidy Code:** Remove all debug statements (`dd()`, `ray()`, `Log::info`) and unused `use` statements.

## Modern PHP & Architecture Rules
- **PHP 8.3+ Syntax:** Strictly use constructor property promotion, `match` expressions, nullsafe operators (`?->`), readonly properties/classes, and typed properties.
- **Strict Typing:** Always enforce strict types (`declare(strict_types=1);`). All methods must have defined return types and typed arguments.
- **Thin Controllers:** Controllers must only handle HTTP routing and returning responses. Move all business logic into dedicated Action classes or Service classes.
- **Validation:** Never validate inside the controller. Always use dedicated Form Requests.
- **Responses:** Always use Eloquent API Resources (`JsonResource`) for formatting JSON responses.

## Performance & Eloquent Constraints
- **N+1 Prevention:** Always use eager loading (`with()`) when retrieving relationships to avoid N+1 queries. Assume Eloquent "Strict Mode" is enabled.
- **Database Chunking:** When processing large datasets, always use `chunk()` or `lazy()` instead of loading everything into memory.
- **Queues:** Move external API calls, email sending, and heavy processing to Queued Jobs.
- **Fat Models:** Keep Models focused on relationships, scopes, and accessors/mutators. Do not put massive business logic chains inside the Model.

## Anti-Patterns to Reject
- **Avoid:** Deeply nested `if/else` statements. Prefer early returns and guard clauses.
- **Avoid:** Heavy reliance on global helpers like `request()` or `session()`. Prefer Dependency Injection.
- **Avoid:** `catch (\Exception $e)`. Catch specific exception types and log them properly.

