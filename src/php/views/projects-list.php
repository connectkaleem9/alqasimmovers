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

echo '<div class="project-list">';
foreach ($__projects as $__p) {
    $__title = ($__lang === 'ar' && !empty($__p['title_ar'])) ? $__p['title_ar'] : $__p['title_en'];
    $__desc = ($__lang === 'ar' && !empty($__p['description_ar'])) ? $__p['description_ar'] : $__p['description_en'];
    $__mediaStmt->execute([(int) $__p['id']]);
    $__media = $__mediaStmt->fetchAll();

    echo '<article class="project">';
    echo '<header class="project__head"><h2 class="project__title">' . e($__title) . '</h2>';
    $__meta = array_filter([$__p['location'] ?? '', $__p['property_type'] ?? '']);
    if ($__meta) {
        echo '<p class="project__meta">' . e(implode(' · ', $__meta)) . '</p>';
    }
    echo '</header>';
    if (!empty($__desc)) {
        echo '<p class="project__desc">' . nl2br(e($__desc)) . '</p>';
    }
    if ($__media) {
        echo '<div class="project__gallery">';
        foreach ($__media as $__m) {
            $__src = '/' . ltrim((string) $__m['path'], '/');
            if ($__m['kind'] === 'video') {
                echo '<figure class="project__item project__item--video">'
                    . '<video controls preload="metadata" playsinline src="' . e($__src) . '"></video></figure>';
            } else {
                $__thumb = !empty($__m['thumb']) ? '/' . ltrim((string) $__m['thumb'], '/') : $__src;
                $__w = (int) ($__m['width'] ?: 1200);
                $__h = (int) ($__m['height'] ?: 800);
                echo '<figure class="project__item">'
                    . '<a href="' . e($__src) . '" target="_blank" rel="noopener">'
                    . '<img src="' . e($__thumb) . '" alt="' . e($__title) . '" width="' . $__w . '" height="' . $__h . '" loading="lazy" decoding="async">'
                    . '</a></figure>';
            }
        }
        echo '</div>';
    }
    echo '</article>';
}
echo '</div>';
