<?php
require_once 'includes/header.php';
require_once 'db/config.php';

$restaurants = [];
$cuisines = [];
try {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name, cuisine, address, phone, email FROM restaurants ORDER BY name ASC");
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT DISTINCT cuisine FROM restaurants WHERE cuisine IS NOT NULL AND cuisine != '' ORDER BY cuisine ASC");
    $cuisines = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

?>
<header class="hero">
    <div class="container">
        <h1 class="display-4">Find Your Next Meal</h1>
        <p class="lead">Browse through our collection of partner restaurants.</p>
    </div>
</header>

<main class="container my-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by restaurant name...">
            </div>
            <div class="col-md-4">
                <select id="cuisineFilter" class="form-select">
                    <option value="">All Cuisines</option>
                    <?php foreach ($cuisines as $cuisine): ?>
                        <option value="<?= htmlspecialchars($cuisine) ?>"><?= htmlspecialchars($cuisine) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row" id="restaurantList">
            <?php if (empty($restaurants)): ?>
                <div class="col">
                    <p class="text-center text-muted">No restaurants are available at the moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($restaurants as $restaurant): ?>
                    <div class="col-md-4 mb-4 restaurant-item" data-name="<?= htmlspecialchars(strtolower($restaurant['name'])) ?>" data-cuisine="<?= htmlspecialchars(strtolower($restaurant['cuisine'])) ?>">
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
        <div id="noResults" class="text-center text-muted" style="display: none;">
            <p>No restaurants match your search.</p>
        </div>
    </main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const cuisineFilter = document.getElementById('cuisineFilter');
        const restaurantList = document.getElementById('restaurantList');
        const restaurantItems = restaurantList.querySelectorAll('.restaurant-item');
        const noResults = document.getElementById('noResults');

        function filterRestaurants() {
            const searchTerm = searchInput.value.toLowerCase();
            const cuisineTerm = cuisineFilter.value.toLowerCase();
            let resultsFound = false;

            restaurantItems.forEach(item => {
                const name = item.dataset.name;
                const cuisine = item.dataset.cuisine;

                const nameMatch = name.includes(searchTerm);
                const cuisineMatch = cuisineTerm === '' || cuisine.includes(cuisineTerm);

                if (nameMatch && cuisineMatch) {
                    item.style.display = '';
                    resultsFound = true;
                } else {
                    item.style.display = 'none';
                }
            });

            noResults.style.display = resultsFound ? 'none' : '';
        }

        searchInput.addEventListener('input', filterRestaurants);
        cuisineFilter.addEventListener('change', filterRestaurants);
    });
</script>
<?php require_once 'includes/footer.php'; ?>
