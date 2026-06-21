# InfinityFree 部署資訊模板

這份文件請只保留模板內容，不要把真實帳密公開上傳。

## 主機資訊

- Hosting Account Username：`your_hosting_username`
- Domain：`your-domain.example.com`
- DB_HOST：`your-db-host.example.com`
- DB_NAME：`your_database_name`
- DB_USER：`your_database_user`
- DB_PASSWORD：`your_database_password`
- FTP Host：`your-ftp-host`
- FTP Username：`your-ftp-username`
- FTP Password：`your-ftp-password`

## 需要修改的檔案

- `card_shop/config.php`
  - `DB_HOST`
  - `DB_NAME`
  - `DB_USER`
  - `DB_PASS`
  - `SMTP_USERNAME`
  - `SMTP_PASSWORD`
  - `MAIL_FROM_ADDRESS`
- `card_shop/db_config.php`
  - `$host`
  - `$user`
  - `$pass`
  - `$db`

## 提醒

- 公開上傳到 GitHub 前，請確認所有真實密碼都已改成範例值。
- 建議把正式帳密只保留在本機版本，不要放進公開 repo。
