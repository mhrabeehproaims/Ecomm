<?php

// Local development only. PHP's static-file handler must not bypass financial-document privacy.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
if (str_contains($path, "\0") || str_contains($path, '\\')) {
    http_response_code(404);

    return;
}
$parts = [];
foreach (explode('/', $path) as $part) {
    if ($part === '..') {
        array_pop($parts);
    } elseif ($part !== '' && $part !== '.') {
        $parts[] = $part;
    }
}
$path = '/'.implode('/', $parts);
if (preg_match('~^/storage/(Invoices|PackingSlips)(/|$)~i', $path) || preg_match('~(^|/)\.~', $path)) {
    http_response_code(404);

    return;
}
$public = dirname(__DIR__).'/public';
if ($path !== '/' && is_file($public.$path) && ! str_ends_with(strtolower($path), '.php')) {
    return false;
}
require $public.'/index.php';
