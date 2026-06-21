# 期末報告｜T's cashop

這個 GitHub Repository 主要收錄我的期末專案 `T's cashop`。

`T's cashop` 是一個韓系小卡交易平台 Web App，使用 `PHP + MySQL + XAMPP + PWA` 製作，包含會員系統、商品管理、購物車、結帳、評價、賣家月報與 PDF 匯出功能。

## 專案入口

- 期末專案資料夾：`期末報告/`
- 網站程式：`期末報告/card_shop/`
- 資料庫 SQL：`期末報告/card_shop.sql`
- InfinityFree 部署用 SQL：`期末報告/card_shop_infinityfree.sql`
- APP 完整介紹整理：`期末報告/APP完整介紹總整理.md`
- PPT 每頁標題內文版：`期末報告/PPT每頁標題內文版.md`

## 公開網站

- 網址：[https://cardshop.free.nf/signin.php](https://cardshop.free.nf/signin.php)

## 專案特色

- 會員系統：註冊、登入、登出、Remember Me
- 商品系統：首頁、商品列表、商品詳情、SOLD OUT 狀態
- 購物流程：加入購物車、刪除商品、結帳成功頁
- 賣家功能：商品上架、多圖上傳、浮水印、月報與 PDF 匯出
- 評價系統：買家可針對訂單留下星級與文字評價
- 通知功能：商品售出後寄送買家與賣家通知信
- PWA：可加入手機主畫面，介面採底部 Tab 風格

## 技術使用

- Frontend：HTML、CSS、JavaScript
- Backend：PHP 8
- Database：MySQL / MariaDB
- Image Processing：GD
- Mail：PHPMailer
- Deployment：XAMPP、InfinityFree

## 測試帳號

- 管理員：`admin / admin123`
- 賣家：`seller01 / seller123`
- 買家：`buyer01 / buyer123`

## 本機執行方式

1. 將 `期末報告/card_shop` 放入 XAMPP 的 `htdocs`
2. 開啟 Apache 與 MySQL
3. 匯入 `期末報告/card_shop.sql`
4. 或開啟 `http://localhost/card_shop/migrate_v2.php`
5. 進入 `http://localhost/card_shop/signin.php`

## 提醒

此公開版本已將正式部署帳密改為範例值。  
若要重新部署，請先修改：

- `期末報告/card_shop/config.php`
- `期末報告/card_shop/db_config.php`

並填入自己的資料庫與 SMTP 設定。
