---
trigger: glob
globs: resources/views/**/*.blade.php, resources/js/**/*.js
---

# UI Framework & Language
- The UI framework is Tabler v1.4.0; all pages must adhere to its design principles.
- The interface must be strictly in the Indonesian language; all user-facing text, validation messages, UI labels, and flash messages must be in Indonesian.

# Blade Conventions
- The UI is Blade-first. Extend `resources\views\layouts\app.blade.php` for most pages.
- Utilize standard sections: `@section('title')`, `@section('body_title')`, `@section('content')`, `@section('modal')`, and append JS via `@push('scripts')`.
- Be mindful of naming convention mix-ups in relations vs. views (e.g., `organizational_unit` vs `organizationalUnit`); verify both controller and template before refactoring.
- Controllers redirect with `with('success', ...)` or `with('error', ...)` which the views render near the top of the page[cite: 1].

# Validation
- Client-side validation is handled via the jQuery Validation plugin, but it does not replace the mandatory server-side Form Requests.