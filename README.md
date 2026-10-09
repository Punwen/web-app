# ReStart — 日常のセーブポイント

日常生活的存檔點。記下遊戲、閱讀、創作、學習做到哪裡，下一次從這裡繼續。

這一版是 **純 HTML＋CSS 的靜態介面原型**，提供繁體中文、日本語和 English 三種介面。沒有 JavaScript、PHP、資料庫、登入或真正的存檔功能。所有記錄和數量都明確作為示範資料，輸入內容不會保存。

## 在 VS Code 開啟

1. 用 GitHub Desktop 複製這個儲存庫到電腦：`File → Clone repository`。
2. 選擇 `Punwen/web-app`，指定本機資料夾，按 **Clone**。
3. 在 GitHub Desktop 選擇 `Repository → Open in Visual Studio Code`，或在 VS Code 用 `File → Open Folder` 開啟剛才的資料夾。
4. 從檔案總管雙擊 `index.html`，即可在瀏覽器查看。也可以先在 VS Code 開啟 `ReStart.code-workspace`。

想在修改 CSS 後自動更新畫面，可以使用專案建議的 **Live Server** 擴充功能，安裝後在 `index.html` 按右鍵，選 **Open with Live Server**。雙擊 HTML 的方式不需要安裝擴充功能。

## 語言切換

每個頁面右上方都有 **繁體中文 / 日本語 / English** 切換列。

- 根目錄的 HTML 是日文版，保留原有網址。
- `zh-Hant/` 是繁體中文版；可直接開啟 `zh-Hant/index.html`。
- `en/` 是英文版；可直接開啟 `en/index.html`。
- 每種語言各有 14 個頁面，共 42 個 HTML。
- 切換會開啟同一畫面的其他語言版本，站內連結繼續使用目前語言。
- 純 HTML 版本不會自動記住上次選擇的語言；請把常用語言的首頁加入書籤。
- 切換語言會換頁，不會跨語言保留試填內容或分類選擇。

## 畫面與檔案

| 檔案 | 用途 |
|---|---|
| `index.html` | 首頁、四筆示範存檔、分類選項 |
| `active.html` | 進行中的示範存檔 |
| `paused.html` | 暫停中的示範存檔 |
| `completed.html` | 已完成的示範存檔 |
| `new.html` | 新活動登錄與進度輸入畫面 |
| `save-01.html` ～ `save-04.html` | 四筆示範存檔的「つづきから」畫面 |
| `edit-01.html` ～ `edit-04.html` | 對應的存檔編輯畫面 |
| `help.html` | 使用說明與原型的功能範圍 |
| `zh-Hant/` | 14 個繁體中文頁面 |
| `en/` | 14 個英文頁面 |
| `assets/css/style.css` | 全部共通外觀、CSS 分類切換與 RWD |
| `assets/images/favicon.svg` | 原創存檔圖示 |
| `assets/images/hero-station.webp` | 保留的第二版車站插畫；預設未使用 |
| `assets/images/hero-night-city-alternative.webp` | 保留的第一版夜景插畫；預設未使用 |
| `.vscode/` | 編輯器設定與 Live Server 建議 |
| `docs/DESIGN.md` | 設計方向與參考來源 |

## 現在可以操作的部分

- 點選存檔卡片的「つづきから」，前往對應的內容頁。
- 從內容頁點選「セーブを更新する」，前往預填的編輯表單。
- 從首頁點選「新しいセーブをつくる」，前往新增表單。
- 使用側邊選單查看各狀態的示範記錄。
- 點選分類標籤，篩選畫面上已存在的卡片。
- 在表單試填文字、選擇分類與狀態。
- 在桌機、平板和手機查看不同版面。

**分類切換**使用 HTML 的 radio input 搭配 CSS `:checked` 和相鄰元素選擇器。每頁都有獨立的示範卡片；這不是資料庫搜尋。

儲存與刪除按鈕目前停用，避免把畫面展示誤當成真的保存。離開頁面後，輸入內容不會作為活動記錄被保留。

## 怎麼修改

- 修改文案／示範資料：直接編輯對應 HTML。
- 修改配色：從 `style.css` 最上方的 `:root` 開始，例如 `--bg`、`--mint`、`--lavender`。
- 修改首頁 hero：搜尋 CSS 的 `/* Hero:` 區塊，以及 `index.html` 的 `<section class="hero">`。
- 修改卡片：搜尋 `.save-card`、`.card-visual` 和 `.next-step`。
- 修改手機排版：搜尋 `@media`；目前涵蓋 1250、1000、760 和 520px 的版面切換。

目前共通側欄、hero 和卡片是直接寫在 HTML 中，方便先練習 HTML/CSS。若修改共通部分，需要同步更新各頁與三種語言；之後導入 PHP 時可以抽成共用檔案。

## 下一階段

等課堂開始 PHP／資料庫後，再接上：新增活動、儲存進度、讀取、更新、刪除與輸入檢查。每個活動先只保存最新一次記錄。

GitHub 儲存庫保存的是程式碼，不代表網站已部署。這個版本可以直接在本機瀏覽器開啟。
