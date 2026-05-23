<?php
require_once 'includes/header.php';
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: catalogue.php');
    exit;
}
$stmt = $pdo->prepare("SELECT t.*, c.name as category_name FROM tools t 
                       JOIN categories c ON t.category_id = c.id 
                       WHERE t.id = ?");
$stmt->execute([$id]);
$tool = $stmt->fetch();
if (!$tool) {
    die("Tool not found.");
}
$stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r 
                       LEFT JOIN users u ON r.user_id = u.id 
                       WHERE r.tool_id = ? AND r.status = 'approved' 
                       ORDER BY r.created_at DESC");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();
$avgRating = 0;
if (count($reviews) > 0) {
    $sum = array_sum(array_column($reviews, 'overall_rating'));
    $avgRating = $sum / count($reviews);
}
?>
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="catalogue.php">Catalogue</a></li>
            <li class="breadcrumb-item active"><?php echo h($tool['name']); ?></li>
        </ol>
    </nav>
    <div class="row">
        <!-- Tool Images & Info -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="row g-0">
                    <div class="col-md-6 bg-light d-flex align-items-center justify-content-center" style="min-height: 400px;">
                        <?php if ($tool['image_path']): ?>
                            <img src="assets/images/<?php echo h($tool['image_path']); ?>" class="img-fluid" alt="<?php echo h($tool['name']); ?>">
                        <?php else: ?>
                            <i class="fas fa-image fa-5x text-muted opacity-25"></i>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="card-body p-4">
                            <span class="badge bg-light text-primary mb-2"><?php echo h($tool['category_name']); ?></span>
                            <h2 class="fw-bold mb-3"><?php echo h($tool['name']); ?></h2>
                            <div class="d-flex align-items-center mb-4">
                                <div class="me-3">
                                    <?php echo renderStars($avgRating); ?>
                                </div>
                                <span class="text-muted small">(<?php echo count($reviews); ?> reviews)</span>
                            </div>
                            <div class="bg-light p-3 rounded-3 mb-4">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="small text-muted">Hourly</div>
                                        <div class="fw-bold">$<?php echo h($tool['hourly_price']); ?></div>
                                    </div>
                                    <div class="col-4 border-start border-end">
                                        <div class="small text-muted">Daily</div>
                                        <div class="fw-bold">$<?php echo h($tool['daily_price']); ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted">Weekly</div>
                                        <div class="fw-bold">$<?php echo h($tool['weekly_price']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted"><?php echo nl2br(h($tool['description'])); ?></p>
                            <div class="mt-4">
                                <?php if ($tool['availability_status'] == 'Available'): ?>
                                    <span class="badge bg-success-subtle text-success p-2 px-3 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> Available Now
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger p-2 px-3 rounded-pill">
                                        <i class="fas fa-times-circle me-1"></i> Currently Rented
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Reviews Section -->
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Customer Reviews</h4>
                    <button class="btn btn-primary rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        Write a Review
                    </button>
                </div>
                <?php if (empty($reviews)): ?>
                    <div class="p-5 bg-white rounded-4 text-center text-muted">
                        No reviews yet. Be the first to share your experience!
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item bg-white">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold m-0"><?php echo h($review['username'] ?: 'Anonymous'); ?></h6>
                                <span class="text-muted small"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div class="mb-2">
                                <?php echo renderStars($review['overall_rating']); ?>
                            </div>
                            <p class="mb-0"><?php echo h($review['comment']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- Rental Calculator Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-calculator me-2 text-secondary"></i>Hire Cost Calculator</h5>
                    <form id="calculatorForm">
                        <input type="hidden" id="hourly_rate" value="<?php echo $tool['hourly_price']; ?>">
                        <input type="hidden" id="daily_rate" value="<?php echo $tool['daily_price']; ?>">
                        <input type="hidden" id="weekly_rate" value="<?php echo $tool['weekly_price']; ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Start Date & Time</label>
                            <input type="datetime-local" id="startDate" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">End Date & Time</label>
                            <input type="datetime-local" id="endDate" class="form-control" required>
                        </div>
                        <div id="calcResults" class="p-3 bg-light rounded-3 d-none mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">Duration:</span>
                                <span id="resDuration" class="small fw-bold">--</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total Estimate:</span>
                                <span id="resTotal" class="h4 fw-bold text-primary mb-0">$0.00</span>
                            </div>
                        </div>
                        <button type="button" onclick="calculateCost()" class="btn btn-secondary w-100 rounded-pill py-2 mb-3">
                            Calculate Cost
                        </button>
                    </form>
                        <div id="rentButtonContainer" class="d-none">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="process-rental.php" method="POST">
                                    <input type="hidden" name="tool_id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="start_date" id="formStartDate">
                                    <input type="hidden" name="end_date" id="formEndDate">
                                    <input type="hidden" name="total_cost" id="formTotalCost">
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                                        Rent Now <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                                    Login to Rent <i class="fas fa-sign-in-alt ms-2"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="submit-review.php" method="POST" class="modal-content rounded-4 border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Share Your Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="tool_id" value="<?php echo $id; ?>">
                <div class="mb-3 text-center">
                    <label class="form-label d-block fw-bold">Your Overall Rating</label>
                    <div class="h3 text-warning">
                        <i class="far fa-star rating-star" data-rating="1"></i>
                        <i class="far fa-star rating-star" data-rating="2"></i>
                        <i class="far fa-star rating-star" data-rating="3"></i>
                        <i class="far fa-star rating-star" data-rating="4"></i>
                        <i class="far fa-star rating-star" data-rating="5"></i>
                    </div>
                    <input type="hidden" name="overall_rating" id="ratingInput" value="5" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Detailed Review</label>
                    <textarea name="comment" class="form-control" rows="4" placeholder="How was the equipment? Was the service good?" required></textarea>
                </div>
                <p class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i> Your review will be visible once approved by our team.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Review</button>
            </div>
        </form>
    </div>
</div>
<script>
function calculateCost() {
    const start = new Date(document.getElementById('startDate').value);
    const end = new Date(document.getElementById('endDate').value);
    if (isNaN(start) || isNaN(end) || end <= start) {
        alert('Please select a valid date range.');
        return;
    }
    const diff = end - start;
    const totalHours = Math.ceil(diff / (1000 * 60 * 60));
    const h_rate = parseFloat(document.getElementById('hourly_rate').value);
    const d_rate = parseFloat(document.getElementById('daily_rate').value);
    const w_rate = parseFloat(document.getElementById('weekly_rate').value);
    let weeks = Math.floor(totalHours / 168);
    let rem = totalHours % 168;
    let days = Math.floor(rem / 24);
    let hours = rem % 24;
    let total = (weeks * w_rate) + (days * d_rate) + (hours * h_rate);
    document.getElementById('resDuration').innerText = `${weeks}w ${days}d ${hours}h`;
    document.getElementById('resTotal').innerText = `$${total.toFixed(2)}`;
    document.getElementById('calcResults').classList.remove('d-none');
    document.getElementById('formStartDate').value = document.getElementById('startDate').value;
    document.getElementById('formEndDate').value = document.getElementById('endDate').value;
    document.getElementById('formTotalCost').value = total.toFixed(2);
    document.getElementById('rentButtonContainer').classList.remove('d-none');
}
document.querySelectorAll('.rating-star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.getAttribute('data-rating');
        document.getElementById('ratingInput').value = rating;
        document.querySelectorAll('.rating-star').forEach(s => {
            if (s.getAttribute('data-rating') <= rating) {
                s.classList.replace('far', 'fas');
            } else {
                s.classList.replace('fas', 'far');
            }
        });
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>
