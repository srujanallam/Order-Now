<?php
require_once 'includes/header.php';
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$favorite_restaurants = [];

try {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT r.id, r.name, r.cuisine, r.address
        FROM restaurants r
        JOIN favorite_restaurants fr ON r.id = fr.restaurant_id
        WHERE fr.user_id = ?
        ORDER BY r.name ASC
    ");
    $stmt->execute([$user_id]);
    $favorite_restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching favorites: " . $e->getMessage());
    // Optionally, show a friendly error to the user
}

?>

<div class="container my-5">
    <h1 class="mb-4">My Favorite Restaurants</h1>
    
    <div class="row">
        <?php if (empty($favorite_restaurants)): ?>
            <div class="col">
                <p class="text-center text-muted">You haven't added any favorite restaurants yet.</p>
                <div class="text-center">
                    <a href="index.php" class="btn btn-primary">Find some restaurants</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($favorite_restaurants as $restaurant): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 restaurant-card">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($restaurant['name']) ?></h5>
                            <p class="card-text"><span class="badge bg-secondary"><?= htmlspecialchars($restaurant['cuisine']) ?></span></p>
                            <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars($restaurant['address']) ?></p>
                            <a href="menu.php?restaurant_id=<?= $restaurant['id'] ?>" class="btn btn-primary mt-auto">View Menu</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
