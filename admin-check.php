<?php
// CLI/Browser check for admin users. Delete after use.

require_once __DIR__ . '/config/db.php';

$result = $conn->query('SELECT id, username, role, created_at FROM admins ORDER BY id');

if (!$result) {
    echo "Query failed.\n";
    exit(1);
}

$rows = $result->fetch_all(MYSQLI_ASSOC);
$result->free();

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "Reminder: delete this file after use.\n\n";

if (count($rows) === 0) {
    echo "No admins found.\n";
    exit(0);
}

foreach ($rows as $row) {
    echo "#{$row['id']} {$row['username']} ({$row['role']}) {$row['created_at']}\n";
}
