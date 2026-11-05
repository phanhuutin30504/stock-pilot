# StockPilot -- Sprint 1 Implementation Plan

**Dành cho Cursor AI thực thi**\
**Ngày lập:** 05/11/2026\
**Phạm vi:** Backend Laravel 12 + frontend Vue 3, chạy local trên
Laragon, PostgreSQL.

------------------------------------------------------------------------

## 1. Mục tiêu

Xây dựng nền tảng kỹ thuật ban đầu cho StockPilot -- Trading Assistant
dành cho thị trường chứng khoán Việt Nam.

Sprint 1 tập trung vào: - Khởi tạo backend Laravel 12 và frontend Vue 3
tách biệt. - Thiết lập cấu trúc module theo nghiệp vụ. - Thiết kế
migrations, Eloquent models và relationships. - Xây dựng contracts, DTOs
và provider adapter để không phụ thuộc cứng vào một nhà cung cấp dữ
liệu. - Xây dựng service đồng bộ danh mục cổ phiếu bằng demo provider. -
Chuẩn bị queue jobs, sync logs và scheduler. - Tạo API health check để
frontend kết nối backend. - Viết tests cho các phần cốt lõi.

**Không triển khai trong Sprint 1:** dữ liệu thị trường thật, tính chỉ
báo kỹ thuật, chiến lược giao dịch, tín hiệu BUY/SELL, Telegram,
portfolio, backtest hoặc tự động đặt lệnh.

## 2. Bối cảnh và quyết định kỹ thuật

  -----------------------------------------------------------------------
  Thành phần                          Quyết định
  ----------------------------------- -----------------------------------
  Backend                             Laravel 12, PHP 8.3+

  Frontend                            Vue 3 + TypeScript + Vite

  Database                            PostgreSQL

  Local environment                   Windows + Laragon

  Queue Sprint 1                      Database queue để dễ chạy local

  Queue về sau                        Có thể chuyển Redis bằng cấu hình
                                      môi trường

  API                                 Laravel API, prefix `/api/v1`

  Timezone                            `Asia/Ho_Chi_Minh`

  Market                              Chứng khoán Việt Nam: HOSE, HNX,
                                      UPCOM

  Data frequency                      EOD trước; intraday 5 phút ở giai
                                      đoạn sau

  Data provider                       Chưa chốt; dùng Demo provider trong
                                      Sprint 1

  Provider ưu tiên đánh giá           SSI FastConnect, sau đó DNSE
                                      OpenAPI

  VNStock                             Chỉ cân nhắc như nguồn thử
                                      nghiệm/adapter riêng; không phụ
                                      thuộc trực tiếp trong Laravel
  -----------------------------------------------------------------------

### Nguyên tắc provider

Không gọi API của nhà cung cấp trực tiếp từ controller, model hoặc
business service. Mọi provider phải triển khai contract nội bộ và trả về
DTO chuẩn hóa.

Chưa được khẳng định provider có miễn phí, có quyền truy cập, có đủ lịch
sử hoặc có SLA cho đến khi kiểm tra tài liệu, điều khoản, quota và tài
khoản thực tế.

## 3. Cấu trúc repository

Nếu repository chưa tồn tại, tạo cấu trúc:

``` text
stockpilot/
├── backend/
└── frontend/
```

Backend:

``` text
backend/
├── app/
│   └── Providers/
│       └── AppServiceProvider.php
├── Modules/
│   ├── Shared/
│   │   ├── Enums/
│   │   └── Exceptions/
│   ├── Stock/
│   │   ├── Contracts/
│   │   ├── DTOs/
│   │   ├── Jobs/
│   │   ├── Models/
│   │   ├── Providers/
│   │   └── Services/
│   └── MarketData/
│       ├── Contracts/
│       ├── DTOs/
│       ├── Jobs/
│       ├── Models/
│       ├── Providers/
│       │   └── SSI/
│       └── Services/
├── config/
│   └── stockpilot.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── console.php
└── tests/
    ├── Feature/
    └── Unit/

frontend/
└── src/
    ├── api/
    ├── components/
    ├── layouts/
    ├── modules/
    │   ├── dashboard/
    │   ├── stocks/
    │   ├── screener/
    │   ├── signals/
    │   └── portfolio/
    ├── router/
    ├── stores/
    └── types/
```

Không tạo module rỗng hàng loạt nếu chưa có nội dung thực tế. Chỉ tạo
các thư mục cần dùng trong Sprint 1.

## 4. Laravel bootstrap và cấu hình

### 4.1. Khởi tạo

