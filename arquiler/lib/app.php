<?php
define('ASSETS_URL', '/assets');

$script_name = parse_url($_SERVER['SCRIPT_NAME'] ?? '/', PHP_URL_PATH);
$parts = explode('/', trim($script_name, '/'));
$base_url = ($parts[0] ?? '') === 'arquiler' ? '/arquiler' : '';
define('BASE_URL', $base_url);

?>