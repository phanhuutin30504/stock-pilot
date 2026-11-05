# StockPilot — Sprint 1 Report

**Ngày:** 05/11/2026  
**Branch:** `feature/sprint-1`  
**Môi trường:** Windows + Laragon, PHP 8.3.30, Laravel 12.71.0, PostgreSQL 18.2, Node 22.22.0

## Đã triển khai

1. Bootstrap Laravel 12 trong `backend/` + Vue 3 + TypeScript + Vite trong `frontend/`.
2. PSR-4 autoload `Modules\\` → `Modules/`.
3. Config `config/stockpilot.php` (timezone, provider, sync flag, batch size, exchanges, timeframes).
4. Migrations PostgreSQL: `industries`, `stocks`, `stock_bars`, `market_indices`, `market_data_sync_logs` (+ jobs/cache sẵn có của Laravel).
5. Eloquent models + relationships trong Modules Stock / MarketData.
6. DTOs `StockData`, `MarketBarData`, `StockCatalogSyncResult` kèm validation.
7. Contracts `StockCatalogProviderInterface`, `MarketDataProviderInterface`.
8. `DemoStockCatalogProvider` + `DemoMarketDataProvider` (demo only).
9. DI binding fail-fast theo `config('stockpilot.provider')`.
10. `StockCatalogSyncService` upsert idempotent, không deactivate mã thiếu trong 1 lần sync, ghi sync log.
11. `SyncStockCatalogJob` (queue, retry/backoff, unique).
12. Scheduler weekdays 18:00 `Asia/Ho_Chi_Minh` khi `MARKET_DATA_SYNC_ENABLED=true`.
13. API `GET /api/v1/health`.
14. Frontend health check (Axios + Pinia + Vue Router + TanStack Query).
15. Unit/Feature tests chạy trên PostgreSQL (`stockpilot_testing`).

## File tạo / sửa chính

### Backend
- `backend/composer.json` (autoload Modules)
- `backend/config/stockpilot.php`, `backend/config/app.php` (timezone)
- `backend/.env.example`, `backend/bootstrap/app.php` (api routes)
- `backend/routes/api.php`, `backend/routes/console.php`
- `backend/app/Providers/AppServiceProvider.php`
- `backend/app/Http/Controllers/Api/V1/HealthController.php`
- `backend/Modules/**` (Contracts, DTOs, Models, Providers, Services, Jobs, Exceptions)
- `backend/database/migrations/2026_11_05_10000*_*.php`
- `backend/database/seeders/MarketIndexSeeder.php`, `DatabaseSeeder.php`
- `backend/phpunit.xml`, `backend/tests/**`

### Frontend
- `frontend/` Vue 3 + TS scaffold
- `frontend/src/api/http.ts`, `health.ts`
- `frontend/src/stores/health.ts`
- `frontend/src/modules/dashboard/DashboardView.vue`
- `frontend/src/router/index.ts`
- `frontend/.env.example`

### Repo
- `.gitignore`
- `docs/SPRINT_1_REPORT.md`

## Lệnh đã chạy

```powershell
# Môi trường
php -v
composer --version
node -v
npm -v
psql --version

# DB
psql -U postgres -h 127.0.0.1 -c "CREATE DATABASE stockpilot;"
psql -U postgres -h 127.0.0.1 -c "CREATE DATABASE stockpilot_testing;"

# Backend
composer create-project laravel/laravel:^12.0 backend --no-interaction
cd backend
composer dump-autoload
php artisan migrate --force
php artisan db:seed --force
php artisan route:list --path=api
php artisan schedule:list
php artisan migrate:rollback --step=5 --force
php artisan migrate --force
php artisan test

# Demo sync (verified): FPT,HPG,PVD,SSI,VNM — lần 2 inserted=0
php artisan serve --host=127.0.0.1 --port=8000

# Frontend
cd ../frontend
npm install
npm install axios pinia vue-router @tanstack/vue-query
npm run build
```

## Kết quả test

```text
Tests:    19 passed (47 assertions)
Duration: ~2.65s
Database: PostgreSQL stockpilot_testing
```

Health live check:

```json
{
  "success": true,
  "application": "StockPilot",
  "environment": "local",
  "timestamp": "<ISO-8601>",
  "database": "ok"
}
```

Demo sync: symbols `FPT,HPG,PVD,SSI,VNM`; sync lần 2 không tạo duplicate.

Scheduler: `0 18 * * 1-5 stockpilot-sync-stock-catalog` (timezone VN).

Frontend production build: OK (`vue-tsc` + `vite build`).

## Vấn đề còn tồn tại / ngoài phạm vi Sprint 1

- Chưa tích hợp SSI/DNSE thật (chưa credentials/tài liệu).
- Chưa ingestion OHLCV thật, indicators, signals, Telegram, portfolio, backtest.
- Scheduler catalog mặc định **tắt** (`MARKET_DATA_SYNC_ENABLED=false`).
- `onOneServer()` chưa bật (local single instance).
- Password PostgreSQL local đang để trống trong `.env` (không commit).

## Việc người dùng cần tự cấu hình

1. Đảm bảo PostgreSQL Laragon đang chạy; DB `stockpilot` đã được tạo trong phiên làm việc này.
2. Nếu postgres có password: cập nhật `backend/.env` → `DB_PASSWORD=...` (và `phpunit.xml` nếu cần).
3. Chạy backend: `cd backend && php artisan serve`.
4. Chạy queue (khi dùng job): `php artisan queue:work database --queue=default --tries=3 --timeout=120`.
5. Bật sync theo lịch (tuỳ chọn): `MARKET_DATA_SYNC_ENABLED=true` rồi `php artisan schedule:work`.
6. Frontend: copy `frontend/.env.example` → `.env`, chỉnh `VITE_API_BASE_URL`, rồi `npm run dev`.
7. Không commit file `.env` / secrets.
