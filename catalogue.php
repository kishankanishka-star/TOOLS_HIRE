<?php
require_once 'includes/header.php';
$category_id = $_GET['category'] ?? null;
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$query = "SELECT t.*, c.name as category_name, 
          (SELECT AVG(overall_rating) FROM reviews WHERE tool_id = t.id AND status = 'approved') as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE tool_id = t.id AND status = 'approved') as review_count
          FROM tools t 
          JOIN categories c ON t.category_id = c.id 
          WHERE 1=1";
$params = [];
if ($category_id) {
    $query .= " AND t.category_id = :cat_id";
    $params['cat_id'] = $category_id;
}
if ($search) {
    $query .= " AND (t.name LIKE :search OR t.description LIKE :search)";
    $params['search'] = "%$search%";
}
switch ($sort) {
    case 'price_low': $query .= " ORDER BY t.daily_price ASC"; break;
    case 'price_high': $query .= " ORDER BY t.daily_price DESC"; break;
    case 'rating': $query .= " ORDER BY avg_rating DESC"; break;
    default: $query .= " ORDER BY t.created_at DESC";
}
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tools = $stmt->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<div class="container py-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <aside class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Categories</h5>
                    <div class="list-group list-group-flush">
                        <a href="catalogue.php" class="list-group-item list-group-item-action <?php echo !$category_id ? 'active' : ''; ?>">All Equipment</a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="catalogue.php?category=<?php echo $cat['id']; ?>" 
                               class="list-group-item list-group-item-action <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                                <?php echo h($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Sort By</h5>
                    <form action="catalogue.php" method="GET">
                        <?php if ($category_id): ?><input type="hidden" name="category" value="<?php echo $category_id; ?>"><?php endif; ?>
                        <?php if ($search): ?><input type="hidden" name="search" value="<?php echo h($search); ?>"><?php endif; ?>
                        <select name="sort" class="form-select mb-3" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Top Rated</option>
                        </select>
                    </form>
                </div>
            </div>
        </aside>
        <!-- Tool Grid -->
        <main class="col-md-9">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h2 class="fw-bold m-0">Equipment Catalogue</h2>
                <span class="text-muted"><?php echo count($tools); ?> tools found</span>
            </div>
            <?php if (empty($tools)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-light mb-3"></i>
                    <p class="text-muted">No tools found matching your criteria.</p>
                    <a href="catalogue.php" class="btn btn-secondary">Clear All Filters</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($tools as $tool): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="tool-card">
                            <div class="tool-img" style="background-image: url('assets/images/<?php echo h($tool['image_path']); ?>'); background-color: #eee;"></div>
                            <div class="tool-body">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small text-secondary"><?php echo h($tool['category_name']); ?></span>
                                    <span class="small text-warning">
                                        <i class="fas fa-star"></i> <?php echo number_format($tool['avg_rating'] ?: 0, 1); ?>
                                    </span>
                                </div>
                                <h6 class="fw-bold mb-3"><?php echo h($tool['name']); ?></h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="price-tag">
                                        $<?php echo h($tool['daily_price']); ?> <span class="fw-normal small">/ day</span>
                                    </div>
                                    <a href="tool-detail.php?id=<?php echo $tool['id']; ?>" class="btn btn-secondary btn-sm">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
