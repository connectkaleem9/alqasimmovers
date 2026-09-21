<?php
declare(strict_types=1);
/**
 * Al Qasim Movers — admin dashboard (/admin/).
 *
 *  - First visit: create the admin account (needs the one-time setup key from the
 *    private config file, so only someone with server access can do it).
 *  - Login: password_hash/verify, 5 failed attempts per 15 minutes per visitor.
 *  - Projects: create / edit / delete, upload photos and videos.
 *  - Reviews: hide, show or delete customer reviews.
 *  - Leads: quote requests from the website forms.
 *
 * Every state-changing request is POST + CSRF token. Uploaded images are decoded
 * and re-encoded with GD (which strips metadata and anything hidden in the file);
 * videos are type-checked by content, never by file name. Nothing uploaded can
 * execute (see /uploads/.htaccess).
 */
require dirname(__DIR__) . '/form/bootstrap.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');

const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
const VIDEO_TYPES = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov'];
const MAX_IMAGE_BYTES = 20 * 1024 * 1024;
const MAX_VIDEO_BYTES = 500 * 1024 * 1024;

admin_session_start();

try {
    $db = db();
} catch (Throwable $e) {
    error_log('[admin] db: ' . $e->getMessage());
    page('Setup needed', '<div class="panel"><h1>Setup needed</h1><p>The website configuration file is missing or the database cannot be opened. Check <code>~/private/alqasim-config.php</code>.</p></div>');
}

/* ================================================================ helpers */

function page(string $title, string $body, bool $nav = false): never
{
    $user = $_SESSION['admin_name'] ?? '';
    $bar = $nav ? '<div class="bar"><span class="bar__brand">Admin dashboard</span><nav class="bar__nav" aria-label="Admin">'
        . link_tab('projects', 'Projects') . link_tab('reviews', 'Reviews') . link_tab('leads', 'Leads')
        . '</nav><form method="post" action="/admin/" class="bar__logout">' . csrf_field()
        . '<input type="hidden" name="action" value="logout"><span>' . e($user) . '</span>'
        . '<button class="btn btn--ghost btn--sm" type="submit">Log out</button></form></div>' : '';
    $inner = '<div class="admin-app">' . $bar . '<div class="wrap">' . flash_html() . $body . '</div></div>';
    $assets = '<link rel="stylesheet" href="/admin/admin.css?v=' . asset_v('admin.css') . '">'
        . '<script src="/admin/admin.js?v=' . asset_v('admin.js') . '" defer></script>';

    // The build renders the real site header + footer into shell.html around a marker.
    $shell = @file_get_contents(__DIR__ . '/shell.html');
    if ($shell !== false && str_contains($shell, '<!--ADMIN-CONTENT-->')) {
        $shell = preg_replace('~<title>.*?</title>~s', '<title>' . e($title) . ' · Al Qasim Movers Admin</title>', $shell, 1);
        $shell = preg_replace('~<meta name="robots"[^>]*>~', '<meta name="robots" content="noindex, nofollow">', $shell, 1);
        $shell = str_replace('</head>', $assets . '</head>', $shell);
        [$head, $foot] = explode('<!--ADMIN-CONTENT-->', $shell, 2);
        echo $head, $inner, $foot;
        exit;
    }
    // fallback if the shell is missing: the dashboard still works without the site chrome
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="robots" content="noindex, nofollow">'
        . '<title>' . e($title) . ' · Al Qasim Movers Admin</title>' . $assets . '</head><body>'
        . '<main>' . $inner . '</main></body></html>';
    exit;
}

function asset_v(string $file): string
{
    $t = @filemtime(__DIR__ . '/' . $file);
    return $t ? (string) $t : '1';
}

function link_tab(string $view, string $label): string
{
    $current = ($_GET['view'] ?? 'projects') === $view || (($_GET['view'] ?? '') === 'project' && $view === 'projects');
    return '<a href="/admin/?view=' . $view . '"' . ($current ? ' aria-current="page"' : '') . '>' . $label . '</a>';
}

