<?php
// index.php  –  Front Controller
// Run with: php -S localhost:8000 index.php

require_once __DIR__ . '/includes/cors.php';
require_once __DIR__ . '/includes/response.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// ── Parse path ──────────────────────────────────────────────
// Strip /api prefix (Vite proxy forwards the full path including /api)
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = preg_replace('#^/api#', '', $uri);   // remove /api prefix
$uri    = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// ── Route table ─────────────────────────────────────────────
//  Format: [METHOD, regex, handler_file]
//  Named capture (?P<id>\d+) is passed via $matches

$routes = [
    // Auth (public)
    ['POST', '#^/login$#',    'handlers/auth/login.php'],
    ['POST', '#^/register$#', 'handlers/auth/register.php'],
    ['POST', '#^/logout$#',   'handlers/auth/logout.php'],

    // Dashboard
    ['GET',  '#^/dashboard$#', 'handlers/dashboard.php'],

    // Expenses
    ['GET',    '#^/expenses$#',                  'handlers/expenses/list.php'],
    ['POST',   '#^/expenses$#',                  'handlers/expenses/create.php'],
    ['PUT',    '#^/expenses/(?P<id>\d+)$#',      'handlers/expenses/update.php'],
    ['DELETE', '#^/expenses/(?P<id>\d+)$#',      'handlers/expenses/delete.php'],

    // Reports
    ['GET', '#^/reports/summary$#',  'handlers/reports/summary.php'],
    ['GET', '#^/reports/daily$#',    'handlers/reports/daily.php'],
    ['GET', '#^/reports/category$#', 'handlers/reports/category.php'],
    ['GET', '#^/reports/monthly$#',  'handlers/reports/monthly.php'],

    // Settings
    ['PUT',    '#^/settings/profile$#',       'handlers/settings/profile.php'],
    ['PUT',    '#^/settings/password$#',      'handlers/settings/password.php'],
    ['PUT',    '#^/settings/notifications$#', 'handlers/settings/notifications.php'],
    ['GET',    '#^/settings/export$#',        'handlers/settings/export.php'],
    ['DELETE', '#^/settings/account$#',       'handlers/settings/account.php'],
];

// ── Match & Dispatch ─────────────────────────────────────────
foreach ($routes as [$routeMethod, $pattern, $handler]) {
    if ($method !== $routeMethod) continue;
    if (!preg_match($pattern, $uri, $matches)) continue;

    // Make named captures available as $routeParams
    $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

    $file = __DIR__ . '/' . $handler;
    if (!file_exists($file)) {
        jsonError("Handler not found: $handler", 500);
    }

    require $file;
    exit;
}

// ── 404 ──────────────────────────────────────────────────────
http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => "Route not found: $method $uri"
]);
