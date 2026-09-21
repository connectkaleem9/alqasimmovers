<?php
declare(strict_types=1);
/**
 * Shared helpers for the form handlers. Kept in a separate file so they can be
 * unit-tested without running the request flow (see tests/lib-test.php).
 */

const FIELD_LIMITS = [
    'name' => 80, 'phone' => 20, 'email' => 120, 'moving_from' => 80,
    'moving_to' => 80, 'property_type' => 20, 'message' => 1200,
];

const ALLOWED_SERVICES = ['packing', 'unpacking', 'assembly', 'storage', 'handyman', 'heavy', 'truck'];
const ALLOWED_PROPERTY = ['studio', 'apt1', 'apt2', 'apt3', 'villa', 'office', 'shop', 'items'];

/* ------------------------------------------------------------------ helpers */

function load_config(): array
{
    $candidates = [];
    if ($env = getenv('QASIM_CONFIG')) {
        $candidates[] = $env;
    }
    $root = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__;
    $candidates[] = dirname($root) . '/private/alqasim-config.php';
    $candidates[] = dirname($root) . '/alqasim-config.php';

    foreach ($candidates as $path) {
        if (is_readable($path)) {
            $config = require $path;
            if (is_array($config)) {
                return $config;
            }
        }
    }
    throw new RuntimeException('configuration file not found');
}

/**
 * Trim, strip control characters (including CR/LF, which stops mail-header injection),
 * and enforce a maximum length counted in characters, not bytes, so Arabic input is
 * not cut mid-character.
 *
 * mbstring is standard on Hostinger, but the fallback keeps the handler working —
 * and keeps multi-byte text intact — if a host ever ships without it.
 */
function clean(?string $value, int $max): string
{
    $value = (string) $value;
    $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? '';
    $value = trim($value);

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }
    // UTF-8 aware fallback: split into characters without mbstring.
    $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return implode('', array_slice($chars, 0, $max));
}

/** Convert Arabic-Indic digits to Western ones, then strip formatting. */
function normalise_phone(string $phone): string
{
    $map = ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5',
            '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5',
            '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9'];
    $phone = strtr($phone, $map);
    return preg_replace('/[^\d+]/', '', $phone) ?? '';
}

function valid_uae_phone(string $phone): bool
{
    $p = normalise_phone($phone);
    // +9715XXXXXXXX / 05XXXXXXXX / landline 0[234679]XXXXXXX / 800XXXXXX
    return (bool) preg_match('/^(?:\+?971|0)(?:5\d{8}|[234679]\d{7})$/', $p)
        || (bool) preg_match('/^800\d{3,7}$/', $p);
}

function redirect(string $location): never
{
    header('Location: ' . $location, true, 303);
    exit;
}

/** A failure never blocks the visitor: they are sent back with a flag, never with their data echoed. */
function bail(string $back, string $reason): never
{
    $sep = str_contains($back, '?') ? '&' : '?';
    redirect($back . $sep . 'error=' . rawurlencode($reason));
}
