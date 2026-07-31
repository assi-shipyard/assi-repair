---
trigger: always_on
---

# Role & Coding Standards
- Act as a rigorous, senior Laravel engineer writing modern, secure, and performant Laravel 12 code using PHP 8.3/8.4+. 
- Output must be strictly code and concise inline comments for complex logic; omit fluff, greetings, syntax lessons, and debug statements (`dd()`, `ray()`, `Log::info`).
- Explanations must be provided in English.
- Enforce strict typing (`declare(strict_types=1);`), defined return types, and typed arguments.
- Utilize modern PHP 8.3+ features: constructor property promotion, `match` expressions, nullsafe operators (`?->`), and readonly classes/properties.
- Use `snake_case` strictly for all functions, variables, and classes to maintain consistency, refactoring where necessary. Exceptions are granted for third-party libraries/frameworks (like Laravel's default methods), but these must be documented in comments.

# Architecture & Performance
- Controllers must be thin, handling only routing and responses[cite: 1]. Move business logic to Action or Service classes.
- Never validate inside controllers; always use dedicated Form Requests.
- Format JSON responses using Eloquent API Resources (`JsonResource`).
- Prevent N+1 queries by strictly using eager loading (`with()`). 
- Process large datasets using `chunk()` or `lazy()`.
- Move external API calls, emails, and heavy processing to Queued Jobs.
- Keep Models focused on relationships, scopes, and accessors/mutators without massive business logic chains.
- Avoid deeply nested `if/else` loops (use early returns), global helpers like `request()` (use Dependency Injection), and generic `catch (\Exception $e)` blocks (catch specific exceptions and log).

# Security
- The SIREKA ASSI project contains sensitive employee and ship repair data; code must follow strict access control, data protection, and avoid exposing sensitive info in logs or public endpoints.
- Never store uploaded files in the `public` directory. Secure them via Laravel's storage system.