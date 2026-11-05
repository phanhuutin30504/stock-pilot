# StockPilot

Trading Assistant cho thị trường chứng khoán Việt Nam.

## Cấu trúc

- `backend/` — Laravel 12 API (`/api/v1`)
- `frontend/` — Vue 3 + TypeScript + Vite
- `docs/` — báo cáo sprint
- `STOCKPILOT_SPRINT_1_PLAN.md` — kế hoạch Sprint 1

## Chạy local (Laragon)

### Backend

```powershell
cd backend
composer install
copy .env.example .env   # nếu chưa có; giữ APP_KEY hiện có nếu đã generate
php artisan key:generate # chỉ khi APP_KEY trống
php artisan migrate
php artisan db:seed
php artisan serve
```

Health: [http://127.0.0.1:8000/api/v1/health](http://127.0.0.1:8000/api/v1/health)

### Frontend

```powershell
cd frontend
npm install
copy .env.example .env
npm run dev
```

### Tests

```powershell
cd backend
php artisan test
```

Chi tiết nghiệm thu Sprint 1: `docs/SPRINT_1_REPORT.md`.
