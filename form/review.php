<?php
declare(strict_types=1);
/**
 * Public review submission. Reviews are published immediately (owner's decision);
 * the admin dashboard can hide or delete any of them.
 *
 * Abuse controls: honeypot, minimum fill time, a 10-second gap between submissions from the
 * same visitor (anti-flood only — there is no cap on how many reviews a customer may leave),
 * links rejected, lengths capped, rating allow-listed. The optional email address
 * is kept private (admin only) so the owner can contact a reviewer.
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
$email = clean($_POST['email'] ?? '', 120);

if ($name === '' || $body === '' || $rating < 1 || $rating > 5) {
    $back('required');
}
if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $back('email');
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
    // No limit on how many reviews one customer may leave. The only guard is a few seconds
    // between submissions, which a person never notices but a flooding script hits immediately.
    $gap = (int) (config()['limits']['review_gap_seconds'] ?? 10);
    if ($gap > 0) {
        $recent = $db->prepare('SELECT COUNT(*) FROM reviews WHERE ip_hash = ? AND created_at >= ?');
        $recent->execute([ip_hash(), gmdate('Y-m-d H:i:s', time() - $gap)]);
        if ((int) $recent->fetchColumn() > 0) {
            $back('slow');
        }
    }

    $insert = $db->prepare(
        'INSERT INTO reviews (created_at, lang, name, area, service, rating, body, email, ip_hash, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $insert->execute([now_utc(), $lang, $name, $area ?: null, $service ?: null, $rating, $body,
        $email !== '' ? mb_strtolower($email) : null, ip_hash(), 'published']);
} catch (Throwable $e) {
    error_log('[review] ' . $e->getMessage());
    $back('server');
}

redirect($page . '?thanks=1#reviews');
