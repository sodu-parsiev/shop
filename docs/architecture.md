# Architecture

This document describes the technical foundation laid for the "Свой Ход" B2B
catalog. It covers structure and configuration only — no business features
(catalog, variants, requests, Bitrix24/1C integration) have been implemented
yet. See `project-spec.md` for the product requirements this foundation is
built to eventually support.

## Stack

| Concern       | Choice                                   |
|---------------|-------------------------------------------|
| Framework     | Laravel 13.25 (see note below)             |
| Admin UI      | Filament v5.7                              |
| Frontend      | Blade, with Livewire + Alpine for interactive parts |
| Database      | MySQL 8                                    |
| Queue         | `database` driver                          |
| Mail          | `log` driver locally, SMTP-ready via config |
| Testing       | Pest 4                                     |
| Authorization | `spatie/laravel-permission` + Laravel Policies |
| Local runtime | Docker (PHP-FPM + Nginx + MySQL) — see `docker/README.md` |

**Laravel version note:** `project-spec.md` specifies Laravel 12. The repo was
scaffolded with Laravel 13.25 (the current release), which is a superset of
what's needed and is what's actually installed (`composer.json` requires
`laravel/framework: ^13.8`). This document reflects the real installed
version rather than the spec, since downgrading would be a regression.

## Layering

The codebase is layered so business logic never lives inside HTTP controllers
or Filament resources — both are thin adapters over the same domain layer.

```
app/
  Domain/                 # Business logic: services, actions, DTOs, ports for
                           # external systems (Bitrix24, email, future 1C).
                           # Framework-agnostic where practical. Organized by
                           # module as features land, e.g. Domain/Catalog,
                           # Domain/Requests.
  Models/                 # Eloquent models, grouped into subdirectories per
                           # related domain: Models/Catalog/* (Product,
                           # Category, Color, Size, Density,
                           # CustomizationService), Models/Content/* (Page,
                           # Faq), plus flat Models/User.php and
                           # Models/Application.php.
  Enums/                  # Backed enums, e.g. ApplicationStatus.
  Policies/                # One Policy per model, gating Filament resource
                           # access via spatie permissions — see
                           # "Authorization" below.
  Http/Controllers/       # Public Blade/Livewire controllers. Each controller
                           # has a matching *ControllerService (e.g.
                           # BlogController + BlogControllerService) that
                           # holds the actual logic — the controller only
                           # translates HTTP <-> service calls.
  Filament/Resources/     # Admin UI only: form/table schemas, actions wired
                           # to Domain services. No business rules here —
                           # a resource should be replaceable without touching
                           # Domain code. Currently bare CRUD shells (a `name`
                           # field only) for every model above except User —
                           # real fields land with each business feature.
  Providers/               # Service bindings: AppServiceProvider registers
                           # all Policies and the Administrator Gate bypass
                           # (see "Authorization"), plus future port/adapter
                           # bindings (e.g. binding a BitrixPort interface to
                           # a concrete adapter based on config).
```

This is a ports-and-adapters (hexagonal) approach for external integrations:
the Domain layer defines interfaces ("ports") for things like "send this
request to Bitrix24" or "send this notification email"; concrete adapters
implement them and are swapped via config/service-provider bindings. This is
what will let 1C support be added later (per project-spec) without touching
Domain or Filament code — only a new adapter is needed.

Nothing under `app/Domain` exists yet — it's created as the first real
module is built, following this convention.

## Configuration

### Environment

`.env` / `.env.example` are set up for the Docker stack in `docker/`:

- `DB_CONNECTION=mysql`, host `mysql` (the Docker service name), database/user
  `shop`.
- `APP_NAME="Свой Ход"`, used as the Filament panel's brand name
  (`AdminPanelProvider::brandName()`).

### Database

Default Laravel `mysql` connection config (`config/database.php`), driven by
`.env`. No custom connections yet.

### Queues

`QUEUE_CONNECTION=database` (`config/queue.php`). The `jobs`, `job_batches`
and `failed_jobs` tables are already migrated. This is deliberately the
simplest driver for now — no Redis/Horizon in the dev stack (see
`docker/README.md` for that decision). Workers run via
`php artisan queue:work` inside the `app` container; this is what will carry
the Bitrix24/email dispatch jobs once requests are implemented.

### Mail

`config/mail.php` is untouched from Laravel defaults. `MAIL_MAILER=log`
locally, so outgoing mail is written to the log instead of sent. Production
will set `MAIL_MAILER` to a real transport (SMTP or a provider driver) via
env — no code changes needed. Request-confirmation mailables are not
implemented yet.

### Logging

`config/logging.php` is untouched from Laravel defaults: `LOG_CHANNEL=stack`
→ `single` file locally, level from `LOG_LEVEL` (`debug` locally). For
production, switch `LOG_CHANNEL` to `daily` via env for log rotation; no code
change required.

## Admin panel (Filament)

A single panel is registered at `/admin` (`app/Providers/Filament/AdminPanelProvider.php`),
auto-discovering resources/pages/widgets under `app/Filament/*`, branded from
`APP_NAME`. Local dev gets a seeded Administrator account (see
`AdminUserSeeder`, env `ADMIN_EMAIL`/`ADMIN_PASSWORD`, defaults
`admin@example.com` / `password`); production admins are created with
`php artisan make:filament-user`.

