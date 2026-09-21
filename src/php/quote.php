<?php
declare(strict_types=1);
/**
 * Al Qasim Movers — quote/contact form handler (Hostinger shared hosting, PHP + MySQL).
 *
 * Flow: POST → validate → store in MySQL (prepared statements) → email the owner →
 *       redirect to the thank-you page in the visitor's language.
 *
 * Security notes:
 *  - No credentials live in this file or anywhere in the repository. They come from a
 *    config file kept ABOVE public_html (see config.sample.php).
 *  - Every query is a prepared statement; no user value is ever concatenated into SQL.
 *  - Nothing the visitor typed is echoed back, so there is no reflected-XSS surface.
 *  - Email headers are built from constants only; the visitor cannot inject headers.
 *  - IP addresses are hashed with a salt before storage.
 */

require __DIR__ . '/lib.php';

/* --------------------------------------------------------------------- run */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect('/');
}

$lang = (isset($_POST['lang']) && $_POST['lang'] === 'ar') ? 'ar' : 'en';
$prefix = $lang === 'ar' ? '/ar' : '';
$backUrl = $prefix . '/get-a-quote/';
$thanksUrl = $prefix . '/get-a-quote/thank-you/';

// 1. Honeypot — a real visitor never sees this field, so any value means a bot.
if (!empty($_POST['company-website'])) {
    redirect($thanksUrl);   // silently accept, store nothing
}

$config = [];
try {
    $config = load_config();
} catch (Throwable $e) {
    error_log('[quote] config: ' . $e->getMessage());
    bail($backUrl, 'server');
}

// 2. Speed check — the timestamp is set by JavaScript on page load.
$minSeconds = (int) ($config['limits']['min_seconds'] ?? 3);
$ts = isset($_POST['ts']) ? (int) $_POST['ts'] : 0;
if ($ts > 0) {
    $elapsed = (time() * 1000 - $ts) / 1000;
    if ($elapsed < $minSeconds) {
        redirect($thanksUrl);   // bot speed: accept quietly, store nothing
    }
}

// 3. Validate
$name = clean($_POST['name'] ?? '', FIELD_LIMITS['name']);
$phoneRaw = clean($_POST['phone'] ?? '', FIELD_LIMITS['phone']);
if ($name === '' || $phoneRaw === '') {
    bail($backUrl, 'required');
}
if (!valid_uae_phone($phoneRaw)) {
    bail($backUrl, 'phone');
}
$phone = normalise_phone($phoneRaw);

$email = clean($_POST['email'] ?? '', FIELD_LIMITS['email']);
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $email = '';   // ignore a malformed optional field rather than rejecting the lead
}

$movingFrom = clean($_POST['moving_from'] ?? '', FIELD_LIMITS['moving_from']);
$movingTo = clean($_POST['moving_to'] ?? '', FIELD_LIMITS['moving_to']);
$message = clean($_POST['message'] ?? '', FIELD_LIMITS['message']);

$propertyType = clean($_POST['property_type'] ?? '', FIELD_LIMITS['property_type']);
if ($propertyType !== '' && !in_array($propertyType, ALLOWED_PROPERTY, true)) {
    $propertyType = '';
}

$movingDate = null;
$dateIn = clean($_POST['moving_date'] ?? '', 10);
if ($dateIn !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateIn)) {
    [$y, $m, $d] = array_map('intval', explode('-', $dateIn));
    if (checkdate($m, $d, $y)) {
        $movingDate = $dateIn;
    }
}

$services = [];
foreach ((array) ($_POST['services'] ?? []) as $service) {
    if (is_string($service) && in_array($service, ALLOWED_SERVICES, true)) {
        $services[] = $service;
    }
}
$servicesText = implode(',', array_unique($services));

$sourcePage = clean($_POST['source_page'] ?? '', 191);
$userAgent = clean($_SERVER['HTTP_USER_AGENT'] ?? '', 255);
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$ipHash = hash('sha256', ($config['ip_salt'] ?? '') . $ip);

// 4. Store
try {
    $db = $config['db'];
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset'] ?? 'utf8mb4');
    $pdo = new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Rate limit per hashed IP
    $limit = (int) ($config['limits']['per_ip_per_10min'] ?? 5);
    $recent = $pdo->prepare('SELECT COUNT(*) FROM leads WHERE ip_hash = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)');
    $recent->execute([$ipHash]);
    if ((int) $recent->fetchColumn() >= $limit) {
        redirect($thanksUrl);   // do not tell a flooder anything useful
    }

    $stmt = $pdo->prepare(
        'INSERT INTO leads (lang, source_page, name, phone, email, moving_from, moving_to,
                            property_type, moving_date, services, message, ip_hash, user_agent)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $lang, $sourcePage, $name, $phone, $email ?: null, $movingFrom ?: null, $movingTo ?: null,
        $propertyType ?: null, $movingDate, $servicesText, $message ?: null, $ipHash, $userAgent,
    ]);
    $leadId = (int) $pdo->lastInsertId();
} catch (Throwable $e) {
    error_log('[quote] db: ' . $e->getMessage());
    $leadId = 0;   // keep going: an email is better than losing the lead entirely
}

// 5. Email the owner
if (!empty($config['mail']['enabled'])) {
    $lines = [
        'New quote request from the website',
        '',
        'Lead ID:   ' . ($leadId ?: 'not stored (database error — check the log)'),
        'Language:  ' . ($lang === 'ar' ? 'Arabic' : 'English'),
        'Name:      ' . $name,
        'Phone:     ' . $phone,
        'WhatsApp:  https://wa.me/' . ltrim(str_replace('+', '', $phone), '0'),
        'Email:     ' . ($email !== '' ? $email : '-'),
        'From:      ' . ($movingFrom !== '' ? $movingFrom : '-'),
        'To:        ' . ($movingTo !== '' ? $movingTo : '-'),
        'Property:  ' . ($propertyType !== '' ? $propertyType : '-'),
        'Date:      ' . ($movingDate ?? '-'),
        'Services:  ' . ($servicesText !== '' ? $servicesText : '-'),
        'Page:      ' . ($sourcePage !== '' ? $sourcePage : '-'),
        '',
        'Message:',
        $message !== '' ? $message : '-',
    ];

    // Headers use configured constants only — no visitor input can reach them.
    $from = (string) ($config['mail']['from'] ?? '');
    $headers = 'From: Al Qasim Movers Website <' . $from . ">\r\n"
        . 'Content-Type: text/plain; charset=UTF-8' . "\r\n"
        . 'X-Mailer: alqasimmovers.com';

    @mail(
        (string) $config['mail']['to'],
        (string) ($config['mail']['subject'] ?? 'New moving quote request'),
        implode("\n", $lines),
        $headers,
        '-f' . $from
    );
}

redirect($thanksUrl);