function flash(string $msg, string $kind = 'ok'): void
{
    $_SESSION['flash'] = [$kind, $msg];
}

function flash_html(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    [$kind, $msg] = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<p class="flash flash--' . e($kind) . '" role="status">' . e($msg) . '</p>';
}

function go(string $query = ''): never
{
    redirect('/admin/' . ($query !== '' ? '?' . $query : ''));
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function uploads_root(): string
{
    $root = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__)), '/') . '/uploads';
    if (!is_dir($root)) {
        mkdir($root, 0755, true);
    }
    // belt and braces: nothing in uploads may ever run as code
    if (!is_file($root . '/.htaccess')) {
        file_put_contents($root . '/.htaccess', "Options -Indexes -ExecCGI\n<FilesMatch \"\\.(php[0-9]?|phtml|phar|pl|py|cgi|sh|shtml|htaccess)$\">\n  Require all denied\n</FilesMatch>\n");
    }
    return $root;
}

function too_many_failures(PDO $db): bool
{
    $q = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_hash = ? AND success = 0 AND created_at >= ?');
    $q->execute([ip_hash(), gmdate('Y-m-d H:i:s', time() - 900)]);
    return (int) $q->fetchColumn() >= 5;
}

function record_attempt(PDO $db, bool $ok): void
{
    $db->prepare('INSERT INTO login_attempts (created_at, ip_hash, success) VALUES (?, ?, ?)')
       ->execute([now_utc(), ip_hash(), $ok ? 1 : 0]);
}

/** Decode, orient, resize and re-encode an uploaded image as WebP. */
function process_image(string $tmp, string $dir, string $name): array
{
    $data = file_get_contents($tmp);
    $img = $data !== false ? @imagecreatefromstring($data) : false;
    if (!$img) {
        throw new RuntimeException('The image could not be read.');
    }
    if (function_exists('exif_read_data')) {
        $exif = @exif_read_data($tmp);
        $rotate = [3 => 180, 6 => -90, 8 => 90][(int) ($exif['Orientation'] ?? 1)] ?? 0;
        if ($rotate) {
            $img = imagerotate($img, $rotate, 0);
        }
    }
    $save = function ($src, int $max, string $file, int $quality): array {
        $w = imagesx($src);
        $h = imagesy($src);
        $scale = min(1, $max / max($w, $h));
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);
        $out = imagecreatetruecolor($nw, $nh);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagecopyresampled($out, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagewebp($out, $file, $quality);
        imagedestroy($out);
        return [$nw, $nh];
    };
    [$w, $h] = $save($img, 1800, "$dir/$name.webp", 82);
    $save($img, 700, "$dir/$name-thumb.webp", 78);
    imagedestroy($img);
    return [$w, $h];
}

