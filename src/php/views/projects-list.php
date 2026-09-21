<?php
declare(strict_types=1);
/**
 * Renders published projects with their photos and videos.
 * Included by /projects/ and /ar/projects/. Expects $LANG.
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$__lang = (isset($LANG) && $LANG === 'ar') ? 'ar' : 'en';
$__t = (require __DIR__ . '/i18n.php')[$__lang];

try {
    $__db = db();
    $__projects = $__db->query(
        "SELECT id, title_en, title_ar, location, property_type, description_en, description_ar
         FROM projects WHERE status = 'published' ORDER BY sort ASC, created_at DESC"
    )->fetchAll();
    $__mediaStmt = $__db->prepare('SELECT kind, path, thumb, width, height FROM project_media WHERE project_id = ? ORDER BY sort ASC, id ASC');
} catch (Throwable $__e) {
    error_log('[projects-list] ' . $__e->getMessage());
    echo '<p class="empty-note">' . e($__t['projects_unavailable']) . '</p>';
    return;
}

if (!$__projects) {
    echo '<p class="empty-note">' . e($__t['projects_empty']) . '</p>';
    return;
}

echo '<div class="project-grid">';
foreach ($__projects as $__p) {
    $__title = ($__lang === 'ar' && !empty($__p['title_ar'])) ? $__p['title_ar'] : $__p['title_en'];
    $__desc = ($__lang === 'ar' && !empty($__p['description_ar'])) ? $__p['description_ar'] : $__p['description_en'];
    $__mediaStmt->execute([(int) $__p['id']]);
    // a row whose file is gone (deleted on the server) must not render a broken image
    $__root = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $__media = array_values(array_filter(
        $__mediaStmt->fetchAll(),
        fn($m) => $__root === '' || is_file($__root . '/' . ltrim((string) $m['path'], '/'))
    ));

    // the card's cover is the first photo; a project with only video shows the video
    $__photos = array_values(array_filter($__media, fn($m) => $m['kind'] !== 'video'));
    $__videos = array_values(array_filter($__media, fn($m) => $m['kind'] === 'video'));
    $__cover = $__photos[0] ?? null;

    echo '<article class="project-card">';
    if ($__cover) {
        $__full = '/' . ltrim((string) $__cover['path'], '/');
        $__thumb = !empty($__cover['thumb']) ? '/' . ltrim((string) $__cover['thumb'], '/') : $__full;
        echo '<a class="project-card__media" href="' . e($__full) . '" target="_blank" rel="noopener">'
            . '<img src="' . e($__thumb) . '" alt="' . e($__title) . '" width="' . (int) ($__cover['width'] ?: 1200)
            . '" height="' . (int) ($__cover['height'] ?: 800) . '" loading="lazy" decoding="async"></a>';
    } elseif ($__videos) {
        echo '<div class="project-card__media"><video controls preload="metadata" playsinline src="'
            . e('/' . ltrim((string) $__videos[0]['path'], '/')) . '"></video></div>';
    }

    echo '<div class="project-card__body">';
    echo '<h2 class="project-card__title">' . e($__title) . '</h2>';
    $__meta = array_filter([$__p['location'] ?? '', $__p['property_type'] ?? '']);
    if ($__meta) {
        echo '<p class="project-card__meta">' . e(implode(' · ', $__meta)) . '</p>';
    }
    if (!empty($__desc)) {
        echo '<p class="project-card__desc">' . nl2br(e($__desc)) . '</p>';
    }

    // the rest of the photos and videos, small, under the text
    $__more = array_merge(array_slice($__photos, 1), $__videos ? ($__cover ? $__videos : array_slice($__videos, 1)) : []);
    if ($__more) {
        echo '<ul class="project-card__more">';
        foreach (array_slice($__more, 0, 4) as $__m) {
            $__src = '/' . ltrim((string) $__m['path'], '/');
            if ($__m['kind'] === 'video') {
                echo '<li><video preload="metadata" muted playsinline controls src="' . e($__src) . '"></video></li>';
            } else {
                $__t2 = !empty($__m['thumb']) ? '/' . ltrim((string) $__m['thumb'], '/') : $__src;
                echo '<li><a href="' . e($__src) . '" target="_blank" rel="noopener">'
                    . '<img src="' . e($__t2) . '" alt="' . e($__title) . '" width="300" height="225" loading="lazy" decoding="async"></a></li>';
            }
        }
        if (count($__more) > 4) {
            echo '<li class="project-card__count">+' . (count($__more) - 4) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div></article>';
}
echo '</div>';
