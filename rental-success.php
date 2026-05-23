<?php
require_once 'includes/header.php';
$rental_id = $_GET['id'] ?? null;
if (!$rental_id) {
    header('Location: index.php');
    exit;
}
$stmt = $pdo->prepare("SELECT r.*, t.name as tool_name FROM rentals r 
                       JOIN tools t ON r.tool_id = t.id 
                       WHERE r.id = ?");
$stmt->execute([$rental_id]);
$rental = $stmt->fetch();
if (!$rental) {
    die("Rental record not found.");
}
?>
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle fa-5x text-success"></i>
                </div>
                <h2 class="fw-bold mb-3">Rental Request Received!</h2>
                <p class="text-muted mb-4">
                    Your request to hire the <strong><?php echo h($rental['tool_name']); ?></strong> has been logged. 
                    Our team will review your request and contact you shortly for confirmation and payment details.
                </p>
                <div class="bg-light p-3 rounded-3 mb-4 text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted">Rental ID:</span>
                        <span class="small fw-bold">#<?php echo str_pad($rental['id'], 5, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted">Estimated Cost:</span>
                        <span class="small fw-bold">$<?php echo h($rental['total_cost']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="small text-muted">Period:</span>
                        <span class="small fw-bold"><?php echo date('M d, H:i', strtotime($rental['start_date'])); ?> - <?php echo date('M d, H:i', strtotime($rental['end_date'])); ?></span>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <a href="catalogue.php" class="btn btn-primary rounded-pill">Browse More Tools</a>
                    <a href="index.php" class="btn btn-light rounded-pill">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
