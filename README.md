<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Kamus Harga

The **Kamus Harga** menu manages independent price books. One book can be the
shipyard's main reference; other books may optionally be associated with a
company for information, and a company may have multiple books for different
agreements. Each project explicitly selects any book on its create/edit form,
regardless of the ship's customer. No book is selected automatically; existing
projects remain unassigned until a book is selected. Selecting or changing a
book does not modify saved BoQ prices. Existing **Data Jenis Reparasi**
remains separate and unchanged.

The book dashboard uses responsive cards with material/service counts. Search
by book code, book name, or customer name, and filter by book type and customer.
Filters are applied across all books and retained when navigating result pages.

Each book contains **Material** and **Jasa** entries with an item code, name, unit,
IDR unit price, and an optional minimum billable quantity in that same unit
(for example, 15 kg of steel or 8 hours of crane service). The same code can
have separate prices for different units. No conversion between units or
inheritance from the main book is performed.

Authenticated users can read books; only administrators can change them.
Resource URLs use UUIDs. Each mutation stores a transactional before/after
database audit record. Books containing entries or used by projects cannot be deleted.
Run `php artisan migrate` to install the price-book tables.
This menu manages the price catalog only; it does not change existing project
document pricing or apply minimum quantities to project totals yet.

## Subcontractor master data

The `subcontractors` table and `App\Models\subcontractor` represent external
company/vendors, separately from customer companies, internal employees, and
login accounts. Each vendor has a unique code and automatically generated public
UUID, a company name, and a contact person's name. Phone, email, address, and
internal notes are optional. UUIDs are used for future route binding.

Vendors default to active. Use the `active()` query scope for future assignment
selectors; deactivate vendors rather than deleting historical references.
Run `php artisan migrate` to install the table.

Internal users can open **Database > Data Subkontraktor** to view a paginated
vendor list, including active and inactive vendors. Administrators can add/edit
vendors and activate/deactivate them using the Status field; deletion is not
offered. Guests and customer accounts cannot access these pages.
Mutations use server-side Form Request validation, jQuery form validation,
UUID URLs, and transactional before/after database audit records.

This is vendor master data only. For example, a rampdoor repair job can have
**Bengkel Konstruksi dan Pipa** as its internal PIC unit and **CV. Berjaya Las**
as its subcontractor; the subcontractor does not replace the internal PIC.
Job-level vendor and internal PIC/employee assignments are deferred until
users fill the Satisfaction Notes report.
Existing free-text job responsibility fields remain unchanged and are not
automatically linked to vendors. The future assignment workflow must
validate input, authorize access, and persist database audit records transactionally.
Payments, bank details, and other finance workflows are deferred.

## Accounts and customer portal

Run the migrations before using the new account screens. Existing employee
accounts remain `internal`; existing users with the Spatie `admin` role are
backfilled with the `admin` account type and a separate username. The legacy
admin identifier remains in `employee_id` for compatibility with existing data.
`AdminSeeder` also populates the new fields.

Administrators use the separate **Akun Administrator** and **Akun Pelanggan**
directories to create/edit admin/customer accounts,
reset passwords, and activate or suspend accounts. Account type cannot be changed.
Internal employees continue to use the employee
form and their nine-digit NIK. The first digit cannot be zero because the first
four digits represent a year. The employee form accepts only digits and
validates this format locally without an availability
request. NIK format and uniqueness are checked again on the server when saving.
Customers sign in at `/customer/login` using
email, and must change their administrator-provisioned initial password before
accessing projects. Deliver initial credentials through an independently
verified, secure channel; automatic email invitations are not implemented.

**Karyawan Internal** and **Akun Pelanggan** use responsive profile cards with
server-side search, status filters, and twelve cards per page. Employee cards
show NIK, position, unit, manager, email, status, and existing profile photos.
Customer cards show company, email, status, active project access count, and
registration date. Admins have their own restricted directory.
Run the account-profile-photo migration to enable private customer/admin photo
uploads on create/edit forms. JPG, PNG, and WEBP uploads are limited to 2 MB and
4,000 x 4,000 pixels, stored on the private `ship` disk, and served only through
admin-authorized UUID routes. Accounts without a photo show a fallback avatar.

Under **Akses & Persetujuan Pelanggan**, assign each customer explicitly to
projects. Their company represents their employer, not necessarily the vessel
owner. A company association alone grants no access. Viewing prices and the
three approval categories are separate per-project permissions. RAB approval
requires financial access; external surveyors without financial access can
review technical details and approve **Catatan Kepuasan**.

Only the latest internally approved/locked RAB Awal, Catatan Kepuasan, or RAB
Akhir revision can be published. Publication creates an immutable allowlisted
snapshot with one designated customer approver. Pending requests are superseded
when a newer revision is published; completed decisions remain preserved.
Pending approvals can be reassigned to another authorized project representative.
Customer decisions do not change internal document approval status. Rejected
or change-requested documents require a new internal revision and publication.

RAB prices are omitted from locked HTML and JSON, including material prices.
An authorized customer must confirm their own password to unlock one document
for five minutes. Unlocks are tied to user, document, password fingerprint and
access version; access changes invalidate earlier unlocks. Responses are
`private, no-store`. Financial snapshots are encrypted using Laravel's application
key, which must be backed up and retained when rotating keys. Customer documents
never expose internal actual costs, internal notes, histories, or unshared files.
Published job prices use the same `est_price`/`est_currency` fields as the existing
BoQ screens; material prices use `price`/`currency`.

Customers submit **Kepuasan Pelanggan** once per account after project completion,
and may submit **Komplain** on any assigned project. Feedback is visible only to
its author and authorized internal handlers. Admins can handle feedback and
publish documents; internal handlers need `manage-customer-projects` (created by
`RolePermissionSeeder`, then assigned through role/permission management).
Only admins can provision accounts or change project access.

Account mutations, access changes, publications, decisions, price unlock/lock
events, password changes, feedback and responses write explicit database audit
records. Transactional mutations fail if their audit cannot be saved. Passwords
and financial amounts are excluded from these event payloads. Published records
use restrictive foreign keys to preserve decision history; deactivate accounts
and revoke access rather than deleting referenced users/projects/documents.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