/* ================================================================ POST actions */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $adminCount = (int) $db->query('SELECT COUNT(*) FROM admins')->fetchColumn();

    // ---- first-time setup (only while no admin exists)
    if ($action === 'setup' && $adminCount === 0) {
        csrf_check();
        if (too_many_failures($db)) {
            flash('Too many attempts. Please wait 15 minutes and try again.', 'error');
            go('view=setup');
        }
        $key = (string) (config()['admin']['setup_key'] ?? '');
        $user = clean($_POST['username'] ?? '', 60);
        $pass = (string) ($_POST['password'] ?? '');
        $pass2 = (string) ($_POST['password2'] ?? '');
        if ($key === '' || !hash_equals($key, (string) ($_POST['setup_key'] ?? ''))) {
            record_attempt($db, false);
            flash('The setup key is not correct.', 'error');
            go('view=setup');
        }
        if (mb_strlen($user) < 3 || mb_strlen($pass) < 10 || $pass !== $pass2) {
            flash('Use a username of 3+ characters and a password of at least 10 characters, typed the same twice.', 'error');
            go('view=setup');
        }
        $db->prepare('INSERT INTO admins (created_at, username, password_hash) VALUES (?, ?, ?)')
           ->execute([now_utc(), $user, password_hash($pass, PASSWORD_DEFAULT)]);
        flash('Account created. Please log in.');
        go();
    }

    // ---- login
    if ($action === 'login') {
        csrf_check();
        if (too_many_failures($db)) {
            flash('Too many attempts. Please wait 15 minutes and try again.', 'error');
            go();
        }
        $user = clean($_POST['username'] ?? '', 60);
        $q = $db->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
        $q->execute([$user]);
        $row = $q->fetch();
        $ok = $row && password_verify((string) ($_POST['password'] ?? ''), $row['password_hash']);
        record_attempt($db, (bool) $ok);
        if (!$ok) {
            flash('Wrong username or password.', 'error');
            go();
        }
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $row['id'];
        $_SESSION['admin_name'] = $row['username'];
        $db->prepare('UPDATE admins SET last_login = ? WHERE id = ?')->execute([now_utc(), (int) $row['id']]);
        go('view=projects');
    }

    // everything below needs a logged-in admin
    if (!is_logged_in()) {
        go();
    }
    csrf_check();

    switch ($action) {
        case 'logout':
            $_SESSION = [];
            session_regenerate_id(true);
            go();

        case 'project_save':
            $id = (int) ($_POST['id'] ?? 0);
            $fields = [
                clean($_POST['title_en'] ?? '', 120), clean($_POST['title_ar'] ?? '', 120),
                clean($_POST['location'] ?? '', 80), clean($_POST['property_type'] ?? '', 40),
                clean_multiline($_POST['description_en'] ?? '', 2000), clean_multiline($_POST['description_ar'] ?? '', 2000),
                in_array($_POST['status'] ?? '', ['published', 'draft'], true) ? $_POST['status'] : 'published',
            ];
            if ($fields[0] === '') {
                flash('Please give the project a title.', 'error');
                go($id ? "view=project&id=$id" : 'view=project');
            }
            if ($id) {
                $db->prepare('UPDATE projects SET title_en=?, title_ar=?, location=?, property_type=?, description_en=?, description_ar=?, status=? WHERE id=?')
                   ->execute([...$fields, $id]);
                flash('Project saved.');
            } else {
                $db->prepare('INSERT INTO projects (created_at, title_en, title_ar, location, property_type, description_en, description_ar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                   ->execute([now_utc(), ...$fields]);
                $id = (int) $db->lastInsertId();
                flash('Project created. Now add its photos and videos below.');
            }
            go("view=project&id=$id");

        case 'project_delete':
            $id = (int) ($_POST['id'] ?? 0);
            $media = $db->prepare('SELECT path, thumb FROM project_media WHERE project_id = ?');
            $media->execute([$id]);
            $docroot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__)), '/');
            foreach ($media->fetchAll() as $m) {
                foreach ([$m['path'], $m['thumb']] as $f) {
                    if ($f && str_starts_with($f, 'uploads/')) {
                        @unlink($docroot . '/' . $f);
                    }
                }
            }
            @rmdir(uploads_root() . "/projects/$id");
            $db->prepare('DELETE FROM project_media WHERE project_id = ?')->execute([$id]);
            $db->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
            flash('Project deleted.');
            go('view=projects');

        case 'media_upload':
            $id = (int) ($_POST['id'] ?? 0);
            $exists = $db->prepare('SELECT COUNT(*) FROM projects WHERE id = ?');
            $exists->execute([$id]);
            if (!(int) $exists->fetchColumn()) {
                go('view=projects');
            }
            $dir = uploads_root() . "/projects/$id";
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $files = $_FILES['media'] ?? null;
            $done = 0;
            $problems = [];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $count = is_array($files['name'] ?? null) ? count($files['name']) : 0;
            for ($i = 0; $i < $count; $i++) {
                $label = (string) $files['name'][$i];
                if ($files['error'][$i] !== UPLOAD_ERR_OK || !is_uploaded_file($files['tmp_name'][$i])) {
                    $problems[] = "$label: upload failed";
                    continue;
                }
                $tmp = $files['tmp_name'][$i];
                $mime = (string) $finfo->file($tmp);
                $size = (int) $files['size'][$i];
                $name = bin2hex(random_bytes(12));
                try {
                    if (isset(IMAGE_TYPES[$mime])) {
                        if ($size > MAX_IMAGE_BYTES) {
                            throw new RuntimeException('image larger than 20 MB');
                        }
                        [$w, $h] = process_image($tmp, $dir, $name);
                        $path = "uploads/projects/$id/$name.webp";
                        $thumb = "uploads/projects/$id/$name-thumb.webp";
                        $db->prepare('INSERT INTO project_media (project_id, created_at, kind, path, thumb, width, height) VALUES (?, ?, ?, ?, ?, ?, ?)')
                           ->execute([$id, now_utc(), 'image', $path, $thumb, $w, $h]);
                    } elseif (isset(VIDEO_TYPES[$mime])) {
                        if ($size > MAX_VIDEO_BYTES) {
                            throw new RuntimeException('video larger than 500 MB');
                        }
                        $file = "$name." . VIDEO_TYPES[$mime];
                        if (!move_uploaded_file($tmp, "$dir/$file")) {
                            throw new RuntimeException('could not save the video');
                        }
                        $db->prepare('INSERT INTO project_media (project_id, created_at, kind, path) VALUES (?, ?, ?, ?)')
                           ->execute([$id, now_utc(), 'video', "uploads/projects/$id/$file"]);
                    } else {
                        throw new RuntimeException('not a supported photo or video (JPG, PNG, WebP, MP4, WebM, MOV)');
                    }
                    $done++;
                } catch (Throwable $e) {
                    $problems[] = "$label: " . $e->getMessage();
                }
            }
            $msg = "$done file(s) uploaded.";
            if ($problems) {
                $msg .= ' Not uploaded — ' . implode('; ', $problems);
            }
            flash($msg, $problems ? 'error' : 'ok');
            go("view=project&id=$id");

        case 'media_delete':
            $mid = (int) ($_POST['media_id'] ?? 0);
            $q = $db->prepare('SELECT project_id, path, thumb FROM project_media WHERE id = ?');
            $q->execute([$mid]);
            if ($m = $q->fetch()) {
                $docroot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__)), '/');
                foreach ([$m['path'], $m['thumb']] as $f) {
                    if ($f && str_starts_with($f, 'uploads/')) {
                        @unlink($docroot . '/' . $f);
                    }
                }
                $db->prepare('DELETE FROM project_media WHERE id = ?')->execute([$mid]);
                flash('File removed.');
                go('view=project&id=' . (int) $m['project_id']);
            }
            go('view=projects');

        case 'review_status':
            $rid = (int) ($_POST['review_id'] ?? 0);
            $status = ($_POST['status'] ?? '') === 'hidden' ? 'hidden' : 'published';
            $db->prepare('UPDATE reviews SET status = ? WHERE id = ?')->execute([$status, $rid]);
            flash($status === 'hidden' ? 'Review hidden from the website.' : 'Review is visible on the website again.');
            go('view=reviews');

        case 'review_delete':
            $db->prepare('DELETE FROM reviews WHERE id = ?')->execute([(int) ($_POST['review_id'] ?? 0)]);
            flash('Review deleted.');
            go('view=reviews');
    }
    go();
}

