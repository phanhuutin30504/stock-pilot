# StockPilot -- Multi-Sprint Master Plan

**Dành cho Cursor AI / developer thực thi theo từng sprint**\
**Ngày lập:** 05/11/2026\
**Mục đích:** Chốt roadmap Sprint 1→6 một lần, tránh phải viết lại plan
sau mỗi sprint.\
**Baseline kỹ thuật:** Laravel 12, PHP 8.3+, Vue 3 + TypeScript + Vite,
PostgreSQL, Laragon, timezone `Asia/Ho_Chi_Minh`, API `/api/v1`.

------------------------------------------------------------------------

## 0. Cách dùng file này

1. Sprint đang làm phải có mục tiêu, phạm vi IN/OUT, migrations,
   modules, API, frontend, tests và tiêu chí hoàn thành trong file này.
2. Khi bắt đầu một sprint: mở đúng section sprint đó + section
   **Nguyên tắc toàn dự án**; không tự mở rộng scope sang sprint sau.
3. Nếu phát hiện blocker (provider thiếu quyền, API đổi, thiếu calendar
   nghỉ lễ…): dừng ở điểm an toàn, cập nhật section sprint tương ứng,
   rồi mới code tiếp.
4. Cuối mỗi sprint: tạo `docs/SPRINT_N_REPORT.md` (giống Sprint 1).
5. Sprint 1 đã hoàn thành nền tảng; section Sprint 1 chỉ là tham chiếu.

### Prompt ngắn khi giao Cursor

``` text
Đọc STOCKPILOT_MULTI_SPRINT_PLAN.md và thực thi đúng Sprint N.
Không làm việc của sprint sau. Giữ nguyên kiến trúc Modules + provider
contracts/DTOs. Ưu tiên PostgreSQL. Viết tests bắt buộc. Cuối sprint
tạo docs/SPRINT_N_REPORT.md.
```

------------------------------------------------------------------------

## 1. Tầm nhìn sản phẩm

StockPilot là **Trading Assistant** cho chứng khoán Việt Nam
(HOSE / HNX / UPCOM):

- Đồng bộ danh mục và dữ liệu giá (EOD trước, intraday sau).
- Tính chỉ báo kỹ thuật.
- Quét chiến lược → sinh tín hiệu BUY / SELL / HOLD.
- Thông báo (Telegram) + theo dõi watchlist/portfolio thủ công.
- Backtest chiến lược trên lịch sử.
- **Không** tự động đặt lệnh trong phạm vi Sprint 1–6.

Pipeline EOD mục tiêu (cuối Sprint 4–5):

``` text
Trading day đóng cửa
  → xác nhận là ngày giao dịch (calendar)
  → sync EOD bars từ provider
  → validate OHLC
  → tính indicators
  → scan strategies
  → tạo signals
  → gửi Telegram (nếu bật)
```

------------------------------------------------------------------------

## 2. Nguyên tắc toàn dự án (áp dụng mọi sprint)

1. **Provider isolation:** không gọi API nhà cung cấp từ controller,
   model, job nghiệp vụ hay Vue. Chỉ qua `Contracts` + DTO nội bộ.
2. **Fail-fast binding:** provider name lấy từ `config/stockpilot.php`,
   mapping class rõ ràng; provider không hỗ trợ thì throw.
3. **Không secret trong Git / log / sync metadata.**
4. **Giá lưu decimal / string decimal-safe; không float cho tiền.**
5. **OHLC chuẩn hóa VND trước khi persist.**
6. **Không trộn adjusted / unadjusted.**
7. **Không deactivate / xóa dữ liệu chỉ vì một lần sync thiếu mã**, trừ
   khi có cơ chế snapshot đầy đủ được spec rõ.
8. **Fetch mạng ngoài transaction; upsert theo chunk trong transaction.**
9. **Queue + scheduler** cho việc nặng; job không chứa business logic.
10. **Timezone:** `Asia/Ho_Chi_Minh`. Không giả định mọi Thứ 2–6 đều là
    ngày giao dịch — dùng trading calendar từ Sprint 2.
