<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    Illuminate\Http\Request::capture()
);

header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'app_key_set' => env('APP_KEY') ? 'yes' : 'no',
    'app_debug' => env('APP_DEBUG'),
    'db_connection' => env('DB_CONNECTION'),
    'app_env' => env('APP_ENV'),
]);
