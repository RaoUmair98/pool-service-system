# Pool Service SaaS — Implementation Document (v1)

**Source:** Pool Service SaaS Product Spec (v1) — Nov 7, 2026  
**App:** `/var/www/html/pool-service-system` (Laravel 11)  
**Purpose:** Single source of truth for building the remaining product on top of what already exists.  
**Status:** v1 core complete (Nov 14, 2026). Modules 1–8 + Dashboard metrics shipped.  
**Progress:** Module 1 ✓ · Module 2 ✓ · Module 3 ✓ · Module 4 ✓ · Module 5 ✓ · Module 6 ✓ · Module 7 ✓ · Module 8 ✓ · Dashboard ✓

---

## 1. Product summary

A multi-tenant web app for small pool cleaning companies (1–10 technicians) in the US, UK, Canada, and Australia. It replaces paper and spreadsheets for customers, weekly routes, technician visits, and monthly billing.

### Roles

| Role | Who | Access in v1 |
|------|-----|--------------|
| **Admin** | Company owner / office staff | Full company: customers, pools, technicians, schedules, invoices, dashboard |
| **Technician** | Field worker | Mobile-responsive: today's route, complete visits, readings, photos |
| **Customer** | Pool owner | **No login.** Receives service reports and invoices by email only |

### v1 principles

1. **One company = one tenant.** Data never crosses `company_id` boundaries.
2. **Technician UI is mobile-responsive web**, not a native app.
3. **Payments are marked manually.** No payment gateway in v1.

### Nine core features

