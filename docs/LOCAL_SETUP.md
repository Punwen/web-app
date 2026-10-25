# ReStart 本機 PHP 版使用指南

網站使用 PHP＋MySQL，沒有產品 JavaScript或自動草稿。請使用 PHP 網址，不要雙擊舊 HTML。

## 這台電腦啟動／停止

原專案：C:\Users\2263137\Documents\GitHub\web-app。

1. 雙擊 `Start-ReStart.cmd`，看到 ready 或 already running 後開啟 <http://127.0.0.1:8088/>。
2. 完成使用後雙擊 `Stop-ReStart.cmd`。重新啟動後，已儲存資料仍會保留。

也可在專案目錄執行：

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/local.ps1 start
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/local.ps1 status
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/local.ps1 stop
```

現有 PHP：C:\php\php.exe。現有 MySQL：C:\Program Files\MySQL\MySQL Server 9.7。
工具使用獨立 `.local/mysql` 資料目錄，僅綁定127.0.0.1，網站埠8088、資料庫埠33079；不修改原課堂資料庫，不自動加入範例。若埠被其他程式占用，會提示，不會停止不相關程式。
密碼在忽略的 `config.local.php` 與 `.local/admin.php`，不印出、不提交 Git。請保留 `.local`，它包含實際資料。

## 操作規則

- 新增、編輯後按「儲存」才保存；每筆只保留最新內容，無修改歷程。
- 狀態由你手動改變，閒置不會自動暫停；首頁顯示最近更新的進行中活動。
- 刪除先確認標題，取消即可保留；沒有回收桶。
- 驗證失敗會保留這次送出的文字；另一分頁已更新時會提示衝突，避免覆蓋。
- 切換語言會換頁，請先儲存；使用者文字不自動翻譯。
- 初次使用從空白開始，每頁最多12筆。標題100、進度2000、下一步2000、備註4000個Unicode字元，中文／日文／emoji可用。

## 備份與還原

資料庫啟動後，在專案目錄執行：

```powershell
New-Item -ItemType Directory -Force backups
& C:\php\php.exe scripts/backup.php export backups\restart-2026-10-25.json
```

每次用新檔名，不會覆寫現有備份。JSON包含存檔、編號、狀態與時間，可複製到其他安全位置。Git保存程式碼，JSON才是活動資料備份。

確認備份內容後可還原：

```powershell
& C:\php\php.exe scripts/backup.php restore backups\restart-2026-10-25.json --confirm
```

還原會取代目前 ReStart 的全部存檔。工具先在 `.local/before-restore-*.json` 保存還原前資料，驗證備份與每筆內容，再以交易還原；錯誤時回復交易。
還原後請重開編輯頁，不要送出還原前舊分頁的表單。

## 可選示範資料

只在空資料庫執行：

```powershell
& C:\php\php.exe scripts/seed.php --confirm
```

加入兩筆有示範標記的活動。已有資料時拒絕，避免重複匯入。

## 學校 XAMPP／既有 MySQL 手動設定

這條路線適用 XAMPP 的 MariaDB 或既有 MySQL；不要再用自動初始化工具管理此資料庫。

1. 啟動課堂使用的資料庫（XAMPP可在Control Panel啟動Apache與MySQL）。
2. 建立獨立 `restart` 資料庫，字元集utf8mb4；選中後匯入 `database/schema.sql`。SQL不會DROP既有資料。
3. 建立僅可操作該資料庫的帳號，依實際環境設定密碼，不假設root密碼為空。
4. 複製 `config.example.php` 為 `config.local.php`，填寫實際連接埠、資料庫、帳號與密碼；該檔被Git忽略。
5. 在原專案目錄使用PHP本機伺服器：

```powershell
& C:\xampp\php\php.exe -S 127.0.0.1:8088 -t . scripts/router.php
```

保留 router.php 參數，設定、資料與測試檔案才不會被網址讀取。仍使用同一Repository，不需要為版本另複製資料夾。
若改用Apache，將Alias/VirtualHost指向原專案，啟用rewrite且允許本專案.htaccess；其中有本機存取與內部路徑限制。本次實測的是PHP本機伺服器，Apache設定需依課堂環境核對。

## 檔案與版本

入口index.php；app/處理資料與共用函式；views/共用頁面；database/提供SQL；scripts/提供本機工具。
assets/css/style.css保留原靜態風格，app.css為PHP版可讀性與排版改善。舊HTML保留參考，真正操作請開PHP網址。
config.local.php、.local/、backups/不提交Git。分支feature/php-local-saves尚未合併main。切換回舊分支前先停止服務，切回PHP版後沿用原資料。

## 開發測試

測試只能使用restart_test，不得改為正式restart；測試會清空測試資料。

```powershell
& C:\php\php.exe tests/domain.php
& C:\php\php.exe tests/repository.php
& C:\php\php.exe tests/backup.php
$env:RESTART_CONFIG = (Resolve-Path .local/test-config.php).Path
& C:\php\php.exe -S 127.0.0.1:8089 -t . scripts/router.php
```

另開終端機執行node tests/http.cjs與node tests/browser.cjs。瀏覽器測試用Playwright及Chrome，這些只供開發驗證，使用網站不需要Node。先執行backup測試清空測試資料，再跑browser測試。
tests/outage.cjs需8091測試伺服器，設定連到無效資料庫埠，用於驗證斷線保留內容與內部檔案拒絕存取。
