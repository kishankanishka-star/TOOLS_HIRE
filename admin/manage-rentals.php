<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
if (isset($_GET['id']) && isset($_GET['status'])) {
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
        header('Location: manage-rentals.php?msg=Rental status and tool availability updated.');
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
    exit;
}
$rentals = $pdo->query("SELECT r.*, t.name as tool_name, u.username 
                        FROM rentals r 
                        JOIN tools t ON r.tool_id = t.id 
                        LEFT JOIN users u ON r.user_id = u.id 
                        ORDER BY r.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rentals | Shelton Admin</title>
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
            <a href="manage-rentals.php" class="admin-nav-link active"><i class="fas fa-receipt me-2"></i> Rentals</a>
            <a href="moderate-reviews.php" class="admin-nav-link"><i class="fas fa-comments me-2"></i> Reviews</a>
            <div class="mt-auto p-4">
                <a href="../logout.php" class="btn btn-outline-light btn-sm w-100">Logout</a>
            </div>
        </nav>
        <main class="col-md-10 p-5">
            <h2 class="fw-bold mb-4">Rental Management</h2>
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                    <?php echo h($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4">ID</th>
                                <th>Tool</th>
                                <th>Customer</th>
                                <th>Period</th>
                                <th>Total Cost</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rentals as $r): ?>
                            <tr>
                                <td class="px-4 text-muted small">#<?php echo str_pad($r['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td class="fw-bold"><?php echo h($r['tool_name']); ?></td>
                                <td><?php echo h($r['username'] ?: 'Guest'); ?></td>
                                <td class="small">
                                    <?php echo date('M d, H:i', strtotime($r['start_date'])); ?> - 
                                    <?php echo date('M d, H:i', strtotime($r['end_date'])); ?>
                                </td>
                                <td class="fw-bold">$<?php echo h($r['total_cost']); ?></td>
                                <td>
                                    <?php 
                                    $badgeClass = 'bg-secondary';
                                    if ($r['status'] === 'confirmed') $badgeClass = 'bg-primary';
                                    if ($r['status'] === 'completed') $badgeClass = 'bg-success';
                                    if ($r['status'] === 'cancelled') $badgeClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($r['status']); ?></span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Update
                                        </button>
                                        <ul class="dropdown-menu border-0 shadow rounded-3">
                                            <li><a class="dropdown-item" href="?id=<?php echo $r['id']; ?>&status=confirmed">Confirm</a></li>
                                            <li><a class="dropdown-item" href="?id=<?php echo $r['id']; ?>&status=completed">Complete</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="?id=<?php echo $r['id']; ?>&status=cancelled">Cancel</a></li>
                                        </ul>
                                    </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
