# ReStart PHP Local Saves Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking. User selected native execution with independent final review.

**Goal:** Deliver a working local PHP/MySQL ReStart with persistent CRUD and three languages.
**Architecture:** One index.php controller, focused domain/database/request helpers and shared PHP views; PDO MySQL stores activities. Original static files remain reference material; application navigation only uses index.php.
**Tech Stack:** PHP 8.2+, PDO MySQL, mbstring, MySQL 8+/MariaDB 10.4+, HTML/CSS, PowerShell local launcher. No product JavaScript or external PHP dependencies.
**Spec:** docs/superpowers/specs/2026-10-25-php-local-saves-design.md

## Global Constraints
- PHP＋MySQL 相容資料庫；產品不使用 JavaScript，不自動保存草稿。
- 單人、本機使用，無帳號與登入，不發布到外網。
- 新增、查看、修改、刪除；每個活動只保留最新進度，無歷程或回收桶。
- 進行中、暫停中、已完成全部由使用者手動設定，不因閒置時間自動變更。
- 繼續上次：只取進行中活動，依更新時間由新到舊，時間相同以編號由大到小決定。
- title 100, progress 2000, next_step 2000, memo 4000 Unicode characters; categories game/reading/creative/study/other; statuses active/paused/completed.
- UTC storage, Asia/Tokyo default display; three languages zh-Hant/ja/en; primary UI default zh-Hant.
- Same repository, branch feature/php-local-saves; base db63131, keep main/history/static assets. No push or merge without further instruction.
- Runtime credentials/data/logs/backups ignored by Git; dedicated loopback-only MySQL port 33079 and PHP port 8088.

## Review Focus
- Malformed arrays, blank Unicode text, emoji length boundaries: validation tests in Task 1.
- Old edit/delete forms or repeated POST: HTTP tests in Task 2; preserve data and prevent duplicate creation.
- Long English text and narrow/short windows: Chrome checks in Task 3.
- Database outage after filling form: Task 2 controlled failure test, retain fields without false success.
- Repeated local startup and backup restore: Task 4 fresh test schema and restart check; never touch unrelated databases.

## File structure
- app/domain.php: normalization/validation, enum constants.
- app/database.php: PDO connection and SaveRepository; no HTML.
- app/web.php: escaping, URLs, session/form tokens, translations.
- app/translations.php: parallel locale dictionaries.
- index.php: allowlisted GET routes and POST operations, error/status responses.
- views/layout.php, list.php, form.php, detail.php, delete.php, help.php, error.php, card.php: semantic reusable HTML.
- assets/css/app.css: app-specific readable refinements after existing style.css.
- database/schema.sql, demo.sql: schema and optional independent demonstration import.
- config.example.php: safe example; config.local.php ignored.
- scripts/router.php: local dev server denies internal/config/data paths.
- scripts/local.ps1, provision.php, backup.php: start/stop, isolated DB initialization and backup tools.
- tests/domain.php, repository.php, http.cjs, browser.cjs: no third-party PHP test framework; JS only development verification.
- docs/LOCAL_SETUP.md, README.md, docs/VALIDATION.md: run/backup/validation documentation.

### Task 1: Database, validation, and storage
**Interfaces:** validate_save(array $input): array{values:array,errors:array}; connect_database(array $config): PDO; SaveRepository(PDO) methods create(array):int, find(int):?array, update(int,int,array):bool, delete(int,int):bool, listing(string,string,int):array, counts():array, latestActive():?array.
- [ ] Write tests/domain.php with real assertions for Unicode title boundaries, empty/array input, invalid category/status, optional blank fields and stored plain text. Run and observe missing implementation failure.
- [ ] Implement domain validation. Run `php tests/domain.php`; expect all assertions PASS.
- [ ] Write tests/repository.php targeting dedicated restart_test schema, checking CRUD, 12-per-page filtering, active-only recency/ties, revision conflict, Unicode persistence. Run before implementation; expect missing repository failure.
- [ ] Implement schema, PDO connection, repository prepared statements and microsecond UTC timestamps. Setup dedicated database for actual MySQL tests.
- [ ] Run domain + repository suite; expect PASS, then commit `feat: add persistent save storage and validation`.

### Task 2: PHP routes and reliable form flow
**Consumes:** Task 1 API.
**Produces:** index.php?view=list|new|detail|edit|delete|help&lang=zh-Hant|ja|en; optional id/status/category/page; POST action=create|update|status|delete, token and submission ID, version for existing saves.
- [ ] Write tests/http.cjs actual HTTP tests: new/save redirect, 422 validation and retained values, CSRF403, GET cannot mutate, duplicate POST same ID, stale update/delete409, missing404, escaped HTML, language preservation, invalid arrays, DB unavailable retains submitted fields. Run initial RED.
- [ ] Implement shared web helpers, localized messages, controller and minimum views. GET unknown route404, wrong method405, connection failure503, success303. Form tokens bind operation+record and completed redirects for retry.
- [ ] Run HTTP suite and Task 1 suite; expect PASS. Commit `feat: connect save forms and localized PHP routes`.

### Task 3: Integrate existing design and responsive states
**Consumes:** controller variables and t(string,array):string, e(mixed):string, url(array):string, icon(string):string, form_fields(string,?int):string.
- [ ] Write browser checks for empty first use, create/read/edit/status/delete, filters, pagination, no-active state, three-locale links, long text and help visible at <=760px. Existing mobile-help test is RED on baseline.
- [ ] Implement full shared templates and app.css; original assets remain. Meaningful body/input text14–16px, helper/status/date>=12px; error summary linked to fields, labels and focus preserved. No scripts in product pages.
- [ ] Chrome widths1440/1024/768/390/320 and short desktop heights; inspect screenshots of home/form/detail in all locales. Run all suites and commit `feat: polish accessible three-language save interface`.

### Task 4: Local startup, optional examples, backup, documentation
**Consumes:** schema + config and completed app.
**Produces:** `powershell -ExecutionPolicy Bypass -File scripts/local.ps1 start|stop|status`, `.local/runtime.json` and ignored configuration; `php scripts/backup.php export <file>` and `restore <file> --confirm` supporting a validated JSON backup; optional demo SQL imported by explicit command.
- [ ] Verify local commands against isolated runtime and create test-first backup roundtrip checks; reject malformed backup before changing data.
- [ ] Implement secure internal-path router and Apache .htaccess deny rules; only intended entry/assets served, no directory listing. Implement idempotent launcher with process identity checks; refuse occupied port owned by another process. Initialize only absent ReStart data directory, app user limited to own schemas.
- [ ] Implement backup validation + transactional restore, before-restore automatic backup, zero destructive default commands. Test restore against restart_test only.
- [ ] Write Chinese setup/launch/backup guide, XAMPP manual alternative, config example, no-JS scope and form-language navigation behavior. Update AGENTS scope to user-approved PHP spec.
- [ ] Run PHP lint, all tests, restart persistence and git diff checks; independent whole-branch review then regression fixes and final commit.

## Execution notes
Plan approved through user's instruction to execute the approved design directly. Native execution, same checkout (user preference). Windows equivalent ledger/brief handling under ignored .superpowers directory; bash skill helper scripts are replaced by equivalent native bookkeeping.