Nếu `backend/` chưa có Laravel project: - Tạo Laravel 12 trong
`backend/`. - Cài API scaffolding bằng lệnh Laravel phù hợp với phiên
bản đã cài. - Không ghi đè project hoặc file hiện có nếu repository đã
được khởi tạo. - Kiểm tra `php artisan --version`, `php -v`,
`composer --version`.

### 4.2. Autoload

Thêm PSR-4 mapping:

``` json
"Modules\\": "Modules/"
```

Giữ nguyên các mapping Laravel hiện có. Chạy `composer dump-autoload` và
xác nhận class trong `Modules\\` được autoload.

### 4.3. Environment

Bổ sung các biến cấu hình cần thiết vào `.env.example`; không commit
secret vào Git.

``` env
APP_NAME=StockPilot
APP_TIMEZONE=Asia/Ho_Chi_Minh

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=stockpilot
DB_USERNAME=postgres
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_STORE=database

MARKET_DATA_PROVIDER=demo
MARKET_DATA_SYNC_ENABLED=false
```

Tạo `config/stockpilot.php` để tập trung cấu hình: - timezone; -
provider mặc định; - bật/tắt đồng bộ; - batch size mặc định 50; - danh
sách exchange hợp lệ: HOSE, HNX, UPCOM; - timeframe hỗ trợ: `1d`, `5m`.

Không đọc trực tiếp `env()` bên ngoài file config.

### 4.4. Database

Tạo database PostgreSQL `stockpilot` trong Laragon/PostgreSQL. Không tự
ý thay đổi hoặc xóa database khác trên máy người dùng.

## 5. Database migrations

Tạo migrations theo thứ tự phụ thuộc.

### 5.1. `industries`

Các cột: - `id`; - `code` varchar(50), unique; - `name`; - `parent_id`
nullable, self-reference, `nullOnDelete`; - `is_active` boolean default
true; - timestamps.

### 5.2. `stocks`

Các cột: - `id`; - `symbol` varchar(20), unique; - `name`; - `exchange`
varchar(20), index; - `industry_id` nullable foreign key đến industries,
`nullOnDelete`; - `company_name` nullable; - `is_active` boolean default
true; - `metadata` nullable JSONB; - timestamps.

Index bổ sung: `(exchange, is_active)`.

### 5.3. `stock_bars`

Tạo bảng để khóa schema dữ liệu OHLCV cho Sprint 2; chưa cần ingestion
dữ liệu thật trong Sprint 1.

Các cột: - `id`; - `stock_id` foreign key, cascade delete; - `timeframe`
varchar(10), dùng `1d` và `5m`; - `trading_date` date; - `started_at`
timestamp with timezone; - `open`, `high`, `low`, `close`
decimal(18,4); - `volume` unsigned big integer default 0; -
`is_adjusted` boolean default false; - `source` varchar(50); -
timestamps.

Unique key: `(stock_id, timeframe, started_at, is_adjusted)`.\
Index: `(stock_id, timeframe, started_at)` và
`(timeframe, trading_date)`.

Quy tắc: - Giá OHLC được chuẩn hóa về VND trước khi lưu. - Không trộn
giá điều chỉnh và chưa điều chỉnh. - `started_at` là thời điểm bắt đầu
nến theo timezone được chuẩn hóa; không phải thời điểm nhận dữ liệu. -
Kiểm tra khả năng hỗ trợ `timestampTz` của PostgreSQL migration driver
đang dùng. - Không dùng float làm kiểu lưu trữ giá trong database.

### 5.4. `market_indices`

Các cột: - `id`; - `symbol` varchar(30), unique; - `name`; - `exchange`
nullable; - `is_active` boolean default true; - timestamps.

Seed các mã chỉ số phổ biến như VNINDEX, VN30, HNX, UPCOM nếu xác định
được mã provider tương ứng. Không tự giả định mã API của provider.

**Lưu ý:** Sprint 1 chưa lưu OHLCV chỉ số. Sprint 2 sẽ quyết định thiết
kế bảng nến chỉ số riêng, không ép dữ liệu index vào `stock_bars` vì
bảng này có foreign key `stock_id`.

### 5.5. `market_data_sync_logs`

Các cột: - `id`; - `provider` varchar(50); - `sync_type` varchar(50); -
`symbol` nullable; - `status` varchar(20), default `pending`; -
`started_at`, `finished_at` nullable timestamp with timezone; -
`records_count` unsigned integer default 0; - `error_message` nullable
text; - `metadata` nullable JSONB; - timestamps.

