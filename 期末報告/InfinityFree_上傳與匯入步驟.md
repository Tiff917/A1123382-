# InfinityFree 上傳與匯入步驟

## 1. 上傳網站檔案

- 開啟 InfinityFree File Manager
- 進入 `htdocs`
- 將 `card_shop` 內所有網站檔案上傳到 `htdocs`
- 確認首頁檔案與資源都在正確位置

## 2. 建立並匯入資料庫

- 進入 `MySQL Databases`
- 開啟 `phpMyAdmin`
- 選擇你的正式資料庫
- 匯入 `card_shop_infinityfree.sql`

## 3. 匯入前注意

若 SQL 檔內含有下列語句，建議先刪除再匯入：

- `CREATE DATABASE`
- `USE card_shop`

這樣可以避免主機端因資料庫名稱不同而出錯。

## 4. 修改設定檔

請把正式部署資訊填入：

- `APP_URL=https://your-domain.example.com`
- `DB_HOST=your-db-host.example.com`
- `DB_NAME=your_database_name`
- `DB_USER=your_database_user`
- `DB_PASS=your_database_password`

## 5. 上線後檢查

- `https://your-domain.example.com/signin.php`
- `https://your-domain.example.com/register.php`
- `https://your-domain.example.com/product_list.php`

## 6. 安全提醒

- 上傳到公開 GitHub 前，請先把 `config.php` 與 `db_config.php` 內的真實帳密改成範例值。
- 正式版本請只保留在本機或私有備份中。
