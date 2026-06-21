<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

use PHPMailer\PHPMailer\PHPMailer;

function send_order_notifications(array $order, array $buyer, array $seller, array $product): ?string
{
    $mailerPath = __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
    if (!is_file($mailerPath)) {
        return 'PHPMailer 尚未安裝，已略過寄信。';
    }

    if (SMTP_USERNAME === '' || SMTP_PASSWORD === '' || MAIL_FROM_ADDRESS === '') {
        return '尚未填入 SMTP 設定，已略過寄信。';
    }

    require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
    require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = SMTP_HOST;
        $mailer->Port = SMTP_PORT;
        $mailer->SMTPAuth = true;
        $mailer->SMTPSecure = SMTP_ENCRYPTION === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Username = SMTP_USERNAME;
        $mailer->Password = SMTP_PASSWORD;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mailer->addAddress($buyer['email'], $buyer['display_name']);
        $mailer->addAddress($seller['email'], $seller['display_name']);
        $mailer->isHTML(true);
        $mailer->Subject = 'Milky Card Shop 成交通知';
        $mailer->Body = '
            <h2>小卡成交通知</h2>
            <p>商品：' . h($product['name']) . '</p>
            <p>團體 / 成員：' . h($product['group_name'] . ' / ' . $product['member_name']) . '</p>
            <p>賣家：' . h($seller['display_name']) . '</p>
            <p>買家：' . h($buyer['display_name']) . '</p>
            <p>數量：' . (int) $order['quantity'] . '</p>
            <p>總金額：' . h(format_currency((float) $order['total_amount'])) . '</p>
            <p>訂單成立時間：' . h($order['paid_at']) . '</p>
        ';
        $mailer->send();

        db()->prepare('UPDATE orders SET notification_sent_at = NOW() WHERE id = :id')->execute([
            'id' => (int) $order['id'],
        ]);

        return null;
    } catch (Throwable $e) {
        return '寄信失敗，但訂單已完成：' . $e->getMessage();
    }
}