function clean_multiline(?string $value, int $max): string
{
    $value = str_replace(["\r\n", "\r"], "\n", (string) $value);
    $value = preg_replace('/[\x00-\x09\x0B-\x1F\x7F]/u', '', $value) ?? '';
    return mb_substr(trim($value), 0, $max);
}

/* ================================================================ GET views */

$adminCount = (int) $db->query('SELECT COUNT(*) FROM admins')->fetchColumn();

if ($adminCount === 0) {
    page('Create admin account', '<div class="panel panel--narrow"><h1>Create your admin account</h1>'
        . '<p>This appears only once. Enter the setup key you were given, then choose your own username and password.</p>'
        . '<form method="post" action="/admin/" class="form">' . csrf_field()
        . '<input type="hidden" name="action" value="setup">'
        . field('Setup key', '<input name="setup_key" required autocomplete="off">')
        . field('Username', '<input name="username" required minlength="3" maxlength="60" autocomplete="username">')
        . field('Password (at least 10 characters)', '<input name="password" type="password" required minlength="10" autocomplete="new-password">')
        . field('Type the password again', '<input name="password2" type="password" required minlength="10" autocomplete="new-password">')
        . '<button class="btn" type="submit">Create account</button></form></div>');
}

if (!is_logged_in()) {
    page('Log in', '<div class="panel panel--narrow"><h1>Admin login</h1>'
        . '<form method="post" action="/admin/" class="form">' . csrf_field()
        . '<input type="hidden" name="action" value="login">'
        . field('Username', '<input name="username" required autocomplete="username">')
        . field('Password', '<input name="password" type="password" required autocomplete="current-password">')
        . '<button class="btn" type="submit">Log in</button></form></div>');
}

