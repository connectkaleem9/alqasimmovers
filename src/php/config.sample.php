<?php
/**
 * SAMPLE configuration — this file contains NO real credentials and is safe to commit.
 *
 * On the server, copy it to a directory ABOVE public_html, for example:
 *     /home/u123456789/private/alqasim-config.php
 * fill in the real values, and set permissions to 600.
 * Nothing outside public_html is reachable from the web, so the password can never be
 * downloaded even if PHP stops executing.
 *
 * The handler looks for the file at the path in the QASIM_CONFIG environment variable,
 * then at ../private/alqasim-config.php relative to the document root.
 */

return [
    // ---- MySQL (hPanel → Databases → MySQL Databases) ----
    'db' => [
        'host'     => 'localhost',
        'name'     => 'u000000000_alqasim',
        'user'     => 'u000000000_alqasim',
        'password' => 'REPLACE_ME',
        'charset'  => 'utf8mb4',
    ],

    // ---- Where new leads are emailed ----
    // Use a mailbox on your own domain as the sender, otherwise mail is likely to be
    // treated as spam. Create it in hPanel → Emails.
    'mail' => [
        'to'      => 'REPLACE_ME@alqasimmovers.com',   // the owner's inbox
        'from'    => 'website@alqasimmovers.com',      // must exist on the domain
        'subject' => 'New moving quote request',
        'enabled' => true,
    ],

    // ---- Anti-abuse ----
    'limits' => [
        'per_ip_per_10min' => 5,     // submissions allowed from one address
        'min_seconds'      => 3,     // a form filled faster than this is a bot
    ],

    // Salt used to hash visitor IP addresses before storing them (privacy).
    // Generate once with: php -r "echo bin2hex(random_bytes(16));"
    'ip_salt' => 'REPLACE_ME_WITH_RANDOM_HEX',
];