11. **Tests:** unit + feature; phần PostgreSQL-specific phải chạy trên
    PostgreSQL (không chỉ SQLite).
12. **Không auto-trading / không khuyến nghị pháp lý giả dạng tư vấn
    chứng khoán có giấy phép** trong product copy; tín hiệu là công cụ
    hỗ trợ nghiên cứu.
13. **YAGNI giữa các sprint:** không implement trước bảng/API của sprint
    sau trừ khi Sprint hiện tại cần schema khóa sớm (đã làm với
    `stock_bars` ở Sprint 1).

### Quyết định provider

| Ưu tiên | Provider        | Ghi chú                                      |
| ------- | ---------------- | -------------------------------------------- |
| 1       | SSI FastConnect  | Ưu tiên đánh giá & tích hợp thật từ Sprint 2 |
| 2       | DNSE OpenAPI     | Adapter dự phòng / Sprint sau khi SSI ổn     |
| 3       | Demo / Fixture   | Luôn giữ để dev/test offline                 |
| —       | VNStock          | Chỉ adapter thử nghiệm riêng nếu cần; không phụ thuộc cứng |

Chưa khẳng định miễn phí / quota / đủ lịch sử cho đến khi có credentials
và tài liệu chính thức. Sprint 2 phải có **spike đánh giá** trước khi
khóa adapter production.

------------------------------------------------------------------------

## 3. Bản đồ module theo thời gian

``` text
Modules/
├── Shared/          # enums, exceptions, calendar helpers (S2+)
├── Stock/           # catalog (S1), industries
├── MarketData/      # bars, sync logs, providers (S1–S2)
├── Indicator/       # S3
├── Strategy/        # S4
├── Signal/          # S4
├── Notification/    # S5 Telegram
├── Portfolio/       # S5 watchlist / manual positions
└── Backtest/        # S6
```

Frontend modules tương ứng:

``` text
frontend/src/modules/
├── dashboard/    # S1 health → S5 tổng hợp
├── stocks/       # S2 danh mục + bars
├── screener/     # S3
├── signals/      # S4
├── portfolio/    # S5
└── backtest/     # S6
```

------------------------------------------------------------------------

## 4. Tổng quan các sprint

| Sprint | Trạng thái   | Mục tiêu one-liner                                      | Phụ thuộc      |
| ------ | ------------ | ------------------------------------------------------- | -------------- |
| 1      | **Done**     | Nền tảng Laravel/Vue, catalog demo, health, queue skeleton | —            |
| 2      | Planned      | EOD bars thật (SSI ưu tiên) + trading calendar + sync pipeline | S1        |
| 3      | Planned      | Indicator engine + API/UI screener cơ bản               | S2             |
| 4      | Planned      | Strategy scan → signals BUY/SELL/HOLD                   | S3             |
| 5      | Planned      | Telegram + watchlist/portfolio thủ công + dashboard     | S4             |
| 6      | Planned      | Backtest lịch sử + metrics (không đặt lệnh)             | S2–S4          |

**Ngoài roadmap 1–6 (backlog sau):** intraday 5m ổn định, paper trading,
broker order adapter, multi-user auth/roles, billing, mobile.

------------------------------------------------------------------------

## 5. Sprint 1 — Tham chiếu (đã xong)

**Đã có:** `backend/`, `frontend/`, `Modules\Stock` + `Modules\MarketData`
(schema), Demo catalog provider, `StockCatalogSyncService`,
`SyncStockCatalogJob`, scheduler skeleton, `GET /api/v1/health`, tests
PostgreSQL, `docs/SPRINT_1_REPORT.md`.

**Không làm lại** trừ khi report ghi technical debt cần sửa trước Sprint 2.

------------------------------------------------------------------------