Index: `(provider, sync_type, status)` và `started_at`.

Không lưu access token, API key hoặc secret vào `metadata` hay
`error_message`.

## 6. Eloquent models

Tạo: - `Modules\Stock\Models\Industry` - `Modules\Stock\Models\Stock` -
`Modules\MarketData\Models\StockBar` -
`Modules\MarketData\Models\MarketIndex` -
`Modules\MarketData\Models\MarketDataSyncLog`

Yêu cầu: - Khai báo `$fillable` rõ ràng. - Khai báo casts cho boolean,
JSON, decimal, date và datetime. - Khai báo relationships. - Không dùng
`$guarded = []` một cách tùy tiện. - Tránh N+1 khi truy vấn quan hệ. -
Kiểm tra model class namespace và autoload.

Relationships: - Industry `parent()` belongsTo Industry. - Industry
`children()` hasMany Industry. - Industry `stocks()` hasMany Stock. -
Stock `industry()` belongsTo Industry. - Stock `bars()` hasMany
StockBar. - StockBar `stock()` belongsTo Stock.

## 7. DTOs và provider contracts

### 7.1. StockData

Tạo immutable DTO `Modules\Stock\DTOs\StockData` với: - `symbol`; -
`name`; - `exchange`; - `companyName` nullable; - `industryCode`
nullable.

Có method chuyển thành array phù hợp với persistence. Chuẩn hóa symbol
và exchange thành uppercase, trim chuỗi đầu vào và validate dữ liệu bắt
buộc.

### 7.2. StockCatalogProviderInterface

Tạo contract:

``` php
interface StockCatalogProviderInterface
{
    /** @return iterable<StockData> */
    public function fetchStocks(): iterable;

    public function providerName(): string;
}
```

### 7.3. MarketBarData

Tạo immutable DTO `Modules\MarketData\DTOs\MarketBarData`: -
startedAt; - open; - high; - low; - close; - volume; - isAdjusted.

Validate OHLC: - giá phải dương; - high \>= open và close; - low \<=
open và close; - high \>= low; - volume không âm.

Dùng decimal-safe representation khi có thể; không thực hiện phép tính
tiền bằng float nếu không cần thiết. DTO cần có mapper tạo attributes để
persistence.

### 7.4. MarketDataProviderInterface

Tạo contract gồm: - `fetchDailyBars(symbol, from, to)`; -
`fetchIntradayBars(symbol, date, timeframe = '5m')`; - `providerName()`.

Dùng `Carbon\CarbonInterface` cho tham số ngày. Return iterable DTO.
Không đưa response JSON riêng của provider vào domain service.

Nếu chưa triển khai provider thật, chỉ tạo contract và test contract
behavior qua fake/demo implementation; không tạo code SSI giả định
endpoint hoặc authentication.

## 8. Demo provider và dependency injection

Tạo `DemoStockCatalogProvider` triển khai
`StockCatalogProviderInterface`.

Dữ liệu demo tối thiểu: - FPT -- HOSE; - PVD -- HOSE; - HPG -- HOSE; -
SSI -- HOSE; - VNM -- HOSE.

Ghi rõ đây là dữ liệu demo, không đại diện danh mục chứng khoán đầy đủ
hay dữ liệu thị trường realtime.

Đăng ký binding trong service provider. Binding phải lấy provider name
từ `config('stockpilot.provider')`, dùng mapping rõ ràng và fail-fast
với provider không được hỗ trợ. Không dùng chuỗi class name tùy tiện từ
request người dùng.

Trong Sprint 1, chỉ cần `demo`. Chuẩn bị điểm mở rộng cho SSI/DNSE nhưng
chưa gọi API thật.

## 9. StockCatalogSyncService

Tạo `Modules\Stock\Services\StockCatalogSyncService`.

Trách nhiệm: - Nhận `StockCatalogProviderInterface` qua dependency
injection. - Fetch danh mục. - Chuẩn hóa và validate từng StockData. -
Upsert theo `symbol`. - Gắn industry nếu industry code đã có mapping;
không tự tạo ngành không rõ nghĩa. - Không xóa hoặc deactivate các mã
không xuất hiện trong một lần sync, trừ khi có cơ chế full snapshot được
xác nhận. - Trả về số bản ghi insert/update hoặc một result object rõ
ràng. - Có transaction phù hợp. - Ghi sync log trạng thái
success/failed, thời gian và số lượng. - Không log secret.