function field(string $label, string $control): string
{
    return '<label class="field"><span>' . e($label) . '</span>' . $control . '</label>';
}

$view = (string) ($_GET['view'] ?? 'projects');

/* ---- one project: edit form + media */
if ($view === 'project') {
    $id = (int) ($_GET['id'] ?? 0);
    $p = ['id' => 0, 'title_en' => '', 'title_ar' => '', 'location' => '', 'property_type' => '',
          'description_en' => '', 'description_ar' => '', 'status' => 'published'];
    if ($id) {
        $q = $db->prepare('SELECT * FROM projects WHERE id = ?');
        $q->execute([$id]);
        $p = $q->fetch() ?: go('view=projects');
    }
    $sel = fn(string $v) => $p['status'] === $v ? ' selected' : '';
    $html = '<p><a href="/admin/?view=projects">← All projects</a></p>'
        . '<div class="panel"><h1>' . ($id ? 'Edit project' : 'New project') . '</h1>'
        . '<form method="post" action="/admin/" class="form form--grid">' . csrf_field()
        . '<input type="hidden" name="action" value="project_save"><input type="hidden" name="id" value="' . (int) $p['id'] . '">'
        . field('Title (English) *', '<input name="title_en" required maxlength="120" value="' . e($p['title_en']) . '" placeholder="e.g. 4-bedroom villa move in Arabian Ranches">')
        . field('Title (Arabic)', '<input name="title_ar" dir="rtl" maxlength="120" value="' . e($p['title_ar']) . '">')
        . field('Location', '<input name="location" maxlength="80" value="' . e($p['location']) . '" placeholder="e.g. Dubai Marina">')
        . field('Property type', '<input name="property_type" maxlength="40" value="' . e($p['property_type']) . '" placeholder="e.g. 2-bedroom apartment">')
        . field('Description (English)', '<textarea name="description_en" rows="4" maxlength="2000">' . e($p['description_en']) . '</textarea>')
        . field('Description (Arabic)', '<textarea name="description_ar" dir="rtl" rows="4" maxlength="2000">' . e($p['description_ar']) . '</textarea>')
        . field('Status', '<select name="status"><option value="published"' . $sel('published') . '>Published — visible on the website</option><option value="draft"' . $sel('draft') . '>Draft — hidden</option></select>')
        . '<div class="form__actions"><button class="btn" type="submit">Save project</button></div></form></div>';

    if ($id) {
        $m = $db->prepare('SELECT * FROM project_media WHERE project_id = ? ORDER BY sort, id');
        $m->execute([$id]);
        $media = $m->fetchAll();
        $html .= '<div class="panel"><h2>Photos &amp; videos</h2>'
            . '<form method="post" action="/admin/" enctype="multipart/form-data" class="upload">' . csrf_field()
            . '<input type="hidden" name="action" value="media_upload"><input type="hidden" name="id" value="' . $id . '">'
            . '<label class="upload__drop"><span><b>Choose photos or videos</b><br>JPG, PNG, WebP up to 20 MB · MP4, WebM, MOV up to 500 MB · several at once</span>'
            . '<input type="file" name="media[]" multiple required accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime"></label>'
            . '<button class="btn" type="submit">Upload</button></form>';
        if ($media) {
            $html .= '<div class="media-grid">';
            foreach ($media as $item) {
                $src = '/' . $item['path'];
                $preview = $item['kind'] === 'video'
                    ? '<video src="' . e($src) . '" preload="metadata" muted playsinline></video>'
                    : '<img src="' . e('/' . ($item['thumb'] ?: $item['path'])) . '" alt="" loading="lazy">';
                $html .= '<figure class="media">' . $preview
                    . '<figcaption>' . e(ucfirst($item['kind'])) . '<form method="post" action="/admin/" data-confirm="Remove this file?">'
                    . csrf_field() . '<input type="hidden" name="action" value="media_delete"><input type="hidden" name="media_id" value="' . (int) $item['id'] . '">'
                    . '<button class="link-danger" type="submit">Remove</button></form></figcaption></figure>';
            }
            $html .= '</div>';
        } else {
            $html .= '<p class="muted">No photos or videos yet.</p>';
        }
        $html .= '</div>'
            . '<div class="panel panel--danger"><h2>Delete project</h2><p>Deletes the project and all of its photos and videos.</p>'
            . '<form method="post" action="/admin/" data-confirm="Delete this project and all its files?">' . csrf_field()
            . '<input type="hidden" name="action" value="project_delete"><input type="hidden" name="id" value="' . $id . '">'
            . '<button class="btn btn--danger" type="submit">Delete project</button></form></div>';
    }
    page($id ? 'Edit project' : 'New project', $html, true);
}