## 6. Sprint 2 — Market Data EOD + Trading Calendar

### 6.1. Mục tiêu

Đưa được dữ liệu nến ngày (`timeframe=1d`) vào `stock_bars` từ provider
thật (SSI ưu tiên), có calendar ngày giao dịch VN, sync có log/queue,
API đọc bars cho frontend.

### 6.2. IN scope

- Spike tài liệu + credentials SSI (và ghi chú DNSE nếu đã có).
- `Modules\MarketData\Providers\SSI\*` triển khai
  `MarketDataProviderInterface` (và catalog provider nếu SSI có API
  danh mục).
- Giữ Demo provider cho test offline.
- Trading calendar: bảng `trading_days` hoặc `market_calendars` + service
  `isTradingDay(date)` / `previousTradingDay` / `nextTradingDay`.
  Seed năm hiện tại + năm tới (HOSE); nguồn: công bố HOSE/UBCK hoặc
  file seed thủ công đã kiểm chứng — **không hardcode “Thứ 2–6”**.
- Services:
  - `DailyBarSyncService` (1 symbol hoặc batch symbols).
  - `StockCatalogSyncService` gắn provider thật nếu có.
- Jobs: `SyncDailyBarsJob`, optional `SyncDailyBarsForUniverseJob`.
- Scheduler EOD: sau giờ đóng cửa + buffer provider (ví dụ 18:30 VN),
  `when(isTradingDay && sync.enabled)`, `withoutOverlapping`.
- Validate bar qua `MarketBarData` trước khi upsert.
- Upsert unique `(stock_id, timeframe, started_at, is_adjusted)`.
- API:
  - `GET /api/v1/stocks` (paginate, filter exchange/symbol)
  - `GET /api/v1/stocks/{symbol}`
  - `GET /api/v1/stocks/{symbol}/bars?timeframe=1d&from=&to=`
- Frontend module `stocks`: danh sách mã + trang chi tiết bảng/chart
  EOD đơn giản (library chart nhẹ; nêu rõ package trước khi cài).
- Artisan: `stockpilot:sync-daily-bars {symbol?} {--from=} {--to=}`.
- Tests bắt buộc (unit + feature trên PostgreSQL).

### 6.3. OUT scope

- Intraday 5m production sync (có thể stub contract đã có từ S1).
- Indicators, signals, Telegram, portfolio, backtest.
- Index OHLCV riêng (có thể thiết kế bảng `index_bars` nhưng **chỉ tạo
  nếu Sprint 2 thực sự ingest index**; mặc định để Sprint 2.1/3).

### 6.4. Schema gợi ý thêm

`trading_days`:

- `id`, `market` (default `VN`), `trade_date` date unique theo market,
  `session` nullable, `is_holiday` bool default false,
  `note` nullable, timestamps.

`provider_credentials` **không** lưu DB plaintext. Dùng `.env`:

``` env
MARKET_DATA_PROVIDER=ssi
SSI_FC_consumer_id=
SSI_FC_consumer_secret=
# hoặc đúng tên biến theo tài liệu SSI đã xác nhận
MARKET_DATA_SYNC_ENABLED=false
MARKET_DATA_EOD_SCHEDULE=18:30
```

### 6.5. Tiêu chí hoàn thành Sprint 2

1. Spike note trong `docs/PROVIDER_SSI_NOTES.md` (endpoint dùng được,
   hạn chế, auth).
2. SSI (hoặc fallback Demo nếu chưa có key — phải nói rõ trong report)
   sync được ≥ 1 mã lịch sử EOD vào `stock_bars`.
3. Calendar phân biệt được ngày nghỉ đã seed.
4. Scheduler list đúng; không chạy ngày nghỉ khi `isTradingDay=false`.
5. API stocks/bars 200 + schema ổn định.
6. Frontend xem được danh sách + bars.
7. `php artisan test` pass trên PostgreSQL.
8. `docs/SPRINT_2_REPORT.md`.

