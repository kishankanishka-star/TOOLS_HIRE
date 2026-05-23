<?php
require_once 'includes/header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT r.*, t.name as tool_name, t.image_path FROM rentals r 
                       JOIN tools t ON r.tool_id = t.id 
                       WHERE r.user_id = ? 
                       ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$rentals = $stmt->fetchAll();
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0">My Rental History</h2>
        <a href="catalogue.php" class="btn btn-outline-secondary rounded-pill btn-sm">Rent More</a>
    </div>
    <?php if (empty($rentals)): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <i class="fas fa-history fa-4x text-light mb-3"></i>
            <p class="text-muted">You haven't rented any tools yet.</p>
            <a href="catalogue.php" class="btn btn-primary rounded-pill">Explore Catalogue</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($rentals as $r): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-2 bg-light p-3 text-center">
                                <?php if ($r['image_path']): ?>
                                    <img src="assets/images/<?php echo h($r['image_path']); ?>" class="img-fluid rounded" alt="...">
                                <?php else: ?>
                                    <i class="fas fa-tools fa-3x text-muted opacity-25"></i>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 p-4">
                                <h5 class="fw-bold mb-1"><?php echo h($r['tool_name']); ?></h5>
                                <p class="text-muted small mb-0">ID: #<?php echo str_pad($r['id'], 5, '0', STR_PAD_LEFT); ?></p>
                            </div>
                            <div class="col-md-3 p-4 border-start border-end">
                                <div class="small text-muted mb-1">Period</div>
                                <div class="small fw-bold">
                                    <?php echo date('M d', strtotime($r['start_date'])); ?> - <?php echo date('M d', strtotime($r['end_date'])); ?>
                                </div>
                                <div class="badge bg-light text-dark mt-2"><?php echo ucfirst($r['status']); ?></div>
                            </div>
                            <div class="col-md-3 p-4 text-center">
                                <div class="h5 fw-bold mb-3">$<?php echo h($r['total_cost']); ?></div>
                                <a href="tool-detail.php?id=<?php echo $r['tool_id']; ?>#reviewModal" class="btn btn-secondary btn-sm rounded-pill px-4">
                                    Leave Review
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
