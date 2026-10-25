# ReStart PHP 開發暫停紀錄

2026-10-25：使用者因五小時用量接近上限，明確要求暫停。未收到「繼續」之前不進行新開發。

## 已保存與驗證
- 同一 Repository：C:/Users/2263137/Documents/GitHub/web-app
- 分支 feature/php-local-saves；原 main 和 origin/feature/resume-and-empty-states 保留。
- 基準 db63131；dcec2e2 保存已批准的設計與計畫。
- eff8b0e：PHP 驗證、MySQL 資料表與儲存層；24 個欄位驗證斷言、18 個實際 MySQL 斷言通過。
- d79ab88：PHP 路由、表單、三語訊息與初步樣板；25 個 HTTP 斷言通過。
- 已測試新增、編輯、刪除確認、CSRF、重複送出、舊分頁衝突及基本輸入保留。
- 獨立 MySQL 資料存在忽略目錄 .local/mysql，配置在 config.local.php；沒有修改原課堂資料庫，沒有把密碼提交 Git。

## 目前還不能視為完成品
- views/list.php 仍是暫時首頁，尚未整合卡片、繼續上次、篩選、分頁與首次使用狀態。
- assets/css/app.css 尚空白，HTML/CSS 完整整合與字級／窄視窗改善未完成。
- tests/browser.cjs 已寫但在「空資料庫應顯示 welcome」處預期失敗，尚未完成瀏覽器驗證。
- scripts/provision.php 是已運行的本機資料庫設定草稿；自動啟動／停止、備份／還原、可選示範資料、Apache／本機 router 存取限制及使用指南尚未完成。
- 資料庫斷線時保留表單的整合測試尚未完成。
- 最終獨立程式碼審查尚未開始。未 push、未合併 main。

## 恢復工作時
1. 重新取得原專案與 .git 的讀寫權限，以及本機網路連線權限；先 git status 確認使用者期間的變更。
2. 讀 docs/superpowers/specs/2026-10-25-php-local-saves-design.md、docs/superpowers/plans/2026-10-25-php-local-saves.md，以及忽略目錄 .superpowers/sdd/php-local-saves/progress.md。
3. 重啟既有專用 MySQL（127.0.0.1:33079，資料目錄 .local/mysql），不要重新初始化或刪除資料；先確認 config.local.php/.local/admin.php 能連線，不能輸出密碼。
4. 測試 PHP 使用 RESTART_CONFIG 指向 .local/test-config.php、port8089；正式網站預定8088。測試只使用 restart_test，正式 restart 保持使用者資料。
5. 從 Task3 的首頁/卡片/CSS 繼續，補足 Task2 斷線測試，再完成 Task4 和獨立審查。不要重做已提交資料層與路由。
6. 檢查 HTML maxlength 使用 UTF-16 單位與 PHP mb_strlen Unicode 字元計數差異，避免100個emoji被前端提前擋住；尚未調整。

## 已知環境事項
- PHP C:/php/php.exe 8.4.12，PDO MySQL與mbstring可用。
- MySQL C:/Program Files/MySQL/MySQL Server 9.7/bin/mysqld.exe。
- 首次連線被沙盒 socket 權限阻擋，授予 network 後需重新啟動 MySQL 程序才可連線。
- 當輪檔案權限可能於新輪失效；使用 request_permissions，不要另外複製專案或改用新資料夾。
- Node 可用，Playwright 位於 C:/Users/2263137/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules，產品頁面不使用 JavaScript。
