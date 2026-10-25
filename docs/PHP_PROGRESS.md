# ReStart PHP 開發進度

2026-10-25：本機 PHP＋MySQL 版已完成實作、回歸測試及獨立程式碼審查。兩項重要審查問題已以故障測試重現並修正。

- Repository：C:/Users/2263137/Documents/GitHub/web-app。
- 分支：feature/php-local-saves；基準db63131，main與原靜態版保留，未push或合併。
- 功能：CRUD、手動狀態、真實計數與繼續上次、三語、篩選分頁、備份還原。
- 無產品JavaScript、無自動草稿；按儲存後資料保存至MySQL。
- 完整證據及決策見docs/VALIDATION.md；啟停與備份見docs/LOCAL_SETUP.md。
- 正式restart資料庫為空白；測試只操作restart_test。保留.local/mysql與config.local.php，不得刪除或重新初始化。
- PHP本機網站http://127.0.0.1:8088/；Start-ReStart.cmd／Stop-ReStart.cmd管理專用網站與資料庫。
- Apache／XAMPP路線有指南，但本次未實機驗證。

後續若要再改功能，先檢查git status。不要因聊天工作目錄不同而另建Repository；原專案之外的權限需重新取得。不要印出密碼設定，也不要將.local或備份加入Git。
