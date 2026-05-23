<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
$totalTools = $pdo->query("SELECT COUNT(*) FROM tools")->fetchColumn();
$pendingReviews = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
$approvedReviews = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved'")->fetchColumn();
$avgRating = $pdo->query("SELECT AVG(overall_rating) FROM reviews WHERE status = 'approved'")->fetchColumn() ?: 0;
$totalRentals = $pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
$activeRentals = $pdo->query("SELECT COUNT(*) FROM rentals WHERE status = 'confirmed'")->fetchColumn();
$reviews = $pdo->query("SELECT r.*, t.name as tool_name, u.username 
                        FROM reviews r 
                        JOIN tools t ON r.tool_id = t.id 
                        LEFT JOIN users u ON r.user_id = u.id 
                        WHERE r.status = 'pending' 
                        ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Shelton Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 admin-sidebar px-0">
            <div class="px-4 mb-5">
                <h5 class="fw-bold text-secondary">SHELTON</h5>
            </div>
            <a href="dashboard.php" class="admin-nav-link active"><i class="fas fa-home me-2"></i> Dashboard</a>
            <a href="manage-tools.php" class="admin-nav-link"><i class="fas fa-tools me-2"></i> Manage Tools</a>
            <a href="manage-rentals.php" class="admin-nav-link"><i class="fas fa-receipt me-2"></i> Manage Rentals</a>
            <a href="moderate-reviews.php" class="admin-nav-link"><i class="fas fa-comments me-2"></i> Reviews 
                <?php if ($pendingReviews > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $pendingReviews; ?></span>
                <?php endif; ?>
            </a>
            <div class="mt-auto p-4">
                <a href="../logout.php" class="btn btn-outline-light btn-sm w-100">Logout</a>
            </div>
        </nav>
        <!-- Main Content -->
        <main class="col-md-10 p-5">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold">Welcome, <?php echo h($_SESSION['username']); ?>!</h2>
                    <p class="text-muted">Here's what's happening today at Shelton Tool-Hire.</p>
                </div>
                <div class="text-muted">
                    <i class="far fa-calendar-alt me-2"></i><?php echo date('D, M d Y'); ?>
                </div>
            </div>
            <!-- Stats -->
            <div class="row mb-5">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="small text-muted mb-2">Total Inventory</div>
                        <div class="h3 fw-bold m-0"><?php echo $totalTools; ?></div>
                        <div class="small text-muted mt-2">Active listings</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="small text-muted mb-2">Total Rentals</div>
                        <div class="h3 fw-bold m-0 text-primary"><?php echo $totalRentals; ?></div>
                        <div class="small text-success mt-2"><i class="fas fa-check-circle"></i> <?php echo $activeRentals; ?> active hires</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="small text-muted mb-2">Avg. Tool Rating</div>
                        <div class="h3 fw-bold m-0"><?php echo number_format($avgRating, 1); ?> <i class="fas fa-star text-warning small"></i></div>
                        <div class="small text-muted mt-2">Based on <?php echo $approvedReviews; ?> reviews</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="small text-muted mb-2">Pending Reviews</div>
                        <div class="h3 fw-bold m-0 text-warning"><?php echo $pendingReviews; ?></div>
                        <div class="small text-muted mt-2">Requires moderation</div>
                    </div>
                </div>
            </div>
            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold m-0">Review Moderation Queue</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4">Tool</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reviews)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No pending reviews. Good job!</td></tr>
                            <?php else: ?>
                                <?php foreach ($reviews as $rev): ?>
                                <tr>
                                    <td class="px-4 fw-bold"><?php echo h($rev['tool_name']); ?></td>
                                    <td><?php echo h($rev['username'] ?: 'Guest'); ?></td>
                                    <td><?php echo renderStars($rev['overall_rating']); ?></td>
                                    <td class="text-truncate" style="max-width: 200px;"><?php echo h($rev['comment']); ?></td>
                                    <td>
                                        <a href="moderate-reviews.php?id=<?php echo $rev['id']; ?>&action=approve" class="btn btn-success btn-sm rounded-pill px-3 me-2">Approve</a>
                                        <a href="moderate-reviews.php?id=<?php echo $rev['id']; ?>&action=reject" class="btn btn-outline-danger btn-sm rounded-pill px-3">Reject</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 p-3 text-center">
                    <a href="moderate-reviews.php" class="text-primary small text-decoration-none fw-bold">View All Reviews</a>
                </div>
            </div>
        </main>
    </div>
</div>
<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
