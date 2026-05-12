<?php
echo "<h3>\$_ENV</h3><pre>";
print_r(array_filter($_ENV, fn($k) => !str_starts_with($k, 'AWS_'), ARRAY_FILTER_USE_KEY));
echo "</pre><h3>\$_SERVER (env vars)</h3><pre>";
print_r(array_filter($_SERVER, fn($k) => !str_starts_with($k, 'SERVER_') && !str_starts_with($k, 'HTTP_') && !str_starts_with($k, 'PATH'), ARRAY_FILTER_USE_KEY));
echo "</pre><h3>getenv()</h3><pre>";
echo "SESSION_DRIVER: " . var_export(getenv('SESSION_DRIVER'), true) . "\n";
echo "CACHE_STORE: " . var_export(getenv('CACHE_STORE'), true) . "\n";
echo "APP_KEY: " . var_export(getenv('APP_KEY'), true) . "\n";
echo "APP_DEBUG: " . var_export(getenv('APP_DEBUG'), true) . "\n";
echo "DB_CONNECTION: " . var_export(getenv('DB_CONNECTION'), true) . "\n";
echo "</pre>";