Lưu ý: nếu danh mục lớn hoặc provider trả dữ liệu theo trang, tránh giữ
transaction mở trong lúc gọi mạng. Fetch/validate trước, sau đó upsert
theo chunk trong transaction. Sprint 1 demo nhỏ có thể xử lý đơn giản,
nhưng service không được thiết kế theo cách khóa database trong suốt
thời gian gọi API.

## 10. Queue job

Tạo `SyncStockCatalogJob`: - implements `ShouldQueue`; - có retry và
backoff; - timeout phù hợp; - unique job hoặc cơ chế lock để tránh sync
catalog chạy đồng thời; - gọi service qua dependency injection; - log
lỗi có context nhưng không có secret; - `failed()` ghi log hoặc cập nhật
sync record nếu có.

Không dispatch job từ constructor. Không đặt logic nghiệp vụ trong Job.

Queue Sprint 1 dùng database. Kiểm tra migration queue theo phiên bản
Laravel đã cài, tránh tạo bảng trùng nếu Laravel đã có migration tương
ứng.

## 11. Scheduler

Dùng `routes/console.php` theo Laravel 12.

Tạo lịch đồng bộ danh mục demo/provider: - chạy theo ngày làm việc vào
18:00 giờ Việt Nam; - chỉ dispatch khi `stockpilot.sync.enabled=true`; -
`withoutOverlapping()`; - `onOneServer()` chỉ bật nếu môi trường
cache/lock hỗ trợ phù hợp. Nếu local single instance không cần, ghi chú
rõ.

Không bật scheduler đồng bộ market bars thật trong Sprint 1.

Kiểm tra bằng: - `php artisan schedule:list`; -
`php artisan schedule:work`.

Lịch EOD tương lai (chưa bật trong Sprint 1): - sync EOD sau khi thị
trường đóng và provider cập nhật dữ liệu; - validate dữ liệu; - tính
indicators; - scan strategy; - tạo signal; - gửi Telegram. Không giả
định ngày giao dịch chỉ là thứ Hai--thứ Sáu; Sprint sau cần trading
calendar/ngày nghỉ lễ và xác nhận dữ liệu đã hoàn tất.

## 12. API health check

Trong `routes/api.php`, tạo: `GET /api/v1/health`

Response JSON gồm: - success; - application; - environment; - timestamp
ISO-8601.

Không trả về DB password, env secrets, filesystem path hoặc thông tin
nhạy cảm.

Có thể kiểm tra kết nối database trong health check ở mức trạng thái
`database: ok/error`, nhưng không trả exception message cho client ở
production.

## 13. Frontend Vue 3

Nếu `frontend/` chưa có project: - tạo Vue 3 + TypeScript + Vite; - cài
Axios, Pinia, Vue Router, TanStack Query for Vue; - tạo
`src/api/http.ts`; - cấu hình `VITE_API_BASE_URL`; - tạo health check
API call; - hiển thị trạng thái backend kết nối được/không kết nối được.

Không xây dựng dashboard giao dịch hoàn chỉnh trong Sprint 1. Không
hardcode URL API trong component.

## 14. Tests bắt buộc

### Unit tests

-   StockData chuẩn hóa symbol/exchange.
-   StockData từ chối symbol/name/exchange rỗng.
-   MarketBarData kiểm tra OHLC hợp lệ.
-   Demo provider trả đúng DTO.
-   Provider binding chọn đúng Demo provider.
-   StockCatalogSyncService upsert idempotent: chạy hai lần không tạo
    duplicate.
-   Service không deactivate mã chỉ vì provider không trả mã đó trong
    một lần sync.

### Feature tests

-   Migration chạy được trên PostgreSQL.
-   API health trả HTTP 200 và JSON schema đúng.
-   Sync catalog tạo các stock demo.
-   Job dispatch và xử lý được với queue fake/worker test phù hợp.

Không chỉ chạy test trên SQLite nếu migration dùng JSONB, timestamp
timezone hoặc tính năng PostgreSQL-specific. Phải có ít nhất một lượt
test integration trên PostgreSQL.

## 15. Lệnh nghiệm thu local

Backend:

``` powershell
cd backend
composer dump-autoload
php artisan optimize:clear
php artisan migrate
php artisan db:seed
php artisan route:list --path=api
php artisan schedule:list
php artisan test
```

Chạy queue:

``` powershell
php artisan queue:work database --queue=default --tries=3 --timeout=120
```

Chạy scheduler:

``` powershell
php artisan schedule:work
```

Frontend:

``` powershell
cd frontend
npm install
npm run dev
```

