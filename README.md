# ReStart — 日常生活的存檔點

本分支為本機 **PHP＋MySQL 存檔工具**，保留繁中／日文／英文及深色遊戲介面。產品不使用JavaScript，無自動草稿、帳號登入或修改歷程。

## 開始使用

在原專案資料夾雙擊 `Start-ReStart.cmd`，看到ready後開啟 <http://127.0.0.1:8088/>；雙擊 `Stop-ReStart.cmd` 停止。
工具使用現有PHP與Oracle MySQL，在.local/mysql獨立保存資料，不修改課堂資料庫。

完整設定、XAMPP路線、備份與還原：[本機使用指南](docs/LOCAL_SETUP.md)。

## 功能

- 新增、查看、編輯、確認後刪除活動；按儲存後保存在資料庫。
- 手動切換進行中／暫停中／已完成；首頁推薦最近更新的進行中活動。
- 自動計數、分類與狀態篩選、每頁12筆、首次使用和無結果畫面。
- 三語介面共用資料；不自動翻譯使用者內容。
- 輸入檢查失敗保留內容、防止舊分頁覆蓋新修改與重複新增。
- JSON備份與交易式還原、可選空資料庫示範匯入。

初次沒有卡片是正常的。未按儲存的內容不保留；先儲存再離開或切換語言。

## 結構

| 檔案 | 用途 |
|---|---|
| index.php | 頁面與表單入口 |
| app/domain.php、database.php | 欄位驗證與PDO資料操作 |
| app/web.php、translations.php | 網頁函式與三語文案 |
| views/ | 共用側欄、卡片、表單與頁面 |
| assets/css/ | 原風格與PHP版改善 |
| database/ | 空結構與可選示範SQL |
| scripts/ | 啟動、備份與設定 |
| tests/ | PHP、HTTP與瀏覽器驗證 |
| config.example.php | 設定範例；密碼另存忽略的config.local.php |

舊HTML保留參考；雙擊只會看見靜態版，請開PHP網址。兩張備選圖片保留。

## 版本保存

feature/php-local-saves以db63131為保存點；main及feature/resume-and-empty-states保留，尚未合併。
大改前先確認Git保存點，不擅自刪除歷史。詳見[AGENTS.md](AGENTS.md)。
Git保存程式碼；活動資料需要另行備份。
