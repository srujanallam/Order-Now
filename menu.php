<?php
require_once 'db/config.php';

if (!isset($_GET['restaurant_id']) || !is_numeric($_GET['restaurant_id'])) {
    die("A valid restaurant ID is required.");
}
$restaurant_id = intval($_GET['restaurant_id']);

// Fetch restaurant details
$restaurant = null;
try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT name, address, phone FROM restaurants WHERE id = :id");
    $stmt->execute(['id' => $restaurant_id]);
    $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB error fetching restaurant: " . $e->getMessage());
}

if (!$restaurant) {
    die("Restaurant not found.");
}

// Fetch menu items
$menu_items = [];
try {
    $stmt = $pdo->prepare("SELECT name, description, price, category FROM menu_items WHERE restaurant_id = :restaurant_id ORDER BY category, name");
    $stmt->execute(['restaurant_id' => $restaurant_id]);
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB error fetching menu items: " . $e->getMessage());
}

// Group menu items by category
$menu_by_category = [];
foreach ($menu_items as $item) {
    $category = $item['category'] ?: 'Uncategorized';
    $menu_by_category[$category][] = $item;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu for <?= htmlspecialchars($restaurant['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F8F9FA; }
        .menu-header {
            background: #343A40;
            color: white;
            padding: 3rem 0;
        }
        .menu-item {
            border-bottom: 1px dashed #E0E0E0;
            padding: 1rem 0;
        }
        .menu-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>

    <div class="menu-header text-center">
        <div class="container">
            <h1 class="display-5"><?= htmlspecialchars($restaurant['name']) ?></h1>
            <p class="lead"><?= htmlspecialchars($restaurant['address']) ?></p>
            <?php if ($restaurant['phone']): ?>
            <p class="text-white-50">Call us at: <?= htmlspecialchars($restaurant['phone']) ?></p>
            <?php endif; ?>
            <a href="index.php" class="btn btn-sm btn-outline-light mt-3"><i class="bi bi-arrow-left"></i> Back to all restaurants</a>
        </div>
    </div>

    <main class="container my-5">
        <?php if (empty($menu_by_category)): ?>
            <div class="text-center">
                <p class="text-muted fs-4">This restaurant hasn't added any menu items yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($menu_by_category as $category => $items): ?>
                <div class="mb-5">
                    <h2 class="mb-4"><?= htmlspecialchars($category) ?></h2>
                    <?php foreach ($items as $item): ?>
                        <div class="row menu-item">
                            <div class="col-8">
                                <h5 class="mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                                <p class="text-muted mb-0"><?= htmlspecialchars($item['description']) ?></p>
                            </div>
                            <div class="col-4 text-end">
                                <p class="fw-bold fs-5">$<?= htmlspecialchars(number_format((float)$item['price'], 2)) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <footer class="text-center text-muted py-4">
        <p>&copy; <?= date('Y') ?> Food Marketplace</p>
    </footer>

</body>
</html>
