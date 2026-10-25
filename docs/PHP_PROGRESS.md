# ReStart PHP 開發接續紀錄

2026-10-25：使用者第二次明確要求暫停，目前已停止開發並關閉網站、測試伺服器及專用MySQL。等使用者再次要求繼續才恢復。

## 專案與保存點
- 原專案 C:/Users/2263137/Documents/GitHub/web-app，分支 feature/php-local-saves。
- 基準 db63131；main與原靜態分支保留，未push或合併。
- dcec2e2 設計與計畫；eff8b0e 資料層；d79ab88 PHP表單路由；3ed8ac8第一次暫停點；478187f首頁與三語介面整合。
- 本次再以正常WIP Commit保存啟動／備份工具、文件與測試，不代表已完成最終驗收。

## 已完成與已取得證據
- PHP欄位驗證24斷言、實際MySQL儲存層18斷言通過。
- HTTP25斷言通過：新增、編輯、刪除、驗證保留輸入、CSRF、重複送出、版本衝突與錯誤網址。
- 首頁／卡片／繼續上次／篩選／分頁／首次使用頁已接真實資料。
- 三語、5種寬度的瀏覽器198項檢查通過；100個emoji標題、暫停排除、刪最後一筆、窄視窗help等已驗證。
- 3張實際瀏覽器截圖已檢視，位於本聊天outputs；截圖取自測試資料，不是正式資料。
- JSON備份與交易式還原7斷言通過；資料庫斷線保留輸入／內部檔案拒絕存取11項檢查通過。
- 本機launcher已實際啟動、辨識重複啟動、停止，再啟動；MySQL重啟後資料保留1項檢查通過。
- 最後停止指令明確輸出 ReStart database stopped / ReStart website stopped。

## 尚未完成
- 完整全套最後回歸、所有PHP語法檢查、較短桌機視窗／篩選分頁等補充驗證。
- 備份CLI完整使用者流程與可選示範的補充驗證。
- docs/VALIDATION.md尚未更新為最後結果，計畫勾選與ledger尚須整理。
- 最終獨立程式碼審查尚未開始；尚不能宣稱完成品。
- 保留 tests/persistence.php 測試資料的那一筆仍在 restart_test；下次讀取確認後執行cleanup。正式restart資料庫仍以空白起始，不應混入測試資料。

## 恢復步驟
1. 讀本檔、docs/superpowers/specs/2026-10-25-php-local-saves-design.md、docs/superpowers/plans/2026-10-25-php-local-saves.md和忽略的.superpowers/sdd/php-local-saves/progress.md。
2. 檢查git status，不覆蓋使用者期間的變更。
3. 使用request_permissions取得原專案與.git的讀寫、本機network。AGENTS.md與scripts/local.ps1若需要再編輯，需明確指定檔案寫入權限（一般目錄權限曾不足）。不要試圖使用被拒絕的sandbox_approval。
4. 執行 powershell -NoProfile -ExecutionPolicy Bypass -File scripts/local.ps1 start -KeepAlive，以保留工具環境的背景服務。網址8088、MySQL33079。
5. 測試伺服器用RESTART_CONFIG=.local/test-config.php，PHP8089＋scripts/router.php；斷線測試用.local/outage-config.php、PHP8091＋router。測試只操作restart_test。
6. 依序persistence read/cleanup、domain、repository、backup（清空test）、HTTP、browser、outage等適當測試；補缺口後做獨立最終審查、修正、Commit。
7. 完成後確認正式資料庫無測試資料，提供可用本機網址與啟動／備份指南，未經指示不push或合併main。

## 重要實作與環境事項
- PHP C:/php/php.exe 8.4.12；MySQL C:/Program Files/MySQL/MySQL Server 9.7/bin/mysqld.exe。
- .local/mysql已存在，不得重新初始化／刪除。config.local.php、.local/admin.php含密碼，永遠不印出或提交。
- 原HTML保留，真正入口index.php。產品無JavaScript；tests/*.cjs是開發測試。
- 字元上限依PHP Unicode碼點；原生maxlength設為兩倍以容納UTF16代理對，PHP仍限制100/2000/2000/4000。
- 沙盒跨工具呼叫無法Get-Process看到另一呼叫啟動的PID。launcher用本地web-instance標記與HTTP標頭辨識本專案；KeepAlive模式透過stop-web-request標記讓原啟動器停止自己的PHP子程序。
- stop順序已改成先MySQL正常SHUTDOWN、再PHP，避免工具job結束時強制終止資料庫；本次停止已成功。
- 自動launcher支援現有Oracle MySQL；XAMPP/MariaDB手動設定另見docs/LOCAL_SETUP.md。Apache.htaccess路線未實測。
- Node Playwright路徑：C:/Users/2263137/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules。
