<?php
require_once 'c:\xampp\htdocs\tools_hire\config\database.php';
$stmt = $pdo->query("SELECT * FROM rentals");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
