# Design notes

## 最終採用的方向

深色遊戲選單、薄荷綠重點色、細線框、明確的存檔編號和 NEXT STEP 區塊。首頁 hero 由 HTML／CSS 的圓環、幾何底板和 SVG 存檔圖示構成，沒有使用 AI 生成風景作為主畫面。

畫面重點是「日常的繼續」，涵蓋遊戲、閱讀、創作、學習和其他活動。介面提供日本語、繁體中文與 English，程式結構維持可直接閱讀的 HTML 與 CSS。

## 本次補強方案

- 首頁在 hero 後加入薄荷綠「繼續上次」區塊，集中呈現最近示範存檔、進度、下一步與詳細頁入口。
- 目前進度與下一步分成兩欄，窄螢幕改為上下排列；下一步使用較明顯的底色。
- 空分類以細虛線外框、存檔圖示與新增入口呈現；切回有資料的分類時恢復原卡片。
- `empty.html` 是獨立的首次使用畫面預覽。沒有卡片、數量或最近存檔，使用三個簡短步驟引導建立第一筆資料。
- 沿用既有 SVG 與配色，未加入新插畫或動畫。
- 分支 `feature/resume-and-empty-states` 保留為可比較方案，原本 `main` 尚未合併。

## 設計參考

- PlatinumGames 官方開發文章：[『NieR:Automata』のUIデザイン](https://www.platinumgames.co.jp/dev-nier-automata/article/183)。參考它以平面設計、細緻排版、克制裝飾為核心的說明。
- [Persona 3 Reload 官方網站](https://persona.atlus.com/p3r/)。作為遊戲介面的整體風格參考。

最終圖形與版面為此專案自行繪製，未將上述遊戲的官方截圖、角色或 UI 素材放入專案。

## 保留的插畫備選

兩張圖片由內建 imagegen 產生，再轉為 WebP 以降低檔案大小。原始 PNG 保留在對話產生的素材中。它們目前沒有被 HTML 或 CSS 引用。

- `assets/images/hero-night-city-alternative.webp`：第一版，動漫夜景、城市屋頂、旅行者與青色存檔水晶。
- `assets/images/hero-station.webp`：第二版，日系手繪車站、傍晚色彩、較低飽和度。

第一版 prompt：

> Use case: stylized-concept. Create a beautiful original anime background illustration for ReStart, a daily-life savepoint web app with a premium JRPG menu aesthetic. Asset only, no UI, no text, no letters, no watermark. Wide landscape 1536x1024 composition. A quiet rooftop overlooking a futuristic coastal Japanese city at dusk transitioning to night, layered distant blue buildings, glowing windows, violet peach clouds, a crescent moon. On the RIGHT third of the image: a small original anime traveler in a teal jacket seen from behind, sitting with a backpack near a softly glowing cyan diamond save crystal hovering over a small pedestal. The character is small in the frame; environment dominates. The LEFT HALF stays very dark midnight navy and atmospheric, low contrast and uncluttered, suitable for white interface text overlaid later. Mood: peaceful, hopeful, an adventure waiting to be resumed. Meticulous painted anime background, crisp silhouette details and soft light, sophisticated muted indigo, cyan, pale lavender, delicate warm sunset accents. Strong cinematic perspective, gentle drifting sparkles. Original scene only.

第二版 prompt：

> Use case: stylized-concept. Asset type: wide website hero backdrop, an original illustration for a Japanese daily savepoint app. Make a restrained authentic hand-painted 2D anime background, like a quiet establishing shot from a 1990s slice-of-life animated film. Flat matte gouache fills, visible pencil line imperfections, simple controlled architecture and a limited palette. Not photorealistic, not 3D, no elaborate shiny fantasy scene. Wide landscape 1536x1024. Scene: a modest empty suburban Japanese train platform at blue hour, seen in a clear side-on composition. On the right half, one simple bench, a small weathered blue roof over the bench, a vending machine and a quiet railway signal, overhead wires, with a calm peach and lavender sky over low hills and a few dark rooftops in the distance. No people, no train. In the left half, a broad unbroken deep blue silhouette of trees and distant hills gives quiet negative space for interface text added later; keep this area substantially darker. Mood: gentle pause between adventures, nostalgic and calm. Refined navy, muted teal, dusty lilac, warm cream, restrained coral sunset. Strong simple silhouettes, slightly grainy printed-paper texture. Understated and intentionally drawn, with modest detail. No floating crystals, no bloom, no volumetric lighting, no sparkles, no lens effects, no oversized moon, no complex fantasy city. No words, no letters, no logos, no watermark, no UI.