### 6.6. Rủi ro Sprint 2

- Chưa có tài khoản SSI → tạm dùng Demo bars **có gắn nhãn demo**, không
  trình bày như dữ liệu thật trên UI.
- Rate limit → sync batch có delay/backoff.
- Adjusted vs unadjusted → cấu hình `is_adjusted` theo provider docs.

------------------------------------------------------------------------

## 7. Sprint 3 — Indicator Engine + Screener

### 7.1. Mục tiêu

Tính chỉ báo kỹ thuật từ `stock_bars` EOD, lưu kết quả (hoặc cache có
chiến lược rõ), API screener, UI lọc cơ bản.

### 7.2. IN scope

- Module `Modules\Indicator`:
  - Contract `IndicatorCalculatorInterface`
  - Implementations tối thiểu: SMA, EMA, RSI, MACD (params cấu hình được)
  - `IndicatorComputationService` nhận symbol + timeframe + window
- Schema gợi ý `indicator_values`:

  - `stock_id`, `timeframe`, `indicator` (varchar), `params_hash`,
    `started_at`/`trading_date`, `values` JSONB (decimal-safe strings),
    unique phù hợp, index `(stock_id, indicator, trading_date)`.

- Job `ComputeIndicatorsJob` sau sync EOD thành công (chain hoặc
  schedule riêng phụ thuộc sync log status=success).
- Không tính indicator nếu thiếu đủ số nến (ghi rõ warmup).
- API:
  - `GET /api/v1/stocks/{symbol}/indicators?names=sma,rsi&...`
  - `GET /api/v1/screener?exchange=HOSE&rsi_max=30&sma_cross=...`
    (bắt đầu với filter đơn giản, document query params).
- Frontend `screener` + enrich trang stock (overlay indicator trên chart
  nếu chi phí hợp lý).
- Tests: công thức SMA/RSI với fixture OHLC cố định (golden values).

### 7.3. OUT scope

- Strategy/signal generation.
- Machine learning.
- Intraday indicators production.

### 7.4. Tiêu chí hoàn thành Sprint 3

1. Tính đúng SMA/EMA/RSI/MACD trên fixture.
2. Persist/rebuild indicator sau EOD.
3. Screener API trả kết quả deterministic với dataset seed.
4. UI screener dùng được.
5. Tests pass + `docs/SPRINT_3_REPORT.md`.

------------------------------------------------------------------------

## 8. Sprint 4 — Strategy + Signals

### 8.1. Mục tiêu

Định nghĩa chiến lược theo contract, quét sau khi có indicators, sinh
tín hiệu có audit trail.

### 8.2. IN scope

- Module `Modules\Strategy`:
  - `StrategyInterface::evaluate(StockContext): StrategyVerdict`
  - Context gồm bars gần đây + indicator values cần thiết.
  - Strategies tối thiểu (ví dụ):
    1. MA Crossover (SMA ngắn/dài)
    2. RSI threshold (oversold/overbought)
  - Registry config `config/stockpilot.php` → `strategies.enabled[]`
- Module `Modules\Signal`:
  - Model `Signal`: `stock_id`, `strategy`, `side` (buy/sell/hold),
    `strength` nullable, `timeframe`, `signal_at`, `trading_date`,
    `payload` JSONB (inputs đã dùng, không secret), `status`
  - Unique chống spam: ví dụ 1 signal / strategy / symbol / trading_date
    / side (chốt trong implementation, ghi rõ).
- `StrategyScanService` + `ScanStrategiesJob` chạy sau compute indicators.
- API:
  - `GET /api/v1/signals`
  - `GET /api/v1/signals/{id}`
  - `GET /api/v1/stocks/{symbol}/signals`
- Frontend module `signals`.
- Copy UI: “tín hiệu kỹ thuật hỗ trợ nghiên cứu, không phải lời khuyên
  đầu tư”.

### 8.3. OUT scope

