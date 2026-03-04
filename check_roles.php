<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = \Spatie\Permission\Models\Role::pluck('name')->toArray();
file_put_contents('/tmp/roles.json', json_encode($roles));
