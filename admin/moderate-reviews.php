<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    $stmt = $pdo->prepare("INSERT INTO moderator_actions (admin_id, action_type, target_type, target_id) VALUES (?, ?, 'review', ?)");
    $stmt->execute([$_SESSION['user_id'], $action, $id]);
    header('Location: moderate-reviews.php?msg=Action completed.');
    exit;
}
$reviews = $pdo->query("SELECT r.*, t.name as tool_name, u.username 
                        FROM reviews r 
                        JOIN tools t ON r.tool_id = t.id 
                        LEFT JOIN users u ON r.user_id = u.id 
                        ORDER BY r.status DESC, r.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Moderate Reviews | Shelton Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 admin-sidebar px-0">
            <div class="px-4 mb-5">
                <h5 class="fw-bold text-secondary">SHELTON</h5>
            </div>
            <a href="dashboard.php" class="admin-nav-link"><i class="fas fa-home me-2"></i> Dashboard</a>
            <a href="manage-tools.php" class="admin-nav-link"><i class="fas fa-tools me-2"></i> Manage Tools</a>
            <a href="moderate-reviews.php" class="admin-nav-link active"><i class="fas fa-comments me-2"></i> Reviews</a>
            <div class="mt-auto p-4">
                <a href="../logout.php" class="btn btn-outline-light btn-sm w-100">Logout</a>
            </div>
        </nav>
        <main class="col-md-10 p-5">
            <h2 class="fw-bold mb-4">Review Moderation</h2>
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                    <?php echo h($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4">Status</th>
                                <th>Equipment</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $rev): ?>
                            <tr>
                                <td class="px-4">
                                    <?php if ($rev['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif ($rev['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?php echo h($rev['tool_name']); ?></td>
                                <td><?php echo h($rev['username'] ?: 'Guest'); ?></td>
                                <td><?php echo renderStars($rev['overall_rating']); ?></td>
                                <td style="max-width: 300px;"><?php echo h($rev['comment']); ?></td>
                                <td class="small text-muted"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></td>
                                <td>
                                    <?php if ($rev['status'] === 'pending'): ?>
                                        <a href="?id=<?php echo $rev['id']; ?>&action=approve" class="btn btn-success btn-sm rounded-pill px-3">Approve</a>
                                        <a href="?id=<?php echo $rev['id']; ?>&action=reject" class="btn btn-outline-danger btn-sm rounded-pill px-3">Reject</a>
                                    <?php else: ?>
                                        <span class="text-muted small">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
