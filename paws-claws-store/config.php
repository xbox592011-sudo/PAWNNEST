<?php
session_start();
$host = 'localhost';
$db   = 'paws_claws_db';
$user = 'root';
$pass = ''; // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed. Please import database.sql in phpMyAdmin. Error: " . $e->getMessage());
}

$stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$store_settings = $stmt->fetch();
?>