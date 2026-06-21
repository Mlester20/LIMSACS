# Admin Module — Workflow & Code Review Suggestions

Scope: `resources/views/admin/**`, `app/controllers/admin/**`, `app/models/admin/**`, and related `app/services/**` used by admin pages (dashboard, users, school year, academic history, audit logs, settings).

Findings are grouped by priority. Each item cites the file/line where it was observed.

---

## 1. Security (fix first)

| Issue | Location | Detail |
|---|---|---|
| **Stored XSS** | `resources/views/admin/audit-logs.php:62-66` | `user_fullName`, `role`, `module`, `description`, `status` are echoed without `htmlspecialchars()`. Other admin views (dashboard, users, school-year) do escape output — audit-logs is the outlier. Any user-controllable string that ends up in an audit log description can execute script in the admin's browser. |
| **No CSRF protection anywhere in admin** | all forms in `users.php`, `school-year.php`, `audit-logs.php`, `settings.php` | No CSRF token field/verification on any POST form (create/edit/delete user, create/edit/delete school year, delete log, update profile/password). All state-changing actions are forgeable from another origin. |
| **No self-protection on user deletion** | `app/controllers/admin/UsersController.php` | Nothing stops an admin from deleting/demoting their own currently-logged-in account via the same Delete button used for other users. |
| **Misleading flash message on failure** | `app/controllers/admin/AuditLogsController.php:25` | `FlashMessage::setFlash("success", "Error deleting log.")` — reports an error as a success. |
| **No server-side input validation** | `app/controllers/admin/UsersController.php:115-120` | Email format, password strength, and `role` enum are never validated server-side before insert — only relies on client-side form attributes. |
| **No server-side date validation** | `app/controllers/admin/SchoolYearController.php:104-109` | `start_date <= end_date` and date format are only checked client-side; nothing stops malformed data via direct POST. |
| **Unvalidated file upload** | `resources/views/admin/settings.php:100` | Profile picture upload has no server-side MIME-type or size check — only relies on filename. |
| **String-interpolated SQL fragment** | `app/models/admin/DashboardModel.php:142` | `WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)` interpolates `$months` directly. It's currently bounded by `max(1, $months)` upstream so not exploitable today, but it's the one query in the file that breaks from prepared-statement style — easy to regress later. |

**Recommended next step:** add a small CSRF helper (token generation + verification) used by every admin POST form, escape all view output by default, and add server-side validation in the controllers that currently trust `$_POST` directly.

---

## 2. Workflow / UX gaps

- **No search or filter** on Users, School Years, Academic History, or Audit Logs — every list is "browse the whole table." For a records system this is the biggest day-to-day friction point for an admin.
- **No pagination on Users or School Years** (`app/models/admin/UsersModel.php:9`, `app/models/admin/SchoolYearModel.php:9` have no `LIMIT`) — the full table loads into memory and renders every row. Academic History and Audit Logs *do* paginate, but inconsistently (see §3).
- **No bulk actions** (e.g. delete/export multiple rows at once) anywhere.
- **No export** (CSV/Excel/PDF) for Academic History or Audit Logs, despite both being report-shaped data admins likely need to hand off.
- **No column sorting** on any data table.
- **Mislabeled page title**: `resources/views/admin/academic-history.php:19` shows "Dashboard" in the `<title>`/header instead of "Academic History."
- **School Year page has no link to "view students enrolled in this year"** — admin has to navigate elsewhere to cross-reference.
- **Settings page**: Role and "Member Since" fields are shown disabled with no path to change them from this screen (`resources/views/admin/settings.php:133,140`) — fine if intentional, but there's no affordance pointing admins to where role changes *do* happen (Users page).
- **Dashboard section-capacity bar caps the visual width at 100%** (`resources/views/admin/dashboard.php:432`, `min($pct, 100)`) but doesn't show that the real value is over 100% — an over-capacity section looks identical to a full one.
- **Delete confirmations are plain `confirm()`** dialogs everywhere, with no extra friction (e.g. typing a name) for destructive, hard-to-reverse actions like deleting a school year or user.

