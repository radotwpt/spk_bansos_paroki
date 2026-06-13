<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'ling1@spk-bansos.local')->first();
if (!$user) {
    echo "User not found\n";
} else {
    echo "User exists.\n";
    if (\Illuminate\Support\Facades\Hash::check('lingkungan12345', $user->password)) {
        echo "Password match!\n";
    } else {
        echo "Password does NOT match! It's hashed as: " . $user->password . "\n";
    }
}
