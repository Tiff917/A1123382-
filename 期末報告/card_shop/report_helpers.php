<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function report_text($image, int $size, int $x, int $y, string $text, array $rgb, ?string $font): void
{
    $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
    if ($font && function_exists('imagettftext')) {
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
        return;
    }

    imagestring($image, 5, $x, $y - 16, $text, $color);
}

function generate_sales_report_jpeg(array $seller, string $month, array $summary, array $orders, string $targetPath): void
{
    $font = first_existing_font();
    $width = 1240;
    $rowHeight = 56;
    $height = 420 + max(1, count($orders)) * $rowHeight;
    $image = imagecreatetruecolor($width, $height);

    $bg = imagecolorallocate($image, 250, 243, 236);
    $panel = imagecolorallocate($image, 255, 249, 244);
    $accent = imagecolorallocate($image, 200, 144, 114);
    $line = imagecolorallocate($image, 230, 212, 198);

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    imagefilledrectangle($image, 60, 60, $width - 60, $height - 60, $panel);
    imagefilledrectangle($image, 60, 60, $width - 60, 145, $accent);

    report_text($image, 26, 90, 116, "T's cashop 每月銷售報表", [255, 255, 255], $font);
    report_text($image, 14, 90, 176, '賣家：' . $seller['display_name'], [85, 62, 50], $font);
    report_text($image, 14, 90, 212, '月份：' . $month, [85, 62, 50], $font);
    report_text($image, 14, 90, 248, '訂單數：' . $summary['total_orders'], [85, 62, 50], $font);
    report_text($image, 14, 380, 248, '售出張數：' . $summary['total_cards'], [85, 62, 50], $font);
    report_text($image, 14, 690, 248, '總營收：' . format_currency((float) $summary['total_revenue']), [85, 62, 50], $font);

    imageline($image, 90, 292, $width - 90, 292, $line);
    report_text($image, 12, 90, 332, '日期', [128, 104, 93], $font);
    report_text($image, 12, 220, 332, '團體 / 成員', [128, 104, 93], $font);
    report_text($image, 12, 520, 332, '商品名稱', [128, 104, 93], $font);
    report_text($image, 12, 860, 332, '買家', [128, 104, 93], $font);
    report_text($image, 12, 1020, 332, '金額', [128, 104, 93], $font);

    $y = 382;
    foreach ($orders as $order) {
        imageline($image, 90, $y - 18, $width - 90, $y - 18, $line);
        report_text($image, 11, 90, $y, substr((string) $order['created_at'], 0, 10), [85, 62, 50], $font);
        report_text($image, 11, 220, $y, trim((string) $order['group_name'] . ' / ' . (string) $order['member_name']), [85, 62, 50], $font);
        report_text($image, 11, 520, $y, (string) $order['product_name'], [85, 62, 50], $font);
        report_text($image, 11, 860, $y, (string) $order['buyer_name'], [85, 62, 50], $font);
        report_text($image, 11, 1020, $y, format_currency((float) $order['total_amount']), [85, 62, 50], $font);
        $y += $rowHeight;
    }

    imagejpeg($image, $targetPath, 92);
    imagedestroy($image);
}

function build_pdf_from_jpeg(string $jpegPath, string $pdfPath): void
{
    $jpeg = file_get_contents($jpegPath);
    if ($jpeg === false) {
        throw new RuntimeException('無法讀取報表圖片。');
    }

    [$widthPx, $heightPx] = getimagesize($jpegPath);
    $pageWidth = 595.28;
    $pageHeight = ($heightPx / $widthPx) * $pageWidth;

    $objects = [];

    $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageWidth} {$pageHeight}] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>";
    $objects[] = "<< /Type /XObject /Subtype /Image /Width {$widthPx} /Height {$heightPx} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($jpeg) . " >>\nstream\n{$jpeg}\nendstream";

    $content = "q\n{$pageWidth} 0 0 {$pageHeight} 0 0 cm\n/Im0 Do\nQ";
    $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream";

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($index + 1) . " 0 obj\n{$object}\nendobj\n";
    }

    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach ($offsets as $offset) {
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }
    $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n{$xref}\n%%EOF";

    file_put_contents($pdfPath, $pdf);
}