- Gửi Telegram (Sprint 5).
- Tối ưu multi-strategy phức tạp / portfolio optimization.
- Auto order.

### 8.4. Tiêu chí hoàn thành Sprint 4

1. Hai strategy chạy trên dữ liệu test tạo đúng side.
2. Scan idempotent trong cùng trading day.
3. API + UI signals hoạt động.
4. Pipeline EOD (sync → indicators → scan) chạy được local bằng
   `schedule:work` hoặc artisan chain.
5. Tests + `docs/SPRINT_4_REPORT.md`.

------------------------------------------------------------------------

## 9. Sprint 5 — Notifications + Portfolio/Watchlist + Dashboard

### 9.1. Mục tiêu

Đưa tín hiệu tới người dùng qua Telegram; cho phép theo dõi watchlist và
vị thế thủ công; dashboard tổng hợp.

### 9.2. IN scope

- Module `Modules\Notification`:
  - `NotifierInterface`
  - `TelegramNotifier` (Bot API)
  - Template message ngắn: symbol, side, strategy, time, link nội bộ
  - Config `.env`: `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`,
    `NOTIFICATIONS_ENABLED=false`
  - Job `SendSignalNotificationJob`; chỉ gửi khi enabled + signal mới
  - Dedup: không gửi trùng cùng signal id
- Module `Modules\Portfolio` (đơn giản, single-user local trước):
  - `watchlist_items` (stock_id unique)
  - `positions` thủ công: symbol/stock_id, qty, avg_price, opened_at,
    notes — **không** đồng bộ broker
  - API CRUD watchlist + positions
- Dashboard frontend: health, số signal hôm nay, watchlist, vài signal
  mới nhất.
- Auth tối thiểu nếu cần bảo vệ API ghi (token Sanctum single user).
  Nếu chưa muốn auth: giới hạn local-only và ghi rõ rủi ro trong report.

### 9.3. OUT scope

- Multi-tenant / nhiều user production.
- Broker sync.
- Email/SMS.

### 9.4. Tiêu chí hoàn thành Sprint 5

1. Bật flag → Telegram nhận được 1 message test + message signal thật
   (hoặc mock HTTP trong test).
2. Watchlist/positions CRUD ổn.
3. Dashboard hiển thị dữ liệu thật từ API.
4. Tắt `NOTIFICATIONS_ENABLED` thì không gửi.
5. Tests + `docs/SPRINT_5_REPORT.md`.

------------------------------------------------------------------------

## 10. Sprint 6 — Backtest (không auto-trade)

### 10.1. Mục tiêu

Chạy lại strategy trên lịch sử EOD, ra metrics cơ bản để so sánh.

### 10.2. IN scope

- Module `Modules\Backtest`:
  - `BacktestRequest` DTO: strategy, symbol(s) hoặc universe nhỏ,
    from/to, capital giả định, fee/slippage đơn giản (bps)
  - `BacktestEngine` replay theo trading calendar
  - Kết quả: trades, equity curve points, metrics
    (`total_return`, `max_drawdown`, `win_rate`, `trade_count`)
- Schema: `backtest_runs`, `backtest_trades` (hoặc JSONB result cho MVP
  nếu volume nhỏ — ưu tiên bảng nếu cần query).
- API: `POST /api/v1/backtests`, `GET /api/v1/backtests/{id}`
- Job async cho backtest dài.
- Frontend `backtest`: form + kết quả + equity chart đơn giản.
- Nhấn mạnh trên UI: mô phỏng lịch sử, không đảm bảo lợi nhuận tương lai.

### 10.3. OUT scope

- Walk-forward phức tạp, Monte Carlo, tối ưu tham số grid-search lớn.
- Auto-trading / broker execution.
- Intraday backtest.

### 10.4. Tiêu chí hoàn thành Sprint 6

