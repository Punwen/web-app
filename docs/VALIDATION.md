# ReStart PHP 驗證紀錄

2026-10-25，Windows／PHP 8.4.12／MySQL 9.7.2／Chrome。本機 PHP 分支；測試資料庫固定 restart_test，正式 restart 未用於測試清除。

| 驗證 | 結果 |
|---|---|
| tests/domain.php | 24 assertions PASS |
| tests/repository.php | 18 assertions PASS，實際 MySQL |
| tests/http.cjs | 28 assertions PASS |
| tests/browser.cjs | 198 checks PASS，三語 × 1440/1024/768/390/320px |
| tests/browser-list.cjs | 30 checks PASS，三語篩選、12筆分頁、1024×600短視窗使用說明 |
| tests/backup.php | 7 assertions PASS，交易還原與不合法資料拒絕 |
| tests/cli.php | 10 assertions PASS，備份指令、確認旗標、禁止覆寫、示範匯入 |
| tests/outage.cjs | 11 checks PASS，503保留文字、內部檔案404 |
| tests/persistence.php read | 1 assertion PASS，服務重啟後保留；測試資料已cleanup |
| PHP lint | 33 PHP files PASS |
| local.ps1 start/status | 已啟動服務可重複start，回報already running；網站與資料庫均可用 |
| git diff --check | PASS |

先前已實測 stop 正常關閉專用資料庫與網站後再次 start；沒有刪除資料目錄。三張瀏覽器截圖已檢視，使用的是測試資料。

執行完整測試時先啟動專用資料庫、8089測試網站及8091斷線網站。依序跑domain、repository、backup、HTTP、browser、CLI、browser-list、outage，避免並行清空同一測試資料庫。Node測試依賴Playwright和Chrome，產品本身不使用JavaScript。

限制：Apache／XAMPP MariaDB 路線提供設定說明，但本次未在該環境執行。獨立審查已完成，兩項重要問題已修正，並完成整套回歸。

## 獨立審查及修正
- 修正密碼設定寫入失敗仍變更資料庫帳號：完整寫入暫存檔並完成更名後才改帳號，設定先保存再建立app user。另驗證資料目錄；處理已保存密碼但尚未ALTER時的重試。
- 修正還原前備份的部分寫入：比較完整位元組長度，失敗即中止，不替換資料。
- tests/io-failures.php backup／admin.php／config.local.php：原實作各自失敗，修正後共5斷言通過。recover模式另1斷言通過。故障注入隔離於測試程序，帳號操作使用假PDO，備份使用restart_test。
- 補上刪除確認後其他分頁已更新的HTTP測試：回409，保留新內容。
- 審查者未獨立執行測試；上列結果由主實作者實際執行。審查者Git上下文失敗，歷史保留由主實作者確認：原HTML、原style.css及原圖片相對db63131無差異。
- 無尚未處理的重要問題，無延後的小項；Apache／XAMPP實機驗證仍列為環境限制。

## 實作決策紀錄
1. 依使用者要求在原checkout使用新分支，不建立新worktree；切換版本須先保存變更。
2. Windows使用原生PowerShell紀錄流程，取代技能bash輔助腳本；以相同測試與Git證據核對，沒有依賴bash自動紀錄。
3. HTML maxlength為Unicode限制的兩倍，容納emoji UTF16代理對；PHP仍依碼點限制。代價是純ASCII超限會送出後才收到錯誤。
4. 舊刪除表單雖有版本檢查，仍補上原計畫要求的HTTP回歸，避免未驗證其實際路由行為。
5. Apache未實測維持明確限制；本次交付以已驗證的PHP本機伺服器為準。
6. 審查不重跑測試與Git歷史檢查，由主實作者提供實測與差異證據；不宣稱雙重獨立驗證。
