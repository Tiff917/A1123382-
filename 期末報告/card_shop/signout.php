<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

logout_user();
session_start();
set_flash('flash_success', '你已經登出。');
redirect('signin.php');
