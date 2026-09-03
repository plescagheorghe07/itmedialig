<?php
/**
 * Exemplu config — copiază ca config.php înainte de instalare
 *
 * Cloudflare Flexible (client HTTPS → origin HTTP):
 * - app.url = https://domeniul-tau.ro  (URL-ul public, cu https)
 * - app.force_https = true
 * - WebSocket: proxiat prin nginx pe /ws (wss pe 443), NU pe port custom public
 *   Cloudflare orange-cloud NU proxy-uiește bine porturi tip 3007.
 */
return [
    'is_setup' => false,
    'app' => [
        'name' => 'Trofeu Hub',
        // URL public (cum îl vede clientul) — cu https dacă e Cloudflare
        'url' => 'https://trofeu.example.com',
        'timezone' => 'Europe/Bucharest',
        'debug' => false,
        // true când origin e HTTP dar clienții vin pe HTTPS (Cloudflare Flexible)
        'force_https' => true,
    ],
    'database' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'trofeu_hub',
        'username' => '',
        'password' => '',
        'charset' => 'UTF-8',
    ],
    'upload' => [
        'max_size' => 5242880,
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
    ],
    'session' => [
        'name' => 'trofeu_hub_session',
        'lifetime' => 7200,
    ],
    'redis' => [
        'enabled' => true,
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 3008, // doar localhost
        'password' => '',
        'database' => 0,
        'prefix' => 'trofeu:',
        'cache_ttl' => 300,
    ],
    'websocket' => [
        // Procesul Ratchet ascultă LOCAL
        'host' => '127.0.0.1',
        'port' => 3007,

        // Ce vede browserul (HTTPS pe Cloudflare)
        // Varianta recomandată: path proxiat de nginx → wss://domeniu/ws
        'public_host' => 'trofeu.example.com',
        'public_path' => '/ws',
        // Alternativ, URL complet (override total):
        // 'public_url' => 'wss://trofeu.example.com/ws',
        // Nu folosi public_port:3007 cu Cloudflare orange-cloud (nu e proxiat).
        'public_port' => 0,
    ],
];
