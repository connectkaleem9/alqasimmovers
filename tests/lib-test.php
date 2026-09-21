<?php
declare(strict_types=1);
/**
 * Tests for the form handler helpers. Run:  php tests/lib-test.php
 * No framework, no dependencies — the same rule as the rest of the project.
 */
require __DIR__ . '/../src/php/lib.php';

$pass = 0;
$fail = 0;

/** Character count that works with or without mbstring (mirrors lib.php). */
function char_len(string $s): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($s, 'UTF-8')
        : count(preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: []);
}

function check(string $label, $actual, $expected): void
{
    global $pass, $fail;
    if ($actual === $expected) {
        $pass++;
        return;
    }
    $fail++;
    printf("  FAIL %s\n       expected: %s\n       actual:   %s\n", $label,
        var_export($expected, true), var_export($actual, true));
}

echo "\nPhone normalisation\n";
check('spaces and dashes removed', normalise_phone('055 686 9224'), '0556869224');
check('international kept', normalise_phone('+971 55 686 9224'), '+971556869224');
check('Arabic-Indic digits', normalise_phone('٠٥٥٦٨٦٩٢٢٤'), '0556869224');
check('Persian digits', normalise_phone('۰۵۵۶۸۶۹۲۲۴'), '0556869224');
check('letters stripped', normalise_phone('call 0556869224 now'), '0556869224');

echo "Phone validation\n";
check('local mobile', valid_uae_phone('0556869224'), true);
check('international mobile', valid_uae_phone('+971556869224'), true);
check('971 without plus', valid_uae_phone('971556869224'), true);
check('Dubai landline', valid_uae_phone('042345678'), true);
check('800 number', valid_uae_phone('8004826'), true);
check('Arabic digits accepted', valid_uae_phone('٠٥٥٦٨٦٩٢٢٤'), true);
check('too short rejected', valid_uae_phone('05568'), false);
check('too long rejected', valid_uae_phone('05568692244444'), false);
check('non-UAE rejected', valid_uae_phone('+447700900123'), false);
check('empty rejected', valid_uae_phone(''), false);
check('letters rejected', valid_uae_phone('not a phone'), false);

echo "Input cleaning\n";
check('trims', clean('  Ahmed  ', 80), 'Ahmed');
check('truncates to limit', clean(str_repeat('a', 200), 80), str_repeat('a', 80));
check('strips control chars', clean("Ahmed\x00\x07", 80), 'Ahmed');
check('strips CR/LF (header injection)', clean("Ahmed\r\nBcc: victim@example.com", 80), 'Ahmed' . 'Bcc: victim@example.com');
check('keeps Arabic text', clean('أحمد', 80), 'أحمد');
check('counts Arabic by characters', char_len(clean(str_repeat('م', 200), 80)), 80);
check('null becomes empty', clean(null, 80), '');
check('script tag is stored as text, not executed', clean('<script>alert(1)</script>', 80), '<script>alert(1)</script>');

echo "\nHeader-injection safety check\n";
$evil = clean("Ahmed\r\nContent-Type: text/html", 80);
check('no CR in cleaned value', str_contains($evil, "\r"), false);
check('no LF in cleaned value', str_contains($evil, "\n"), false);

printf("\n%d passed, %d failed\n\n", $pass, $fail);
exit($fail === 0 ? 0 : 1);
