# Bilimsel Program

Etkinlik ve bilimsel kongre programı yönetim sistemi. Organizasyonlar altında etkinlikler, program oturumları, sunumlar, katılımcılar ve sponsorlar yönetilir.

## Tech stack

- **Backend:** Laravel 12, Jetstream, Sanctum, Inertia Laravel v2
- **Frontend:** Vue 3, Tailwind CSS 3, Vite
- **API:** Public JSON (`/api/v1/*`), OpenAPI (l5-swagger)
- **Geliştirme:** Laravel Boost (MCP + AI guidelines), Laravel Herd

## Domain modeli

```
Organization → Event → EventDay → Venue → ProgramSession → Presentation
              ↳ Participant, Sponsor (organizasyon düzeyinde)
```

## Kurulum

```bash
composer install
cp .env.example .env   # gerekirse
php artisan key:generate
php artisan migrate --seed
npm install
```

### Geliştirme sunucusu

```bash
composer run dev
```

Bu komut eşzamanlı çalıştırır: `php artisan serve`, queue, `pail`, `npm run dev`.

Herd kullanıyorsanız site genelde `https://bilimsel-program.test` adresinde açılır.

## Ana URL'ler

| Amaç | URL |
|------|-----|
| Ana sayfa | `/` |
| Public etkinlik listesi | `/events` |
| Admin panel | `/admin` (giriş gerekli) |
| Public API | `/api/v1/events` |
| Admin Participants API | `/api/v1/admin/participants` (Sanctum) |
| Admin Event Days API | `/api/v1/admin/events/{id}/days` (Sanctum) |
| API sağlık | `/api/health` |
| Swagger UI | `/api/documentation` |
| Route denetimi | `docs/ROUTE_AUDIT.md` |

## API katmanları

1. **Inertia Admin** — `/admin/*` (ana yönetim arayüzü)
2. **Public JSON API** — `/api/v1/events/*` (yayınlanan etkinlikler, program)
3. **Timeline API** — `/api/v1/program-sessions/*` (Sanctum ile korumalı)
4. **Admin REST API** — `/api/v1/admin/participants`, `/api/v1/admin/events/{id}/days` (read-only, Sanctum)

## Laravel Boost

```bash
composer require laravel/boost --dev
php artisan boost:install
```

Cursor MCP: `.cursor/mcp.json` içinde `php artisan boost:mcp`. Guidelines: `.cursor/rules/laravel-boost.mdc`.

Faydalı araçlar: `search-docs`, `database-schema`, `list-routes`, `browser-logs`, `get-absolute-url`.

## Testler

```bash
php artisan test
php artisan test --filter=PublicEvent
```

## Dokümantasyon

- [Route audit](docs/ROUTE_AUDIT.md)
- [API/Admin envanter](docs/API_ADMIN_INVENTORY.md)

## Lisans

MIT
