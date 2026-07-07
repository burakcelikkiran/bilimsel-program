# API/Admin Controller Inventory

Hibrit strateji: bu katman şu an **route'a bağlı değil**. Swagger dokümantasyonu ve gelecekteki `/api/v1/admin/*` endpoint'leri için referans.

| Controller | Durum | Not |
|------------|-------|-----|
| [ParticipantController.php](../app/Http/Controllers/API/Admin/ParticipantController.php) | **Gerçek** | Tam CRUD + search/statistics; `import`/`export` → 501 |
| [EventDayController.php](../app/Http/Controllers/API/Admin/EventDayController.php) | **Gerçek** | JsonResponse implementasyonları mevcut |
| [VenueController.php](../app/Http/Controllers/API/Admin/VenueController.php) | **Gerçek** | JsonResponse CRUD |
| [ProgramSessionController.php](../app/Http/Controllers/API/Admin/ProgramSessionController.php) | **Stub** | Metot gövdeleri `// Method implementation...` |
| [PresentationController.php](../app/Http/Controllers/API/Admin/PresentationController.php) | **Karışık** | Bazı metotlar gerçek, bazıları eksik |
| [ProgramSessionCategoryController.php](../app/Http/Controllers/API/Admin/ProgramSessionCategoryController.php) | **Gerçek** | JsonResponse + `getForSelect` |
| [EventController.php](../app/Http/Controllers/API/Admin/EventController.php) | **Kısmi** | Yalnızca `getForSelect`, `analytics` |
| [OrganizationController.php](../app/Http/Controllers/API/Admin/OrganizationController.php) | **Stub** | Tüm CRUD metotları boş gövde |
| ~~SponsorController.php~~ | **Kaldırıldı** | Duplicate Inertia kopyası silindi; sponsor yönetimi `Admin\SponsorController` üzerinden |
| [Schemas + Form Requests](../app/Http/Requests/API/Admin/) | **Swagger** | OpenAPI şemaları; controller dışı |

## Önerilen kademeli açılım

1. **Faz A** — ~~Route'suz duplicate'leri temizle~~ `SponsorController` (API/Admin) kaldırıldı.
2. **Faz B** — ~~Mobil/entegrasyon için read-only~~ `/api/v1/admin/participants` (index, show, search) + Sanctum **aktif**.
3. **Faz C (devam)** — `ParticipantController` Swagger şemaları `Schemas.php`'ye taşındı; `l5-swagger` taramasına eklendi. `/api/v1/admin/events/{event:id}/days` read-only (index, show) + Sanctum **aktif**.
4. **Faz D** — `ProgramSessionController` API stub'larını `Admin\ProgramSessionController` ile paylaşılan service'e taşı.

## Karşılaştırma: Admin vs API/Admin

| Kaynak | Route | Response tipi |
|--------|-------|---------------|
| `Controllers/Admin/*` | `/admin/*` | Inertia |
| `Controllers/API/PublicEventController` | `/api/v1/events/*` | JSON |
| `Controllers/API/ProgramSessionApiController` | `/api/v1/program-sessions/*` | JSON (Sanctum) |
| `Controllers/API/Admin/*` | `/api/v1/admin/*` | Participants + event days read-only (Sanctum) |

## l5-swagger taraması

Aktif annotation dosyaları (`config/l5-swagger.php`):

- `API/Schemas.php` — paylaşılan şemalar (ParticipantWithStats dahil)
- `API/PublicEventController.php`
- `API/EventProgramController.php`
- `API/Admin/VenueController.php`
- `API/Admin/EventController.php`
- `API/Admin/ParticipantController.php`

**Bilinçli olarak hariç:** `EventDayController`, `OrganizationController` — route'lanmamış yazma endpoint'leri ve eksik stub'lar nedeniyle.

Üretim: `php artisan l5-swagger:generate`