---

## 3. Code quality / consistency

- **Pagination is implemented three different ways**:
  - `AcademicHistoryController.php:18-39` — proper SQL `LIMIT`/`OFFSET` with a separate count query.
  - `AuditLogsController.php:36-45` — loads *all* rows, then paginates with PHP `array_slice` (`app/controllers/admin/AuditLogsController.php:45`). This defeats the purpose of pagination performance-wise and should be moved to SQL.
  - Page-range rendering logic is duplicated inline in the views instead of a shared helper/partial.
- **Controller pattern is inconsistent**: `UsersController` and `SchoolYearController` extend the base `Controller` class; `AuditLogsController` and `AcademicHistoryController` don't. Some controllers take `$con` via constructor injection (`DashboardController`), others reach for a global `$con` (`UsersController`, `AcademicHistoryController`).
- **No routing layer** — admin pages `require_once` their controller directly and execute it inline in the view's bootstrap code, rather than going through a front controller/router. This makes it hard to add things like CSRF middleware or centralized auth in one place.
- **Duplicated modal markup**: the Add/Edit modal structure in `users.php` and `school-year.php` is nearly identical but not shared as a partial.
- **`DashboardController`/`DashboardModel` do too much in one place**: 17 separate stat-fetching methods (`app/models/admin/DashboardModel.php`) called individually from `DashboardController::index()` (`app/controllers/admin/DashboardController.php:14-35`). Worth extracting into a `DashboardStatsService` with grouped queries.
- **Inconsistent error handling/return types** across models — e.g. `AuditLogsModel` returns an error *string* on failure in one method but an array on success; `UsersModel` returns `false`; `UsersController.php:48-50` logs an exception and calls `exit()` with no user-facing flash message.
- **Markup bug**: `resources/views/admin/school-year.php:268` — stray/misplaced `</thead>` relative to `</tbody>` (table structure is malformed, though browsers tolerate it).
- **Hardcoded page-size constants** scattered across files (`10` in `dashboard.php:337`, `dashboard.php:456`, `AuditLogsController.php:37`, `AcademicHistoryController.php:19`) instead of one config value.

---

## 4. Performance

- **`AuditLogsController.php:45`**: fetches the entire `audit_logs` table on every page view, then slices in PHP. Switch to `LIMIT`/`OFFSET` in the model query, same as `AcademicHistoryModel`.
- **Unbounded list queries**: `UsersModel.php:9` and `SchoolYearModel.php:9` have no `LIMIT` — fine while data is small, but will degrade as the tables grow. Add pagination now while it's cheap to do.
- **Dashboard issues 17 separate queries per page load** (`app/models/admin/DashboardModel.php`) with no caching. Several of these (status counts, document counts) could be combined into fewer aggregate queries (e.g. one `GROUP BY status` query instead of 3 separate `COUNT(*) WHERE status = ...` calls).
- No caching layer for dashboard stats that don't need to be real-time-accurate to the second (e.g. a 30–60s cache would cut load significantly on a high-traffic admin dashboard).

---

## 5. Quick wins (low effort, high value)

1. Fix the audit-logs XSS by wrapping the 5 echoed fields in `htmlspecialchars()` — matches the pattern already used elsewhere.
2. Fix the inverted success/error flash message in `AuditLogsController.php:25`.
3. Fix the academic-history page `<title>`/header text.
4. Add a guard in the user-delete flow to block an admin from deleting their own account.
5. Add `LIMIT`/`OFFSET` to `UsersModel`/`SchoolYearModel` queries instead of loading entire tables.

## 6. Larger follow-ups (worth planning separately)

1. Introduce a shared CSRF token helper and wire it into every admin POST form.
2. Add server-side validation (email format, password policy, role whitelist, date-range sanity) in `UsersController` and `SchoolYearController`.
3. Standardize pagination (SQL-level, one shared helper) and apply it to Users and School Years.
4. Add search/filter to Users, School Years, Academic History, and Audit Logs.
5. Consolidate `DashboardModel`'s 17 queries into a leaner stats service with optional short-lived caching.