Kiểm tra: - `GET /api/v1/health` trả HTTP 200. - Frontend gọi được
health endpoint. - Demo sync tạo FPT, PVD, HPG, SSI, VNM. - Chạy sync
hai lần không tạo duplicate. - Job chạy qua queue. - Scheduler hiển thị
đúng timezone và lịch. - `php artisan test` pass. - Không có secret
trong Git diff.

## 16. Tiêu chí hoàn thành Sprint 1

Sprint chỉ được đánh dấu hoàn thành khi: 1. Laravel 12 chạy trên
Laragon. 2. PostgreSQL kết nối thành công. 3. Namespace `Modules\\`
autoload đúng. 4. Migrations chạy và rollback được. 5. Models và
relationships hoạt động. 6. DTO validation hoạt động. 7. Provider
contract và Demo provider hoạt động. 8. Provider được resolve qua
dependency injection. 9. Catalog sync idempotent và có log. 10. Queue
job chạy, retry được và tránh duplicate. 11. Scheduler đăng ký đúng
timezone. 12. API health hoạt động. 13. Vue 3 chạy độc lập và gọi được
API. 14. Tests cần thiết pass trên PostgreSQL.

## 17. Quy tắc thực thi dành cho Cursor

Cursor phải làm việc theo từng phase, không tạo toàn bộ code một cách mù
quáng.

### Quy trình bắt buộc

1.  Đọc toàn bộ repository hiện tại trước khi sửa.
2.  Kiểm tra PHP, Composer, Laravel, Node, npm và PostgreSQL.
3.  Báo cáo cấu trúc hiện tại và các file sẽ tạo/sửa.
4.  Nếu project đã tồn tại, bảo toàn code và cấu hình hiện có; không ghi
    đè file quan trọng.
5.  Triển khai lần lượt:
    -   Phase 1: bootstrap/autoload/config;
    -   Phase 2: migrations/models;
    -   Phase 3: DTO/contracts/providers;
    -   Phase 4: services/sync logs;
    -   Phase 5: jobs/queue/scheduler;
    -   Phase 6: API health;
    -   Phase 7: Vue frontend integration;
    -   Phase 8: tests và nghiệm thu.
6.  Sau mỗi phase, chạy lệnh kiểm tra tương ứng và sửa lỗi trước khi
    sang phase tiếp theo.
7.  Không tự ý thêm package nếu chưa giải thích lý do.
8.  Không tích hợp provider thương mại khi chưa có credentials và tài
    liệu chính thức.
9.  Không tạo dữ liệu giá giả rồi trình bày như dữ liệu thật.
10. Không triển khai auto-trading, khuyến nghị mua bán hoặc Telegram
    trong Sprint 1.
11. Không xóa database, migration, file hoặc dữ liệu hiện có.
12. Cuối cùng tạo `docs/SPRINT_1_REPORT.md` gồm:
    -   những gì đã triển khai;
    -   danh sách file tạo/sửa;
    -   lệnh đã chạy;
    -   kết quả test;
    -   vấn đề còn tồn tại;
    -   các bước người dùng cần tự cấu hình.

### Cách Cursor phản hồi

-   Nêu rõ phase đang thực hiện.
-   Nêu file đã tạo/sửa.
-   Nêu kết quả kiểm tra thực tế.
-   Không tuyên bố test pass nếu chưa chạy.
-   Nếu thiếu thông tin hoặc gặp xung đột cấu hình, dừng ở điểm an toàn
    và hỏi người dùng; không tự suy đoán làm thay đổi môi trường.

------------------------------------------------------------------------

## 18. Prompt ngắn để giao Cursor

Hãy đọc file `docs/STOCKPILOT_SPRINT_1_PLAN.md` và thực thi đúng kế
hoạch.

Trước tiên kiểm tra repository và môi trường hiện tại. Không ghi đè
code/cấu hình đang có. Chia công việc theo phase, hoàn thành và kiểm thử
từng phase trước khi tiếp tục. Ưu tiên PostgreSQL, Laravel 12, PHP 8.3+,
Vue 3 + TypeScript + Vite và local Laragon. Dùng Demo provider, chưa
tích hợp dữ liệu thị trường thật. Không triển khai trading signals,
Telegram hoặc auto-trading trong Sprint 1.

Nếu repository hiện tại chưa có cấu trúc `stockpilot/backend` và
`stockpilot/frontend`, hãy đề xuất cách tạo an toàn trước khi thực hiện.
Cuối cùng tạo `docs/SPRINT_1_REPORT.md` và báo cáo chính xác các
lệnh/test đã chạy.
