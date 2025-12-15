<?php
require_once 'includes/header.php';
require_once 'db/config.php';

if (!isset($_GET['restaurant_id']) || !is_numeric($_GET['restaurant_id'])) {
    // Redirect or show a generic error page
    header("Location: index.php?error=invalid_restaurant");
    exit;
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
    // Show a generic error page to the user
    die("Error: Could not load restaurant information.");
}

if (!$restaurant) {
    // Redirect or show a 404 page
    header("Location: index.php?error=not_found");
    exit;
}

$is_favorite = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM favorite_restaurants WHERE user_id = ? AND restaurant_id = ?");
    $stmt->execute([$_SESSION['user_id'], $restaurant_id]);
    if ($stmt->fetch()) {
        $is_favorite = true;
    }
}

// Fetch menu items
$menu_items = [];
try {
    $stmt = $pdo->prepare("SELECT name, description, price, category FROM menu_items WHERE restaurant_id = :restaurant_id ORDER BY category, name");
    $stmt->execute(['restaurant_id' => $restaurant_id]);
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB error fetching menu items: " . $e->getMessage());
    // It's okay to show the restaurant info even if menu fails to load
}

// Group menu items by category
$menu_by_category = [];
foreach ($menu_items as $item) {
    $category = $item['category'] ?: 'Uncategorized';
    $menu_by_category[$category][] = $item;
}

?>
<div class="menu-header text-center">
    <div class="container">
        <h1 class="display-5"><?= htmlspecialchars($restaurant['name']) ?></h1>
        <p class="lead"><?= htmlspecialchars($restaurant['address']) ?></p>
        <?php if ($restaurant['phone']): ?>
        <p class="text-white-50">Call us at: <?= htmlspecialchars($restaurant['phone']) ?></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])):
            $btn_class = $is_favorite ? 'btn-danger' : 'btn-outline-warning';
            $btn_text = $is_favorite ? '<i class="fas fa-heart-broken"></i> Unfavorite' : '<i class="fas fa-heart"></i> Favorite';
        ?>
            <button id="favoriteBtn" class="btn btn-lg <?= $btn_class ?> mt-3" data-restaurant-id="<?= $restaurant_id ?>">
                <?= $btn_text ?>
            </button>
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
<?php require_once 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.getElementById('favoriteBtn');
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            const restaurantId = this.dataset.restaurantId;
            
            fetch('api/favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ restaurant_id: restaurantId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.isFavorite) {
                        this.classList.remove('btn-outline-warning');
                        this.classList.add('btn-danger');
                        this.innerHTML = '<i class="fas fa-heart-broken"></i> Unfavorite';
                    } else {
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-outline-warning');
                        this.innerHTML = '<i class="fas fa-heart"></i> Favorite';
                    }
                } else {
                    alert(data.message || 'An error occurred.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
});
</script>

