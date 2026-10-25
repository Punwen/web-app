-- Optional examples only. This file is never imported automatically.
-- Import into the selected ReStart database only when you want sample saves.
INSERT INTO saves (title,category,status,progress,next_step,memo,created_at,updated_at)
VALUES
('閱讀中的書','reading','active','讀到第 120 頁。','從第 6 章開始。','這是一筆可自由刪除的示範資料。',UTC_TIMESTAMP(6),UTC_TIMESTAMP(6)),
('週末插畫','creative','paused','人物草稿已完成。','下次先整理線稿。','這是一筆可自由刪除的示範資料。',UTC_TIMESTAMP(6),UTC_TIMESTAMP(6));
