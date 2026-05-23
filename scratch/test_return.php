<?php
require_once 'c:\xampp\htdocs\tools_hire\config\database.php';
$_GET['id'] = 2; // Pending rental for tool 2
$_GET['status'] = 'completed';
$_SESSION['role'] = 'admin';

// Simulate the logic in manage-rentals.php
    $id = $_GET['id'];
    $status = $_GET['status'];
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE rentals SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $stmt = $pdo->prepare("SELECT tool_id FROM rentals WHERE id = ?");
        $stmt->execute([$id]);
        $tool_id = $stmt->fetchColumn();
        $tool_status = 'Available';
        if ($status === 'confirmed') $tool_status = 'Rented';
        $stmt = $pdo->prepare("UPDATE tools SET availability_status = ? WHERE id = ?");
        $stmt->execute([$tool_status, $tool_id]);
        $pdo->commit();
        echo "Success";
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
