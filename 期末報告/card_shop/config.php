<?php
declare(strict_types=1);

const DB_HOST = 'your-db-host.example.com';
const DB_PORT = '3306';
const DB_NAME = 'your_database_name';
const DB_USER = 'your_database_user';
const DB_PASS = 'your_database_password';

const APP_NAME = "T's cashop";
const APP_URL = 'https://cardshop.free.nf';
const REMEMBER_COOKIE = 'card_shop_remember';
const REMEMBER_DAYS = 7;
const MAX_UPLOAD_IMAGES = 5;
const UPLOAD_DIR = __DIR__ . '/uploads';
const UPLOAD_URL = 'uploads';
const REPORT_DIR = __DIR__ . '/uploads/reports';
const REPORT_URL = 'uploads/reports';
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 465;
const SMTP_USERNAME = 'your-email@example.com';
const SMTP_PASSWORD = 'your-app-password';
const SMTP_ENCRYPTION = 'ssl';
const MAIL_FROM_ADDRESS = 'your-email@example.com';
const MAIL_FROM_NAME = APP_NAME;

date_default_timezone_set('Asia/Taipei');