1. Company signup and login (owner registers, invites staff, RBAC)
2. Customer management (contact, address, access notes)
3. Pool profiles (type, gallons, equipment, photo, monthly rate)
4. Technician management (login per tech)
5. Recurring scheduling (weekly day + assigned tech → generated visits)
6. Technician visit flow (route → start → checklist/readings/photos → complete)
7. Service reports (email on completion)
8. Basic invoicing (monthly fixed rate, PDF, Paid/Unpaid)
9. Admin dashboard (today's visits, customers, unpaid invoices)

### Out of scope (v2+)

- Stripe / online payments / autopay  
- Route optimization / live GPS  
- Chemical dosing calculator / inventory  
- Quotes / repair work orders  
- Customer portal  
- QuickBooks  
- Native mobile app with offline sync  
- SaaS billing of the platform itself (Laravel Cashier later)

---

## 2. Current codebase baseline

What is **already implemented** and must be extended, not replaced.

### Stack in use (keep this)

| Layer | Spec suggestion | Actual choice in this repo |
|-------|-----------------|----------------------------|
| Backend | Laravel 11+, MySQL | Laravel 11, PHP 8.2; MySQL (`pool_service`); tests use `pool_service_testing` |
| Multi-tenancy | `stancl/tenancy` | **Single DB + `company_id`** (already working — keep this) |
| Admin UI | Livewire or Inertia + Vue | **Blade + corporate admin theme** (keep this) |
| Technician UI | Same app, mobile-first | Same Blade app; add mobile-first visit views |
| Auth / RBAC | — | Laravel Breeze + `spatie/laravel-permission` |
| Queue | — | `database` driver (ready) |
| Mail | Laravel Mail + queue | Configured (`log` locally) |
| Files | S3-compatible | Start with `public` / `local`; add S3 when needed |
| PDF | barryvdh/laravel-dompdf | ✓ Installed |
| Scheduler | Laravel commands | ✓ `visits:generate` daily; `invoices:generate` monthly |

**Do not introduce** Livewire, Inertia, Vue SPA, or `stancl/tenancy` for v1 unless explicitly decided later. Stay consistent with Blade + existing patterns.

### Already built

#### Module 1 — Auth & Tenancy ✓

| Piece | Location / notes |
|-------|------------------|
| Company signup | `RegisteredUserController` — creates `Company` + owner `User` with `admin` role |
| Login / logout / password reset | Breeze auth routes |
| Roles | `admin`, `technician` via Spatie |
| Permissions | Full v1 set seeded in `RolesAndPermissionsSeeder` (customers/pools/schedules/visits/invoices/reports/dashboard included for upcoming modules) |
| Team CRUD | `TeamController` — add/edit/toggle/delete admins & technicians in same company |
| Company settings | name, email, phone, address, **logo**, **currency**, **timezone** |
| Tenant isolation | `BelongsToCompany` + `Controller::ensureSameCompany` (404 cross-tenant) |
| Active users | `HasActiveStatus` + `EnsureUserIsActive` middleware |
| Activity log | `LogsActivity` trait + `ActivityLog` |
| Admin layout | sidebar with Operations → Customers |
| Demo seed | `admin@example.com` / `tech@example.com` + demo customer |

#### Module 2 — Customers ✓

| Piece | Location / notes |
|-------|------------------|
| Table / model | `customers` + `Customer` (`BelongsToCompany`, `HasActiveStatus`, `LogsActivity`) |
| CRUD + toggle | `CustomerController` — list, detail, add/edit, turn on/off, delete |
| Screens | `resources/views/customers/{index,form,show}.blade.php` |
| Permission | `manage customers` (admin only) |
| Tests | `tests/Feature/CustomerTest.php` |
| Demo data | Jordan Rivera seeded for Demo Pool Service |

#### Module 3 — Pools ✓

| Piece | Location / notes |
|-------|------------------|
| Tables / models | `pools`, `pool_equipment` + `Pool`, `PoolEquipment` |
| CRUD + toggle | `PoolController` — create under customer, show/edit/toggle/delete |
| Equipment | Repeatable name/notes rows synced on save |
| Photo | Public disk under `companies/{id}/pools/{id}/` |
| Screens | `resources/views/pools/{form,show}.blade.php`; listed on customer detail |
| Permission | `manage pools` (admin only) |
| Tests | `tests/Feature/PoolTest.php` |
| Demo data | Jordan Rivera → Main (chlorine $149) + Spa (salt $59) with equipment |
| Safety | Customer delete blocked while pools exist |

#### Module 4 — Technicians ✓ (via Team)

Covered by Team page; no separate `technicians` table.

#### Module 5 — Scheduling ✓

| Piece | Location / notes |
|-------|------------------|
| Tables / models | `service_schedules`, `visits` + `ServiceSchedule`, `Visit` |
| Schedule UI | Pool → Schedule: weekday (Sun–Sat) + technician; saves + generates visits |
| Visit board | `/visits` filter by date/tech/status; detail with reassign / reschedule / skip |
| Generator | `VisitGenerator` + `php artisan visits:generate` (idempotent, 14-day horizon, company timezone) |
| Scheduler | `routes/console.php` — daily at 01:00 |
| Permission | `manage schedules` |
| Tests | `tests/Feature/SchedulingTest.php` |
| Demo data | Main → Monday, Spa → Wednesday; visits seeded |
| Safety | Pool delete blocked while visits exist; skipped visits not overwritten |

#### Module 6 — Service Visits ✓

| Piece | Location / notes |
|-------|------------------|
| Visit fields | `checklist` JSON, `reading_ph`, `reading_free_chlorine`, `reading_alkalinity` |
| Photos | `visit_photos` + `VisitPhoto` (before/after/other) |
| Domain | `VisitService` — start / saveProgress / complete / addPhoto |
| Tech UI | `layouts.tech` + `/route` today's list + stop form (mobile-first) |
| Home redirect | Technicians → Today's Route; admins → Dashboard |
| Permission | `complete visits` (technicians); own visits only |
| Admin | Completed checklist/readings/photos shown on visit detail |
| Tests | `tests/Feature/RouteTest.php` |
| Report email | ✓ Queued on complete via `SendServiceReportJob` |

#### Module 7 — Reports & Notifications ✓

| Piece | Location / notes |
|-------|------------------|
| Table / model | `email_logs` + `EmailLog` |
| Mail | `ServiceReportMail` + `emails/service-report` |
| Job / service | `SendServiceReportJob` + `ServiceReportService` |
| On complete | `VisitService` queues report after commit |
| Admin | Preview `/visits/{visit}/report`, Resend, Email Log list |
| Permission | `view reports` |
| Tests | `tests/Feature/ServiceReportTest.php` |
| No email | Logged as `skipped` with reason |

### Remaining gaps / deferred

| Spec item | Status | Notes |
|-----------|--------|--------|
| Invite by email (tech sets password) | Deferred | Team form sets password (accepted for v1 speed). Invite tokens optional later. |
| Separate `technicians` table | Won't do in v1 | Use `users` with role `technician`. |
| Invite polish / SaaS billing / v2 features | Out of scope | See Out of Scope section. |

---

## 3. Architecture decisions

### 3.1 Tenancy

- Every business row carries `company_id`.
- Controllers/scopes always constrain to the signed-in user's company.
- Use `BelongsToCompany` on all tenant models (`Customer` already does).
- Abort `404` (not `403`) via `Controller::ensureSameCompany` when a record belongs to another company.

### 3.2 Technicians = users

- Spec lists a `technicians` table. In this app, technicians are already `users` with role `technician`.
- **Implementation choice:** no separate `technicians` table for v1.
- Schedules and visits reference `technician_user_id` → `users.id` (must be a technician in the same company).
- Team page remains the UI for “Technician management” (Module 4).

### 3.3 UI surfaces

| Audience | Layout | Notes |
|----------|--------|-------|
| Admin | `layouts.admin` | Desktop-first corporate theme |
| Technician | Same layout **or** a slim `layouts.tech` | Mobile-first; Today's Route as home for technicians |
| Guest / auth | `layouts.guest` | Already exists |

**Routing tip:** After login, redirect technicians to Today's Route; admins to Dashboard.

### 3.4 Permissions (extend Spatie)

Add permissions as modules land; seed them in `RolesAndPermissionsSeeder`.

| Permission | Admin | Technician | Seeded |
|------------|-------|------------|--------|
| `manage company` | ✓ | | ✓ |
| `manage team` | ✓ | | ✓ |
| `view activity log` | ✓ | | ✓ |
| `manage customers` | ✓ | | ✓ (wired) |
| `manage pools` | ✓ | | ✓ (wired) |
| `manage schedules` | ✓ | | ✓ (wired) |
| `view visits` | ✓ | ✓ (own assigned only) | ✓ |
| `complete visits` | | ✓ | ✓ (wired — Today's Route) |
| `manage invoices` | ✓ | | ✓ (wired) |
| `view reports` | ✓ | | ✓ (wired) |
| `view dashboard` | ✓ | optional limited | ✓ |

Technicians must never CRUD customers/pools/invoices.

### 3.5 Soft conventions from existing code

Reuse these everywhere:

- `HasActiveStatus` for active/inactive records  
- `LogsActivity` on customer/pool/schedule/invoice models  
- List filters via `partials/master-filters`  
- `partials/row-actions`, `partials/form-actions`, `partials/pagination`  
- `x-ui.field` form component  
- Select2 + SweetAlert2 already in admin layout  
- `save` / `save_and_new` pattern from Team forms  

---

## 4. Domain model & database

### 4.1 Entity relationship (conceptual)

```
Company 1──* User (admin | technician)
Company 1──* Customer 1──* Pool 1──* PoolEquipment
Pool 1──1 ServiceSchedule (weekly day + technician_user_id)
ServiceSchedule ──generates──► Visit
Visit 1──* VisitChecklistItem
Visit 1──* VisitReading   (or one row with pH/chlorine/alkalinity columns)
Visit 1──* VisitPhoto
Visit ──triggers──► EmailLog (service report)
Company 1──* Invoice 1──* InvoiceItem
Invoice ──optional──► Payment (or status flip only in v1)
Invoice ──triggers──► EmailLog
```

### 4.2 Table schemas

All tenant tables include `company_id` (FK → `companies`, cascade delete) unless noted.

#### `companies` ✓

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name, email, phone, address | | |
| logo_path | string nullable | public disk |
| currency | string(3) | default `USD` (`USD`/`GBP`/`CAD`/`AUD`) |
| timezone | string | default `UTC` (curated IANA list) |
| timestamps | | |

#### `customers` ✓

| Column | Type | Notes |
|--------|------|-------|
| company_id | FK | |
| name | string | |
| phone | string nullable | |
| email | string nullable | used for reports/invoices email |
| service_address | text | |
| access_notes | text nullable | gate code, dog on site, etc. |
| is_active | boolean | default true |
| timestamps | | |

Indexes: `(company_id, name)`, `(company_id, is_active)`.

#### `pools` ✓

| Column | Type | Notes |
|--------|------|-------|
| company_id | FK | denormalized for easy scoping |
| customer_id | FK | |
| name | string nullable | e.g. "Main", "Spa" |
| type | string | `chlorine` \| `salt` |
| gallons | unsigned integer nullable | |
| photo_path | string nullable | |
| monthly_rate | decimal(10,2) | used for invoicing |
| is_active | boolean | default true |
| timestamps | | |

#### `pool_equipment` ✓

| Column | Type | Notes |
|--------|------|-------|
| pool_id | FK | |
| name | string | e.g. "Pump", "Filter", "Heater" |
| notes | string nullable | |
| timestamps | | |

Simple list; no need for a global equipment catalog in v1.

#### `service_schedules` ✓

| Column | Type | Notes |
|--------|------|-------|
| company_id | FK | |
| pool_id | FK unique | one schedule per pool in v1 |
| technician_user_id | FK → users | must be technician, same company |
| weekday | tinyint | `0`=Sunday … `6`=Saturday (Carbon) |
| is_active | boolean | inactive schedule stops visit generation |
| timestamps | | |

#### `visits` ✓

| Column | Type | Notes |
|--------|------|-------|
| company_id | FK | |
| pool_id | FK | |
| customer_id | FK | denormalized for lists |
| technician_user_id | FK | assigned tech (admin may reassign) |
| service_schedule_id | FK nullable | nullOnDelete |
| scheduled_date | date | calendar day of service |
| status | string | `scheduled` \| `in_progress` \| `completed` \| `skipped` |
| started_at | datetime nullable | |
| completed_at | datetime nullable | |
| skip_reason | text nullable | required when skipped |
| notes | text nullable | tech notes / chemicals used |
| checklist | json nullable | Option A — keys in `Visit::CHECKLIST_ITEMS` |
| reading_ph | decimal(4,2) nullable | |
| reading_free_chlorine | decimal(5,2) nullable | |
| reading_alkalinity | decimal(6,1) nullable | |
| timestamps | | |

Unique constraint: `(pool_id, scheduled_date)` so the nightly job is idempotent.

#### `visit_photos` ✓

| Column | Type | Notes |
|--------|------|-------|
| visit_id | FK | |
| company_id | FK | |
| path | string | disk path |
| kind | string | `before` \| `after` \| `other` |
| caption | string nullable | |
| timestamps | | |

#### `email_logs` ✓

| Column | Type | Notes |
|--------|------|-------|
| company_id | FK | |
| customer_id | FK nullable | |
| related_type | morph | Visit / Invoice |
| related_id | | |
| to_email | string | |
| subject | string | |
| type | string | `service_report` \| `invoice` |
| status | string | `queued` \| `sent` \| `failed` \| `skipped` |
| error | text nullable | |
| sent_at | datetime nullable | |
| timestamps | | |

#### `invoices` ✓

| Column | Type | Notes |
|--------|------|-------|
| company_id | FK | |
| customer_id | FK | |
| invoice_number | string | unique per company; format `INV-{companyId}-{YYYYMM}-{seq}` |
| period_year | smallint | |
| period_month | tinyint | |
| status | string | `draft` \| `sent` \| `paid` |
| subtotal / total | decimal(10,2) | no tax in v1 |
| currency | string(3) | copied from company |
| issued_at / due_at | date nullable | due = issued + 14 days |
| sent_at / paid_at | datetime nullable | |
| payment_method / payment_note | nullable | cash / bank_transfer / cheque / other |
| notes | text nullable | |
| timestamps | | |

Unique: `(company_id, customer_id, period_year, period_month)`.

#### `invoice_items` ✓

| Column | Type | Notes |
|--------|------|-------|
| invoice_id | FK | |
| pool_id | FK nullable | |
| description | string | e.g. "Monthly service — Main pool" |
| amount | decimal(10,2) | pool monthly_rate snapshot |
| timestamps | | |

**Payments table:** skipped for v1 — `paid_at` + `payment_method` / `payment_note` on invoices.

### 4.3 Status machines

**Visit:** `scheduled` → `in_progress` → `completed`  
Also: `scheduled` → `skipped` (with reason).  
Do not reopen completed visits in v1 (admin can note issues separately if needed).

**Invoice:** `draft` → `sent` → `paid`.  
No cancel/void in v1 (soft-delete or leave unpaid).

---

## 5. Modules — detailed implementation plan

Build order from the spec: **1 → 2 → 3 → 4 → 5 → 6 → 7 → 8 → Dashboard**.

Modules 1–4 can be parallelized; 5 depends on 2–4; 6 depends on 5; 7 depends on 6; 8 depends on 3; Dashboard depends on 5/6/8.

---

### Module 1 — Auth & Tenancy ✓

**Completed (P0):**

1. ✓ `logo_path`, `currency`, `timezone` on `companies`
2. ✓ Company settings form: logo upload/remove, currency, timezone
3. ✓ Invite-by-email deferred — Team admin-set password remains the v1 path
4. ✓ `BelongsToCompany` + shared `ensureSameCompany`
5. Post-login redirect by role — still pending until Today's Route (Module 6) exists

**Screens:** Signup, Login, Company settings (enhanced). Invite user deferred.

---

### Module 2 — Customers ✓

**Completed:**

1. ✓ Migration + `Customer` model (`HasActiveStatus`, `LogsActivity`, `BelongsToCompany`)
2. ✓ `CustomerController` resource + toggle
3. ✓ Customer delete blocked when pools exist (prefer Turn off)
4. ✓ Permission `manage customers`
5. ✓ Sidebar → Operations → Customers
6. ✓ Filters: search + status
7. ✓ Detail page lists pools (Module 3)

**Screens:** Customer list, Customer detail, Add/Edit customer.

**Tests:** `CustomerTest` — CRUD, technician 403, cross-company 404, company currency/timezone update.

---

### Module 3 — Pools ✓

**Completed:**

1. ✓ Migrations: `pools`, `pool_equipment`
2. ✓ Routes: `/customers/{customer}/pools/create|store`, `/pools/{pool}` show/edit/update/toggle/destroy
3. ✓ Photo upload to `public` disk
4. ✓ Equipment as repeatable form rows (name + notes)
5. ✓ Monthly rate required (≥ 0)
6. ✓ Schedule shown on pool detail (Module 5)

**Screens:** Pool detail, Add/Edit pool (from customer).

**Tests:** `PoolTest` — create with equipment, update/photo/toggle/delete, technician 403, cross-company 404, customer delete guard.

---

### Module 4 — Technicians ✓

**Completed via Team page.** No separate table.

1. ✓ Team create defaults to technician role
2. ✓ Sidebar Team + role filter
3. ✓ Schedule assignee dropdowns use active technicians in company
4. ✓ No new tables

**Screens:** Technician list / Add/Edit = Team screens.

---

### Module 5 — Scheduling ✓

**Completed:**

1. ✓ Migrations: `service_schedules`, `visits`
2. ✓ Pool schedule UI: weekday + technician
3. ✓ Visits board with date/tech/status filters; reassign / reschedule / skip
4. ✓ `visits:generate` + nightly schedule at 01:00; 14-day horizon; company timezone; idempotent `firstOrCreate`
5. ✓ Skipped/completed visits not overwritten

**Screens:** Schedule form on pool, Visits list, Visit detail.

**Tests:** `SchedulingTest` — schedule+generate, idempotent, reassign/reschedule/skip, skip preserved, permissions, cross-tenant, pool delete guard.

---

### Module 6 — Service Visits ✓

**Completed:**

1. ✓ Checklist JSON + readings columns; `visit_photos`
2. ✓ `/route` today's list, stop detail, start, save, complete, photos
3. ✓ `VisitService` status transitions
4. ✓ Report job dispatched on complete (Module 7)
5. ✓ Admin visit detail shows completed data; techs only complete own stops
6. ✓ `layouts.tech` + mobile CSS (large taps, sticky Complete, `capture="environment"`)

**Screens:** Today's route (mobile), Visit form (mobile), Visit detail (admin).

**Tests:** `RouteTest` — home redirect, own visits only, start/save/complete/photo, permissions, closed visit guard.

---

### Module 7 — Reports & Notifications ✓

**Completed:**

1. ✓ `email_logs` + `EmailLog`
2. ✓ `ServiceReportMail` (markdown) with checklist, readings, photo links
3. ✓ `SendServiceReportJob` + `ServiceReportService`
4. ✓ Preview + Resend on visit / report page
5. ✓ No customer email → `skipped` log
6. ✓ Invoice emails reuse `email_logs` (Module 8)

**Screens:** Report preview, Email log.

**Tests:** `ServiceReportTest` — queue on complete, send/skip, preview/resend, email log, permissions.

---

### Module 8 — Billing ✓

**Completed:**

1. ✓ `barryvdh/laravel-dompdf`
2. ✓ `invoices` + `invoice_items` (+ payment_method/note)
3. ✓ `invoices:generate` + monthly schedule (1st at 02:00); current-month drafts; idempotent
4. ✓ Invoice list filters + detail
5. ✓ PDF download, Send email (→ `sent`), Mark paid
6. ✓ `InvoiceMail` with PDF attachment + `email_logs`
7. ✓ No payment gateway

**Screens:** Invoice list, Invoice detail, Generate modal.

**Tests:** `InvoiceTest` — generate idempotent, send/PDF/mark paid, skip no email, permissions, dashboard unpaid.

---

### Dashboard ✓

**Admin cards shipped:**

1. ✓ Today's visits done / total (pending shown)
2. ✓ Active customers
3. ✓ Unpaid invoices (count + amount)
4. ✓ Technicians count (kept)

Recent activity kept. Technicians still land on Today's Route.

---

## 6. Workflows (end-to-end)

### 6.1 Company onboarding

1. Owner signs up (company name, email, password) → company + admin user.  
2. Admin opens Company Settings → logo, address, currency, timezone.  
3. Admin adds technicians via Team (or invite email).  

### 6.2 Customer & pool setup

1. Add customer (contact, service address, access notes).  
2. Add pool(s): type, gallons, equipment, photo, monthly rate.  
3. Set schedule: weekday + technician.  

### 6.3 Visit generation (system)

1. Nightly `visits:generate` creates visits for next 14 days.  
2. Skips existing `(pool_id, scheduled_date)`.  
3. Admin may reassign / reschedule / skip one visit without changing the schedule.  

### 6.4 Technician daily visit

1. Tech opens Today's Route on phone.  
2. Opens stop → address, access notes, pool, last readings.  
3. Start → `in_progress` + `started_at`.  
4. Checklist + readings + chemicals note + photos.  
5. Complete → `completed` + `completed_at` → queue report email.  

### 6.5 Service report

1. Job builds report from visit data.  
2. Email customer; write `email_logs`.  
3. Admin can preview/resend from visit page.  

### 6.6 Monthly billing

1. On 1st: `invoices:generate` creates draft invoices.  
2. Admin reviews → Send (PDF email) → `sent`.  
3. When paid offline → Mark Paid → `paid`.  
4. Unpaid (`sent` and not `paid`) show on dashboard.  

---

## 7. Routes map (proposed)

```text
# Admin — customers & pools
GET|POST          /customers
GET|PUT|DELETE    /customers/{customer}
GET|POST          /customers/{customer}/pools
GET|PUT|DELETE    /pools/{pool}

# Admin — scheduling & visits
GET               /schedules
PUT               /pools/{pool}/schedule
GET               /visits
PATCH             /visits/{visit}/reassign
PATCH             /visits/{visit}/reschedule
POST              /visits/{visit}/skip
GET               /visits/{visit}
GET               /visits/{visit}/report
POST              /visits/{visit}/report/resend

# Technician — route
GET               /route
GET               /route/visits/{visit}
POST              /route/visits/{visit}/start
PUT               /route/visits/{visit}
POST              /route/visits/{visit}/complete
POST              /route/visits/{visit}/photos

# Billing
GET               /invoices
POST              /invoices/generate
GET               /invoices/{invoice}
GET               /invoices/{invoice}/pdf
POST              /invoices/{invoice}/send
POST              /invoices/{invoice}/mark-paid

# Notifications
GET               /email-logs   (optional)
```

Existing routes stay: `/dashboard`, `/team`, `/company`, `/activity-log`, `/profile`, auth.

---

## 8. Jobs, commands & schedule

| Command / job | When | Purpose |
|---------------|------|---------|
| `visits:generate` | Daily ~01:00 company-agnostic UTC, or per-company using timezone | Create next 14 days of visits |
| `invoices:generate` | Monthly, 1st ~02:00 | Create draft invoices |
| `SendServiceReportJob` | On visit complete | Email report |
| `SendInvoiceMailJob` | On admin Send | Email PDF invoice |

Register in `routes/console.php`:

```php
Schedule::command('visits:generate')->dailyAt('01:00');
Schedule::command('invoices:generate')->monthlyOn(1, '02:00');
```

Ensure production runs `php artisan schedule:work` or cron + `queue:work`.

---

## 9. Packages to add

| Package | When | Why |
|---------|------|-----|
| `barryvdh/laravel-dompdf` | Module 8 | Invoice PDF |
| (optional) `intervention/image` | Module 3/6 | Resize pool/visit photos |
| AWS S3 flysystem | when deploying photos | Spec S3-compatible storage |

Do **not** add Cashier, stancl/tenancy, Livewire, or Inertia for v1.

---

## 10. Testing checklist

### Feature tests (PHPUnit, match existing Feature suite)

- Company registration creates company + admin  
- Tenant isolation: user A cannot view/update B's customers/pools/visits/invoices  
- Technician cannot manage customers/invoices  
- Visit generation is idempotent  
- Visit status transitions reject illegal moves  
- Complete visit dispatches report job  
- Invoice generation one-per-customer-per-period  
- Mark paid / send invoice permissions  

### Manual QA

- Phone-width today's route (Chrome device mode)  
- Photo upload from camera  
- Email preview with `MAIL_MAILER=log` / Mailpit  
- PDF download  
- Timezone: schedule weekday correct for company timezone  

---

## 11. Suggested implementation phases

| Phase | Scope | Status |
|-------|--------|--------|
| **P0** | Docs + company fields (logo/currency/timezone) + `BelongsToCompany` + permissions scaffold | ✓ Done |
| **P1a** | Customers | ✓ Done |
| **P1b** | Pools + equipment + photos | ✓ Done |
| **P2** | Schedules + visit generation command + admin visit board | ✓ Done |
| **P3** | Technician today's route + visit completion | ✓ Done |
| **P4** | Service report email + email_logs | ✓ Done |
| **P5** | Invoices + PDF + send + mark paid | ✓ Done |
| **P6** | Dashboard metrics + polish + seed demo data | ✓ Done |

After each phase: migrate, seed, smoke-test as admin + technician.

---

## 12. Demo seed expansion

**Done:** demo company, admin, tech, Jordan Rivera, Main/Spa pools, Mon/Wed schedules, visits, and current-month draft invoice.  

---

## 13. File/folder conventions

```text
app/
  Models/Invoice.php ✓  InvoiceItem.php ✓  EmailLog.php ✓
  Services/InvoiceGenerator.php ✓  InvoiceService.php ✓
  Mail/InvoiceMail.php ✓  Jobs/SendInvoiceMailJob.php ✓
  Console/Commands/GenerateInvoicesCommand.php ✓
  Http/Controllers/InvoiceController.php ✓
resources/views/
  invoices/ ✓  emails/invoice.blade.php ✓  emails/service-report.blade.php ✓
```

---

## 14. Open decisions

1. **Invite email vs admin-set password** — **Decided for v1:** admin-set password via Team. Invites optional later.  
2. **Checklist storage** — JSON on `visits` vs child table (recommend JSON).  
3. **Invoice period** — invoice on 1st for **current** month vs **previous** month (recommend current month drafts on the 1st).  
4. **Technician layout** — **Decided:** slim `layouts.tech` for Today's Route.  
5. **Hard delete vs deactivate** — **Decided:** customers with pools cannot be deleted; pools with visits cannot be deleted (Turn off instead).  

---

## 15. Definition of done for v1

A 1–10 tech company can:

1. Sign up and configure company (including currency/timezone).  
2. Add staff, customers, pools, and weekly schedules.  
3. Rely on the system to create the next two weeks of visits.  
4. Have technicians complete mobile visits with checklist, readings, and photos.  
5. Auto-email service reports on completion.  
6. Generate monthly invoices, email PDF, mark paid.  
7. See today's visit progress, customer count, and unpaid invoices on the admin dashboard.  

No data leaks across companies. No payment gateway. No customer login.

---

## 16. Changelog

| Date | What |
|------|------|
| 2026-11-14 | Created implementation doc from Product Spec v1 |
| 2026-11-14 | **P0 + Module 2 shipped:** company logo/currency/timezone, `BelongsToCompany`, permissions scaffold, Customers CRUD + tests + demo seed |
| 2026-11-14 | **Module 3 shipped:** pools + equipment + photo, nested under customers, tests + demo Main/Spa pools |
| 2026-11-14 | **Module 5 shipped:** weekly schedules, visit generator (14 days), admin visits board (reassign/reschedule/skip) |
| 2026-11-14 | **Module 6 shipped:** technician Today's Route, VisitService, checklist/readings/photos, mobile tech layout |
| 2026-11-14 | **Module 7 shipped:** service report email on complete, email_logs, admin preview/resend |
| 2026-11-14 | **Module 8 + Dashboard shipped:** invoices/PDF/send/mark paid, unpaid stats on dashboard |

---

*v1 implementation complete per Product Spec. Optional next: polish, invite-by-email, or v2 items (Stripe, customer portal, etc.).*
