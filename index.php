<?php
require_once 'includes/header.php';
$stmt = $pdo->prepare("SELECT t.*, c.name as category_name FROM tools t 
                       JOIN categories c ON t.category_id = c.id 
                       WHERE t.featured = 1 LIMIT 3");
$stmt->execute();
$featuredTools = $stmt->fetchAll();
if (empty($featuredTools)) {
    $stmt = $pdo->prepare("SELECT t.*, c.name as category_name FROM tools t 
                           JOIN categories c ON t.category_id = c.id LIMIT 3");
    $stmt->execute();
    $featuredTools = $stmt->fetchAll();
}
?>
<section class="hero">
    <div class="container">
        <h1>Work Harder, Rent Smarter.</h1>
        <p class="lead opacity-75">Premium tool hire for construction, landscaping, and industrial projects.</p>
        <form action="catalogue.php" method="GET" class="search-container">
            <input type="text" name="search" placeholder="What are you looking for? (e.g. Drill, Ladder...)">
            <button type="submit">Search</button>
        </form>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <span class="badge bg-secondary py-2 px-3">Over 500+ Tools</span>
            <span class="badge bg-secondary py-2 px-3">Expert Support</span>
            <span class="badge bg-secondary py-2 px-3">Next Day Delivery</span>
        </div>
    </div>
</section>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-0">Featured Equipment</h2>
            <p class="text-muted">Our most popular rentals this month.</p>
        </div>
        <a href="catalogue.php" class="btn btn-outline-primary rounded-pill">View All Catalogue</a>
    </div>
    <div class="row">
        <?php foreach ($featuredTools as $tool): ?>
        <div class="col-md-4">
            <div class="tool-card">
                <div class="tool-img" style="background-image: url('assets/images/<?php echo h($tool['image_path']); ?>'); background-color: #eee;">
                    <?php if (!$tool['image_path']): ?>
                        <div class="h-100 d-flex align-items-center justify-content-center text-muted">
                            <i class="fas fa-image fa-3x"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="tool-body">
                    <span class="badge bg-light text-primary mb-2"><?php echo h($tool['category_name']); ?></span>
                    <h5 class="fw-bold"><?php echo h($tool['name']); ?></h5>
                    <p class="text-muted small mb-3"><?php echo h(substr($tool['description'], 0, 80)); ?>...</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="price-tag">
                            $<?php echo h($tool['daily_price']); ?> <span class="fw-normal">/ day</span>
                        </div>
                        <a href="tool-detail.php?id=<?php echo $tool['id']; ?>" class="btn btn-secondary btn-sm">Details</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <section class="mt-5 p-5 bg-white rounded-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="fw-bold">Why Choose Shelton?</h3>
                <p class="text-muted">We don't just rent tools; we provide solutions. Our team of experts ensures every piece of equipment is maintained to the highest standards.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check-circle text-success me-2"></i> Safety Inspected & Certified</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i> Flexible Rental Periods</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i> 24/7 Technical Support</li>
                </ul>
            </div>
            <div class="col-md-6 text-center">
                <div class="p-4 bg-light rounded-circle d-inline-block">
                    <i class="fas fa-hard-hat fa-5x text-secondary"></i>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once 'includes/footer.php'; ?>