1. Backtest 1 strategy / 1 symbol deterministic với fixture.
2. Metrics khớp kỳ vọng fixture.
3. API + UI chạy end-to-end local.
4. Không có code đặt lệnh broker.
5. Tests + `docs/SPRINT_6_REPORT.md`.

------------------------------------------------------------------------

## 11. Chuỗi phụ thuộc kỹ thuật (không phá thứ tự)

``` text
S1 foundation
 └─ S2 market data + calendar
     └─ S3 indicators
         └─ S4 strategies/signals
             ├─ S5 telegram + portfolio/dashboard
             └─ S6 backtest (cần S2 data + S4 strategy contracts;
                 có thể bắt đầu thiết kế song song cuối S4 nhưng
                 chỉ merge sau khi S4 ổn)
```

Không triển khai S4 trước khi S3 có RSI/MA ổn định. Không gửi Telegram
trước khi signal model ổn định.

------------------------------------------------------------------------

## 12. Checklist vận hành local (mọi sprint)

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

Queue / schedule khi cần:

``` powershell
php artisan queue:work database --queue=default --tries=3 --timeout=120
php artisan schedule:work
```

Frontend:

``` powershell
cd frontend
npm install
npm run dev
```

------------------------------------------------------------------------

## 13. Definition of Done chung mỗi sprint

- [ ] Đúng IN scope, không lấn OUT scope
- [ ] Migrations rollback được trên PostgreSQL
- [ ] Provider vẫn qua contract/DTO
- [ ] Không secret trong Git diff
- [ ] Tests bắt buộc pass trên PostgreSQL
- [ ] `docs/SPRINT_N_REPORT.md` có: đã làm, file đụng, lệnh chạy, kết quả
      test, nợ kỹ thuật, việc user tự cấu hình
- [ ] README / roadmap cập nhật trạng thái sprint nếu cần

------------------------------------------------------------------------

## 14. Backlog có chủ đích (sau Sprint 6)

1. Intraday 5m ingestion ổn định + indicator intraday.
2. Index bars riêng (`VNINDEX`, `VN30`…).
3. Paper trading ledger.
4. Broker adapter (chỉ khi có pháp lý + API đặt lệnh rõ ràng).
5. Multi-user auth, roles, audit.
6. Redis queue/cache production.
7. Observability (metrics, alert lỗi sync).

------------------------------------------------------------------------

## 15. Quyết định mặc định đã chốt trong master plan này

Để tránh phải hỏi lại mỗi sprint:

| Chủ đề              | Quyết định mặc định                          |
| ------------------- | -------------------------------------------- |
| Provider thật đầu tiên | SSI FastConnect                           |
| Provider fallback   | Demo (dev/test) → DNSE sau khi SSI ổn        |
| Tần suất chính S2–6 | EOD `1d`                                     |
| Intraday            | Sau Sprint 6                                 |
| Calendar            | Bảng `trading_days`, không hardcode Mon–Fri  |
| Auto-trade          | Không trong Sprint 1–6                       |
| Auth                | Single-user / local; Sanctum nếu S5 cần bảo vệ ghi |
| Queue               | Database; Redis khi deploy thật              |
| Ngôn ngữ UI         | Tiếng Việt                                   |
| Report mỗi sprint   | `docs/SPRINT_N_REPORT.md`                    |

Nếu muốn đổi một quyết định trên: sửa bảng này trước, rồi mới code.

------------------------------------------------------------------------

## 16. Thứ tự thực thi đề xuất cho Cursor

1. Đọc section sprint hiện tại + Nguyên tắc toàn dự án.
2. Kiểm tra repo/env; không ghi đè secrets.
3. Làm theo phase nhỏ trong sprint (schema → domain → jobs → API → UI →
   tests → report).
4. Dừng nếu thiếu credentials bắt buộc; ghi vào report và dùng Demo có
   nhãn rõ.
5. Không bắt đầu sprint sau cho đến khi DoD sprint hiện tại đạt.

**Sprint tiếp theo cần thực thi:** Sprint 2.
