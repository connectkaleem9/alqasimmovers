<?php
declare(strict_types=1);
/**
 * Public review submission. Reviews are published immediately (owner's decision);
 * the admin dashboard can hide or delete any of them.
 *
 * Abuse controls: honeypot, minimum fill time, 3 reviews per visitor per 24h,
 * links rejected, lengths capped, rating allow-listed. The phone number is kept
 * private (admin only) so the owner can verify a reviewer was a real customer.
 */
require __DIR__ . '/bootstrap.php';

const REVIEW_SERVICES = ['home', 'apartment', 'villa', 'office', 'packing', 'furniture', 'storage', 'inter-emirate', 'other'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect('/reviews/');
}

$lang = (($_POST['lang'] ?? '') === 'ar') ? 'ar' : 'en';
$page = ($lang === 'ar' ? '/ar' : '') . '/reviews/';
$back = fn(string $code) => redirect($page . '?error=' . rawurlencode($code) . '#write-review');

// 1. bots
if (!empty($_POST['company-website'])) {
    redirect($page . '?thanks=1#reviews');
}
$ts = isset($_POST['ts']) ? (int) $_POST['ts'] : 0;
if ($ts > 0 && (time() * 1000 - $ts) < 3000) {
    redirect($page . '?thanks=1#reviews');
}

// 2. validate
$name = clean($_POST['name'] ?? '', 60);
$area = clean($_POST['area'] ?? '', 60);
$service = clean($_POST['service'] ?? '', 30);
$rating = (int) ($_POST['rating'] ?? 0);
$body = clean($_POST['body'] ?? '', 1000);
$phoneRaw = clean($_POST['phone'] ?? '', 20);

if ($name === '' || $body === '' || $phoneRaw === '' || $rating < 1 || $rating > 5) {
    $back('required');
}
if (!valid_uae_phone($phoneRaw)) {
    $back('phone');
}
if (mb_strlen($body) < 20) {
    $back('short');
}
if (preg_match('~(https?://|www\.|\b[a-z0-9-]+\.(com|net|org|ae|io|info|biz)\b)~iu', $body . ' ' . $name)) {
    $back('links');
}
if ($service !== '' && !in_array($service, REVIEW_SERVICES, true)) {
    $service = '';
}

// 3. store
try {
    $db = db();
    $since = gmdate('Y-m-d H:i:s', time() - 86400);
    $count = $db->prepare('SELECT COUNT(*) FROM reviews WHERE ip_hash = ? AND created_at >= ?');
    $count->execute([ip_hash(), $since]);
    if ((int) $count->fetchColumn() >= 3) {
        $back('limit');
    }

    $insert = $db->prepare(
        'INSERT INTO reviews (created_at, lang, name, area, service, rating, body, phone, ip_hash, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $insert->execute([now_utc(), $lang, $name, $area ?: null, $service ?: null, $rating, $body,
        normalise_phone($phoneRaw), ip_hash(), 'published']);
} catch (Throwable $e) {
    error_log('[review] ' . $e->getMessage());
    $back('server');
}

redirect($page . '?thanks=1#reviews');
