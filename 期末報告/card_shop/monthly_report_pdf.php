<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_once __DIR__ . '/report_helpers.php';
require_login(['seller', 'admin']);

$month = preg_match('/^\d{4}-\d{2}$/', (string) ($_GET['month'] ?? '')) ? (string) $_GET['month'] : date('Y-m');
$seller = fetch_user_by_id((int) current_user()['id']);
$summary = monthly_sales_summary((int) current_user()['id'], $month);
$orders = seller_monthly_orders((int) current_user()['id'], $month);

$safeMonth = str_replace('-', '', $month);
$jpgPath = REPORT_DIR . '/seller_' . current_user()['id'] . '_' . $safeMonth . '.jpg';
$pdfPath = REPORT_DIR . '/seller_' . current_user()['id'] . '_' . $safeMonth . '.pdf';

generate_sales_report_jpeg($seller, $month, $summary, $orders, $jpgPath);
build_pdf_from_jpeg($jpgPath, $pdfPath);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="milky-card-sales-' . $safeMonth . '.pdf"');
readfile($pdfPath);
exit;
