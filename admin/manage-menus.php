<?php
require_once '../includes/config.php'; // Ensure this path is correct

// Mockup users data
$users = [
    ['username' => 'admin', 'password' => '123', 'role' => 'manager'],
    ['username' => 'user', 'password' => '1234', 'role' => 'employee'],
    ['username' => 'manager', 'password' => '12345', 'role' => 'manager']
];

// Insert users into the database
foreach ($users as $user) {
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user['username'], $hashedPassword, $user['role']]);
}

echo "Users have been inserted successfully!";
?>