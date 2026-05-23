<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM tools WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: manage-tools.php?msg=Tool deleted.');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $h_price = $_POST['hourly_price'];
    $d_price = $_POST['daily_price'];
    $w_price = $_POST['weekly_price'];
    $status = $_POST['availability_status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    if ($id) {
        $stmt = $pdo->prepare("UPDATE tools SET name=?, category_id=?, description=?, hourly_price=?, daily_price=?, weekly_price=?, availability_status=?, featured=? WHERE id=?");
        $stmt->execute([$name, $category_id, $description, $h_price, $d_price, $w_price, $status, $featured, $id]);
        $msg = "Tool updated.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tools (name, category_id, description, hourly_price, daily_price, weekly_price, availability_status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category_id, $description, $h_price, $d_price, $w_price, $status, $featured]);
        $msg = "Tool added.";
    }
    header("Location: manage-tools.php?msg=$msg");
    exit;
}
$tools = $pdo->query("SELECT t.*, c.name as category_name FROM tools t JOIN categories c ON t.category_id = c.id ORDER BY t.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$editTool = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tools WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editTool = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tools | Shelton Admin</title>
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
            <a href="manage-tools.php" class="admin-nav-link active"><i class="fas fa-tools me-2"></i> Manage Tools</a>
            <a href="moderate-reviews.php" class="admin-nav-link"><i class="fas fa-comments me-2"></i> Reviews</a>
            <div class="mt-auto p-4">
                <a href="../logout.php" class="btn btn-outline-light btn-sm w-100">Logout</a>
            </div>
        </nav>
        <main class="col-md-10 p-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0"><?php echo $editTool ? 'Edit Equipment' : 'Equipment Inventory'; ?></h2>
                <?php if (!$editTool): ?>
                    <button class="btn btn-primary rounded-pill" data-bs-toggle="collapse" data-bs-target="#toolForm">
                        <i class="fas fa-plus me-2"></i> Add New Tool
                    </button>
                <?php else: ?>
                    <a href="manage-tools.php" class="btn btn-light rounded-pill">Cancel Edit</a>
                <?php endif; ?>
            </div>
            <!-- Form (Add/Edit) -->
            <div class="collapse <?php echo $editTool ? 'show' : ''; ?> mb-5" id="toolForm">
                <div class="card border-0 shadow-sm rounded-4">
                    <form method="POST" class="card-body p-4">
                        <?php if ($editTool): ?>
                            <input type="hidden" name="id" value="<?php echo $editTool['id']; ?>">
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Equipment Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $editTool['name'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($editTool['category_id']) && $editTool['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo h($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="3" required><?php echo $editTool['description'] ?? ''; ?></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Hourly Rate ($)</label>
                                <input type="number" step="0.01" name="hourly_price" class="form-control" value="<?php echo $editTool['hourly_price'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Daily Rate ($)</label>
                                <input type="number" step="0.01" name="daily_price" class="form-control" value="<?php echo $editTool['daily_price'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Weekly Rate ($)</label>
                                <input type="number" step="0.01" name="weekly_price" class="form-control" value="<?php echo $editTool['weekly_price'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Status</label>
                                <select name="availability_status" class="form-select">
                                    <option value="Available" <?php echo (isset($editTool['availability_status']) && $editTool['availability_status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                    <option value="Rented" <?php echo (isset($editTool['availability_status']) && $editTool['availability_status'] == 'Rented') ? 'selected' : ''; ?>>Rented</option>
                                    <option value="Maintenance" <?php echo (isset($editTool['availability_status']) && $editTool['availability_status'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featSwitch" <?php echo (isset($editTool['featured']) && $editTool['featured']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label small fw-bold" for="featSwitch">Show in Featured Section on Homepage</label>
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-secondary px-5 rounded-pill">
                                    <?php echo $editTool ? 'Update Equipment' : 'Add to Inventory'; ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Table -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4">Tool</th>
                                <th>Category</th>
                                <th>Rates (H/D/W)</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tools as $t): ?>
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold"><?php echo h($t['name']); ?></div>
                                    <div class="text-muted x-small"><?php echo h(substr($t['description'], 0, 50)); ?>...</div>
                                </td>
                                <td><span class="badge bg-light text-dark"><?php echo h($t['category_name']); ?></span></td>
                                <td>
                                    <span class="small">$<?php echo $t['hourly_price']; ?> / $<?php echo $t['daily_price']; ?> / $<?php echo $t['weekly_price']; ?></span>
                                </td>
                                <td>
                                    <?php if ($t['availability_status'] == 'Available'): ?>
                                        <span class="text-success small"><i class="fas fa-check-circle me-1"></i> Available</span>
                                    <?php else: ?>
                                        <span class="text-danger small"><i class="fas fa-times-circle me-1"></i> <?php echo h($t['availability_status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($t['featured']): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $t['id']; ?>" class="btn btn-light btn-sm rounded-circle me-2"><i class="fas fa-edit text-primary"></i></a>
                                    <a href="?delete=<?php echo $t['id']; ?>" class="btn btn-light btn-sm rounded-circle" onclick="return confirm('Delete this tool?')"><i class="fas fa-trash text-danger"></i></a>
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