/* ---- reviews */
if ($view === 'reviews') {
    $rows = $db->query('SELECT * FROM reviews ORDER BY created_at DESC, id DESC LIMIT 300')->fetchAll();
    $html = '<div class="panel"><h1>Customer reviews</h1><p class="muted">New reviews appear on the website immediately. Hide anything that is spam or not from a real customer. Phone numbers are only visible here.</p>';
    if (!$rows) {
        $html .= '<p class="muted">No reviews yet.</p>';
    } else {
        $html .= '<div class="table-wrap"><table><thead><tr><th>Date</th><th>Customer</th><th>Rating</th><th>Review</th><th>Status</th><th></th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $hidden = $r['status'] === 'hidden';
            $html .= '<tr' . ($hidden ? ' class="is-hidden"' : '') . '><td>' . e(substr($r['created_at'], 0, 16)) . '</td>'
                . '<td><b>' . e($r['name']) . '</b><br><span class="muted">' . e($r['phone'] ?? '') . '<br>' . e(trim(($r['area'] ?? '') . ' ' . ($r['service'] ?? ''))) . '</span></td>'
                . '<td>' . str_repeat('★', (int) $r['rating']) . '</td>'
                . '<td>' . nl2br(e($r['body'])) . '</td>'
                . '<td>' . ($hidden ? 'Hidden' : 'Live') . '</td><td class="actions">'
                . '<form method="post" action="/admin/">' . csrf_field() . '<input type="hidden" name="action" value="review_status"><input type="hidden" name="review_id" value="' . (int) $r['id'] . '">'
                . '<input type="hidden" name="status" value="' . ($hidden ? 'published' : 'hidden') . '"><button class="btn btn--sm btn--ghost" type="submit">' . ($hidden ? 'Show' : 'Hide') . '</button></form>'
                . '<form method="post" action="/admin/" data-confirm="Delete this review permanently?">' . csrf_field()
                . '<input type="hidden" name="action" value="review_delete"><input type="hidden" name="review_id" value="' . (int) $r['id'] . '">'
                . '<button class="link-danger" type="submit">Delete</button></form></td></tr>';
        }
        $html .= '</tbody></table></div>';
    }
    page('Reviews', $html . '</div>', true);
}

