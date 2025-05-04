<?php
// db_connect.php

$host   = '127.0.0.1';
$dbname = 'tracker_app';
$user   = 'root';
$pass   = '';            // blank
$charset= 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // if you still get an error, this will show it
    die("Database connection failed: " . $e->getMessage());
}

