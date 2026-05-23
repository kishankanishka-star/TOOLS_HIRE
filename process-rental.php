<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tool_id = $_POST['tool_id'] ?? null;
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $total_cost = $_POST['total_cost'] ?? 0;
    $user_id = $_SESSION['user_id'] ?? 2; 
    if ($tool_id && $start_date && $end_date) {
        try {
            $stmt = $pdo->prepare("INSERT INTO rentals (tool_id, user_id, start_date, end_date, total_cost, status) 
                                   VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$tool_id, $user_id, $start_date, $end_date, $total_cost]);
            header("Location: rental-success.php?id=" . $pdo->lastInsertId());
            exit;
        } catch (PDOException $e) {
            die("Error processing rental: " . $e->getMessage());
        }
    }
}
header('Location: catalogue.php');
exit;
?>