/* ---- leads */
if ($view === 'leads') {
    $rows = $db->query('SELECT * FROM leads ORDER BY created_at DESC, id DESC LIMIT 300')->fetchAll();
    $html = '<div class="panel"><h1>Quote requests</h1>';
    if (!$rows) {
        $html .= '<p class="muted">No quote requests yet.</p>';
    } else {
        $html .= '<div class="table-wrap"><table><thead><tr><th>Date</th><th>Name</th><th>Phone</th><th>Move</th><th>Details</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $wa = 'https://wa.me/' . ltrim(str_replace('+', '', (string) $r['phone']), '0');
            $html .= '<tr><td>' . e(substr($r['created_at'], 0, 16)) . '<br><span class="muted">' . e(strtoupper($r['lang'])) . '</span></td>'
                . '<td><b>' . e($r['name']) . '</b>' . ($r['email'] ? '<br>' . e($r['email']) : '') . '</td>'
                . '<td><a href="tel:' . e($r['phone']) . '">' . e($r['phone']) . '</a><br><a href="' . e($wa) . '" target="_blank" rel="noopener">WhatsApp</a></td>'
                . '<td>' . e(trim(($r['moving_from'] ?? '') . ' → ' . ($r['moving_to'] ?? ''), ' →')) . '<br><span class="muted">' . e(($r['property_type'] ?? '') . ' ' . ($r['moving_date'] ?? '')) . '</span></td>'
                . '<td>' . e($r['services']) . ($r['message'] ? '<br>' . nl2br(e($r['message'])) : '') . '</td></tr>';
        }
        $html .= '</tbody></table></div>';
    }
    page('Quote requests', $html . '</div>', true);
}

/* ---- projects list (default) */
$rows = $db->query('SELECT p.*, (SELECT COUNT(*) FROM project_media m WHERE m.project_id = p.id) AS media_count,
                           (SELECT thumb FROM project_media m WHERE m.project_id = p.id AND m.kind = \'image\' ORDER BY m.sort, m.id LIMIT 1) AS cover
                    FROM projects p ORDER BY p.sort, p.created_at DESC')->fetchAll();
$html = '<div class="panel"><div class="panel__head"><h1>Projects</h1><a class="btn" href="/admin/?view=project">+ New project</a></div>'
    . '<p class="muted">Projects appear on <a href="/projects/" target="_blank" rel="noopener">/projects/</a> with their photos and videos.</p>';
if (!$rows) {
    $html .= '<p class="muted">No projects yet. Create your first one.</p>';
} else {
    $html .= '<div class="project-cards">';
    foreach ($rows as $r) {
        $cover = $r['cover'] ? '<img src="/' . e($r['cover']) . '" alt="" loading="lazy">' : '<span class="project-cards__none">No photo</span>';
        $html .= '<a class="project-cards__item" href="/admin/?view=project&amp;id=' . (int) $r['id'] . '">' . $cover
            . '<b>' . e($r['title_en']) . '</b><span class="muted">' . e($r['location'] ?? '') . ' · ' . (int) $r['media_count'] . ' file(s)'
            . ($r['status'] === 'draft' ? ' · <em>Draft</em>' : '') . '</span></a>';
    }
    $html .= '</div>';
}
page('Projects', $html . '</div>', true);