Resources, grouped by navigation section:

- **Content**: Products, Categories, Colors, Sizes, Densities, Customization
  Services, Pages, FAQs — all bare shells (`app/Filament/Resources/Catalog/*`,
  `Content/*`), single-page "simple" resources with just a `name` field. Real
  fields land per business feature.
- **Sales**: Applications (`app/Filament/Resources/Applications`) — the only
  resource with real fields, since Sales Manager's permissions needed
  something concrete to gate: customer contact details (read-only, submitted
  externally), `status` (`App\Enums\ApplicationStatus`), `internal_notes`,
  `assigned_to` (a user), and a permission-gated CSV/XLSX export action
  (`ApplicationExporter`).
- **Administration**: Users, Roles (`app/Filament/Resources/Users`,
  `Roles`) — manage admin accounts and role/permission assignment.

`Media` and `SEO` (from the Content Manager's permission list) are not
standalone resources — they're reserved permission names (`manage_media`,
`manage_seo`) for capabilities that will live inside other resources (file
uploads, per-record SEO fields) once those are built.

## Authorization

Roles and permissions are stored via `spatie/laravel-permission`
(`config/permission.php`, guard `web`). `App\Models\User` uses `HasRoles` and
implements Filament's `FilamentUser` (`canAccessPanel()` — true for any of
the three roles) and `HasName` contracts.

**Enforcement is via real Laravel Policies**, one per model
(`app/Policies/*Policy.php`), each implementing `viewAny`/`view`/`create`/
`update`/`delete`/`deleteAny` as a one-line check against a spatie
permission, e.g.:

```php
public function viewAny(User $user): bool
{
    return $user->can('view_any_product');
}
```

Policies are registered explicitly in `AppServiceProvider::boot()` via
`Gate::policy()` (not left to Laravel's naming-convention auto-discovery,
since `RolePolicy` guards `Spatie\Permission\Models\Role`, which isn't under
`App\Models` and wouldn't auto-resolve). Filament automatically calls these
policy methods to gate navigation visibility and page/action access — no
per-resource authorization code needed.

Permission naming convention: `{ability}_{model_slug}` for the six standard
abilities (e.g. `update_application`), plus three standalone permissions:
`export_application`, `manage_media`, `manage_seo`.

**Administrator** gets full access via a `Gate::before` bypass
(`AppServiceProvider`), not explicit permissions — the role is seeded with
zero permission rows, so toggling permissions for it in the Roles admin page
has no effect (documented inline in the seeder).

**Content Manager** and **Sales Manager** get an explicit permission set,
defined in `database/seeders/RolesAndPermissionsSeeder`:

| Role            | Permissions |
|------------------|-------------|
| Content Manager | Full CRUD (`view_any`/`view`/`create`/`update`/`delete`/`delete_any`) on Product, Category, Color, Size, Density, CustomizationService, Page, Faq, plus `manage_media`, `manage_seo` |
| Sales Manager   | `view_any_application`, `view_application`, `update_application` (covers status/notes/assignment), `export_application` — nothing else |

Run `php artisan db:seed --class=RolesAndPermissionsSeeder` (or the full
`db:seed`, which calls it) after a fresh migration to create the roles.

**Known gotcha, already handled:** creating permissions and immediately
`syncPermissions()`-ing a role in the same process throws
`PermissionDoesNotExist` unless the registrar's cache is flushed in between
(`app(PermissionRegistrar::class)->forgetCachedPermissions()`) — see the
comment in the seeder.

## Testing

Pest 4 (`pestphp/pest`, `pestphp/pest-plugin-laravel`), configured in
`tests/Pest.php`:

- `Feature` tests extend `Tests\TestCase` with `RefreshDatabase`.
- `Unit` tests extend `Tests\TestCase` with no database.
- `phpunit.xml` points the test environment at an in-memory SQLite database,
  `sync` queue, and `array` mail/session/cache drivers — isolated from the
  dev MySQL database, no external calls made during tests.

Current tests:

- `tests/Unit/ExampleTest.php`, `tests/Feature/ExampleTest.php`,
  `tests/Feature/FilamentAdminPanelTest.php` — placeholders proving the stack
  boots (`/` and `/admin/login` return 200).
- `tests/Feature/Authorization/RoleAccessTest.php` — the full per-role access
  matrix: Administrator can reach every resource; Content Manager can reach
  every content resource but is forbidden from Applications and
  Users/Roles; Sales Manager can reach Applications but is forbidden from
  every content resource and Users/Roles; a user with no role at all is
  forbidden from `/admin` entirely. 34 cases via Pest datasets.

Run tests via `docker compose -f docker/docker-compose.yml exec app php artisan test`
(see `docker/README.md` for the full command set).

## What's intentionally not here yet

Real fields/business logic on the catalog models (variants, MOQ, stock),
public Blade/Livewire controllers, jobs, mailables, and Bitrix24/1C
integration code. Those land as business features are implemented on top of
this foundation, following the layering above — the current catalog models
and Filament resources are deliberately bare shells that exist only so the
role/permission system has real resources to gate.
