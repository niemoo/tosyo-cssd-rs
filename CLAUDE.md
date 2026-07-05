# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

CSSD Management System — a Laravel 12 app for tracking a hospital's Central Sterile Supply
Department: instruments, tray assembly, sterilization batches, consumable stock/usage, and
distribution requests from hospital units. Multi-hospital (a user can belong to several
hospitals). Server-rendered with Blade + Tailwind v4 + Vite; no SPA framework/API layer.
UI copy and flash messages are written in Indonesian.

## Commands

```bash
composer setup          # first-time setup: install, .env, key:generate, migrate, npm install/build
composer dev             # run server + queue:listen + pail (logs) + vite concurrently
php artisan serve        # app only, http://localhost:8000
npm run dev              # vite dev server only
npm run build            # production asset build

composer test            # config:clear + php artisan test
php artisan test --filter=testName          # single test by name
php artisan test tests/Feature/FooTest.php   # single file
vendor/bin/pint          # format PHP (Laravel Pint)
php artisan migrate:fresh --seed             # rebuild DB with seeders (DatabaseSeeder wires order)
```

Tests run against in-memory SQLite (see `phpunit.xml`), independent of the app's normal MySQL
connection configured in `.env`.

## Architecture

**No API/service layer beyond one exception.** Controllers talk to Eloquent models directly;
almost all business logic lives in controllers. The one extracted service is
`app/Services/ConsumableUsageService.php`, which is the single place that debits consumable
stock, writes a `ConsumableMovement` (OUT), and records a `ConsumableUsage` — any new code that
consumes stock (e.g. from a sterilization batch or tray assembly) should call this rather than
mutating `ConsumableStock` directly.

**Multi-hospital scoping is manual, not global.** There is no global scope or middleware that
filters queries by hospital. Every controller's `index()` repeats the same pattern: load the
user's active hospitals (`auth()->user()->hospitals()`), and if the user belongs to only one
hospital, filter by `session('active_hospital_id')`; if they belong to several, filter by
`hospital_id` query param or fall back to `whereIn` over all their hospital IDs. The active
hospital is set via the `switch-hospital/{hospital}` route (routes/web.php) and only changes
via that route. When adding a new resource, follow this same repeated pattern rather than
introducing a new scoping mechanism — it's a deliberate (if repetitive) convention here, not a
gap.

**Authorization is route-level via spatie/laravel-permission**, not policies. Every resource
group in `routes/web.php` is wrapped in `Route::middleware(['permission:<resource>.view'])`,
with finer actions (`.approve`, `.fulfill`, `.return`) gated by additional `permission:` middleware
on individual routes. Permission strings follow `{resource-plural-kebab}.{action}` (e.g.
`instrument-categories.edit`, `distribution-requests.approve`) — see
`database/seeders/PermissionSeeder.php` for the full catalog and `RoleSeeder.php` for how
permissions map to roles.

**Soft deletes + audit columns are the norm for domain models.** Most models use
`SoftDeletes` and the `App\Traits\HasAuditColumns` trait, which auto-populates
`created_by`/`updated_by` (and `deleted_by` on `deleting`) from the authenticated user via model
events. Migrations add these columns with the `Blueprint::auditColumns()` /
`auditColumnsWithDelete()` macros registered in `app/Providers/AppServiceProvider.php` — use
those macros in new migrations rather than hand-rolling the columns. Controllers follow a
consistent `index/create/store/show/edit/update/destroy/restore/toggleActive` shape, with
`destroy` soft-deleting and a paired `restore(int $id)` route (`{resource}/{id}/restore`) using
`onlyTrashed()->findOrFail()`. `index()` supports `show_deleted`, `search`, `status`, and
column-allowlisted `sort`/`direction` query params.

**Core domain lifecycle** (each stage is a distinct model with its own status enum defined as
class constants + a `STATUSES` label/color map, e.g. `Tray::STATUSES`,
`SterilizationBatch::STATUSES`):

1. `Instrument`/`InstrumentItem` — catalog of physical instruments per hospital.
2. `TrayTemplate`/`TrayTemplateItem` — defines what instruments a tray type should contain.
3. `Tray`/`TrayItem` — an assembled physical tray, moves through
   `ASSEMBLING → READY → IN_STERILIZATION → STERILE → IN_USE → RETURNED` (or
   `NEEDS_REPROCESSING`).
4. `SterilizationBatch`/`SterilizationBatchItem` — groups trays through a sterilizer run
   (`PENDING → IN_PROGRESS → COMPLETED/FAILED`); pass/fail result is per tray via the pivot.
5. `DistributionRequest`/`DistributionRequestItem` — a unit's request for trays, going
   `DRAFT → PENDING_APPROVAL → APPROVED/REJECTED → IN_PROCESS → FULFILLED`. Editing is only
   allowed in `DRAFT`/`REJECTED` (see `canBeApproved()`/`canBeFulfilled()` guards on the model).
   Fulfillment (`DistributionRequestController::processFulfillment`) assigns specific `STERILE`
   trays 1:1 to request items inside a `DB::transaction`, flips trays to `IN_USE`, and only
   marks the request `FULFILLED` once every item has a tray.
6. `TrayReturn` — a unit returning a tray, and `ConsumableUsage`/`ConsumableMovement`/
   `ConsumableStock` — consumable tracking, tied to whatever consumed them via a polymorphic
   `usageable` relation (`Tray`, `SterilizationBatch`, etc.).

When touching this lifecycle, check the relevant model's status constants and `canBeX()` helper
methods before writing new transition logic — the guards are the source of truth for valid
transitions, not the controller.

**Views**: Blade only, organized by resource under `resources/views/{resource}/` with
`index/create/edit/show` per resource, plus shared components in
`resources/views/components/` (`sort-header`, `pagination`, `stat-card`, `badge`,
`modal-confirm`, etc.). Reuse these instead of building new list/table chrome.

**Locale**: app locale and Carbon are forced to Indonesian (`id`) in
`AppServiceProvider::boot()` regardless of `.env`'s `APP_LOCALE`; keep new user-facing
strings/flash messages in Indonesian to match the rest of the UI.
