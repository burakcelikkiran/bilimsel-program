# Route Audit

Generated as part of the stabilization plan. Last reviewed: 2026-07-01.

## Fixed in this pass

| Route | Issue | Resolution |
|-------|-------|------------|
| `admin/timeline/.../update-order` | Duplicate definition (lines 314 & 322) | Removed duplicate |
| `admin/participants/search` | Registered after resource; captured as `{participant}` | Moved before `Route::resource` |
| `events/*` (`*Page` methods) | Controller methods missing | Added `PublicEventController@*Page` + Inertia pages |
| `admin/ajax/.../available-time-slots` | Called private method | `getAvailableTimeSlots` made public |
| `admin/ajax/.../venue-conflicts` | Method missing | Added `getVenueConflicts` |
| `admin/timeline/settings` | Method missing | Added `settings` |
| `admin/quick/timeline` | Method missing | Added `quickAccess` |
| `admin/ajax/validate-presentation-move` | Method missing | Added `PresentationController@validateMove` |
| `api/v1/program-sessions/*` | No auth middleware | Wrapped in `auth:sanctum` |

## Remaining known issues

| Route | Issue | Priority |
|-------|-------|----------|
| `docs/` | ~~References missing `swagger-ui` Blade view~~ | Redirect to `/api/documentation` |
| `admin/presentations/*` move routes | ~~Methods missing~~ | `moveToSession`, `changeTime`, `reorderInSession` eklendi |
| `admin/venues` | Top-level resource overlaps event-nested venues | P2 — naming confusion |
| `admin/program-sessions/timeline` vs `admin/timeline/*` | Overlapping timeline entry points | P2 |

## Route layers

| Layer | Prefix | Status |
|-------|--------|--------|
| Public web | `/`, `/events/*` | Working (Inertia public pages) |
| Admin (Inertia) | `/admin/*` | Working |
| Public JSON API | `/api/v1/events/*` | Working (partial stubs) |
| Timeline JSON API | `/api/v1/program-sessions/*` | Secured with Sanctum |
| Admin REST API | `/api/v1/admin/*` | **Kısmen** — participants + event days read-only (Sanctum) |

## Verification

```bash
php artisan route:list --path=events
php artisan route:list --path=program-sessions
php artisan test
```
