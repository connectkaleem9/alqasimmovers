<?php
declare(strict_types=1);
/**
 * Shared bootstrap for every PHP endpoint: configuration, database connection,
 * schema migration, escaping, sessions and CSRF.
 *
 * Database: SQLite by default (a single file kept ABOVE public_html, so it can
 * never be downloaded), or MySQL when config db.driver = 'mysql'. Tables are
 * created automatically on first use, so no manual import step is needed.
 */

require_once __DIR__ . '/lib.php';

function config(): array
{
    static $config = null;
    if ($config === null) {
        $config = load_config();
    }
    return $config;
}

function now_utc(): string
{
    return gmdate('Y-m-d H:i:s');
}

/** HTML-escape anything that is printed. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ip_hash(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return hash('sha256', (config()['ip_salt'] ?? '') . $ip);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $cfg = config()['db'] ?? [];
    $driver = $cfg['driver'] ?? 'sqlite';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if ($driver === 'sqlite') {
        $path = (string) ($cfg['path'] ?? '');
        if ($path === '') {
            throw new RuntimeException('db.path is not configured');
        }
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $pdo = new PDO('sqlite:' . $path, null, null, $options);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
    } else {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
            $cfg['host'] ?? 'localhost', $cfg['name'] ?? '', $cfg['charset'] ?? 'utf8mb4');
        $pdo = new PDO($dsn, $cfg['user'] ?? '', $cfg['password'] ?? '', $options);
    }

    migrate($pdo, $driver);
    return $pdo;
}

/** Idempotent schema creation — safe to run on every request. */
function migrate(PDO $db, string $driver): void
{
    $sqlite = $driver === 'sqlite';
    $id = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY';
    $text = $sqlite ? 'TEXT' : 'TEXT';
    $str = fn(int $n) => $sqlite ? 'TEXT' : "VARCHAR($n)";
    $int = $sqlite ? 'INTEGER' : 'INT';
    $tail = $sqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    $tables = [
        "CREATE TABLE IF NOT EXISTS leads (
            id $id, created_at {$str(19)} NOT NULL, lang {$str(2)} NOT NULL DEFAULT 'en',
            source_page {$str(191)} NOT NULL DEFAULT '', name {$str(80)} NOT NULL, phone {$str(20)} NOT NULL,
            email {$str(120)}, moving_from {$str(80)}, moving_to {$str(80)}, property_type {$str(20)},
            moving_date {$str(10)}, services {$str(255)} NOT NULL DEFAULT '', message $text,
            ip_hash {$str(64)} NOT NULL DEFAULT '', user_agent {$str(255)} NOT NULL DEFAULT '',
            status {$str(12)} NOT NULL DEFAULT 'new', notes $text
        )$tail",
        "CREATE TABLE IF NOT EXISTS reviews (
            id $id, created_at {$str(19)} NOT NULL, lang {$str(2)} NOT NULL DEFAULT 'en',
            name {$str(60)} NOT NULL, area {$str(60)}, service {$str(30)}, rating $int NOT NULL,
            body $text NOT NULL, phone {$str(20)}, ip_hash {$str(64)} NOT NULL DEFAULT '',
            status {$str(12)} NOT NULL DEFAULT 'published'
        )$tail",
        "CREATE TABLE IF NOT EXISTS projects (
            id $id, created_at {$str(19)} NOT NULL, title_en {$str(120)} NOT NULL, title_ar {$str(120)},
            location {$str(80)}, property_type {$str(40)}, description_en $text, description_ar $text,
            status {$str(12)} NOT NULL DEFAULT 'published', sort $int NOT NULL DEFAULT 0
        )$tail",
        "CREATE TABLE IF NOT EXISTS project_media (
            id $id, project_id $int NOT NULL, created_at {$str(19)} NOT NULL,
            kind {$str(8)} NOT NULL, path {$str(255)} NOT NULL, thumb {$str(255)},
            width $int, height $int, sort $int NOT NULL DEFAULT 0
        )$tail",
        "CREATE TABLE IF NOT EXISTS admins (
            id $id, created_at {$str(19)} NOT NULL, username {$str(60)} NOT NULL,
            password_hash {$str(255)} NOT NULL, last_login {$str(19)}
        )$tail",
        "CREATE TABLE IF NOT EXISTS login_attempts (
            id $id, created_at {$str(19)} NOT NULL, ip_hash {$str(64)} NOT NULL, success $int NOT NULL DEFAULT 0
        )$tail",
    ];
    foreach ($tables as $sql) {
        $db->exec($sql);
    }

    // columns added after the first release (existing databases get them on the next request)
    $added = ['reviews' => ['email' => $str(120)]];
    foreach ($added as $table => $columns) {
        foreach ($columns as $column => $type) {
            try {
                $db->query("SELECT $column FROM $table LIMIT 0");
            } catch (PDOException) {
                $db->exec("ALTER TABLE $table ADD COLUMN $column $type");
            }
        }
    }
}

/* ---------------------------------------------------------------- sessions */

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('aqm_admin');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/admin/', 'secure' => $https,
        'httponly' => true, 'samesite' => 'Strict',
    ]);
    session_start();

    // idle timeout: 2 hours
    $now = time();
    if (isset($_SESSION['seen']) && $now - (int) $_SESSION['seen'] > 7200) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['seen'] = $now;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = (string) ($_POST['csrf'] ?? '');
    if ($sent === '' || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(403);
        exit('Security check failed. Please go back, refresh the page and try again.');
    }
}
