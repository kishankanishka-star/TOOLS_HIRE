<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tool_id = $_POST['tool_id'] ?? null;
    $rating = $_POST['overall_rating'] ?? 5;
    $comment = $_POST['comment'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
    if ($tool_id && $user_id) {
        // Verify that the user has actually rented and returned this item
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ? AND tool_id = ? AND status = 'completed'");
        $stmt->execute([$user_id, $tool_id]);
        if ($stmt->fetchColumn() == 0) {
            header("Location: tool-detail.php?id=$tool_id&msg=" . urlencode("Error: You can only review items after you have returned them."));
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO reviews (tool_id, user_id, overall_rating, comment, status) 
                               VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$tool_id, $user_id, $rating, $comment]);
        header("Location: tool-detail.php?id=$tool_id&msg=" . urlencode("Review submitted for moderation."));
        exit;
    }
}
header('Location: catalogue.php');
exit;
?>
