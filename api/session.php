<?php
declare(strict_types=1);


require __DIR__ . '/../config/bootstrap.php';


header('Content-Type: application/json; charset=utf-8');


$user = current_user();


echo json_encode([
    'authenticated' => $user !== null,
    'admin' => is_admin(),
    'name' => $user['full_name'] ?? null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

