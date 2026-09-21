<?php
declare(strict_types=1);
/**
 * Renders published reviews, newest first. Included by /reviews/ and /ar/reviews/.
 * Expects $LANG ('en' | 'ar') to be set by the including page.
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$__lang = (isset($LANG) && $LANG === 'ar') ? 'ar' : 'en';
$__t = (require __DIR__ . '/i18n.php')[$__lang];

// status message after a submission
if (isset($_GET['thanks'])) {
    echo '<p class="notice notice--ok" role="status">' . e($__t['reviews_thanks']) . '</p>';
}
$__err = (string) ($_GET['error'] ?? '');
if ($__err !== '' && isset($__t['reviews_error_' . $__err])) {
    echo '<p class="notice notice--error" role="alert">' . e($__t['reviews_error_' . $__err]) . '</p>';
}

try {
    $__rows = db()->query(
        "SELECT name, area, service, rating, body, created_at FROM reviews
         WHERE status = 'published' ORDER BY created_at DESC, id DESC LIMIT 60"
    )->fetchAll();
} catch (Throwable $__e) {
    error_log('[reviews-list] ' . $__e->getMessage());
    echo '<p class="empty-note">' . e($__t['reviews_unavailable']) . '</p>';
    return;
}

if (!$__rows) {
    echo '<p class="empty-note">' . e($__t['reviews_empty']) . '</p>';
    return;
}

$__initials = function (string $name): string {
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out !== '' ? $out : '?';
};

echo '<div class="card-grid card-grid--3 review-list">';
foreach ($__rows as $__r) {
    $__rating = max(1, min(5, (int) $__r['rating']));
    $__ts = strtotime($__r['created_at'] . ' UTC') ?: time();
    $__date = $__t['months'][(int) gmdate('n', $__ts) - 1] . ' ' . gmdate('Y', $__ts);
    $__where = array_filter([
        $__r['area'] ?? '',
        isset($__r['service'], $__t['services'][$__r['service']]) ? $__t['services'][$__r['service']] : '',
    ]);
    echo '<article class="review-card">'
        . '<div class="review-card__head">'
        . '<span class="review-card__avatar" aria-hidden="true">' . e($__initials((string) $__r['name'])) . '</span>'
        . '<div>'
        . '<p class="review-card__stars" aria-label="' . $__rating . ' / 5">'
        . str_repeat('★', $__rating) . str_repeat('☆', 5 - $__rating) . '</p>'
        . '<p class="review-card__who">' . e($__r['name']) . '</p>'
        . '<p class="review-card__where">' . e(implode(' · ', $__where)) . ($__where ? ' · ' : '') . e($__date) . '</p>'
        . '</div></div>'
        . '<blockquote class="review-card__text">' . nl2br(e($__r['body'])) . '</blockquote>'
        . '</article>';
}
echo '</div>';
