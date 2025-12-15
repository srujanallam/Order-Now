<?php
require_once 'db/config.php';

$restaurants = [];
try {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name, address, phone, email FROM restaurants ORDER BY name ASC");
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // For a real app, you would log this error and show a user-friendly message.
    error_log("Database error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8F9FA;
        }
        .hero {
            background-color: #343A40;
            color: #FFFFFF;
            padding: 4rem 0;
            text-align: center;
        }
        .restaurant-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .restaurant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .btn-primary {
            background-color: #FF6347;
            border-color: #FF6347;
        }
        .btn-primary:hover {
            background-color: #E5533D;
            border-color: #E5533D;
        }
    </style>
</head>
<body>

    <header class="hero">
        <div class="container">
            <h1 class="display-4">Find Your Next Meal</h1>
            <p class="lead">Browse through our collection of partner restaurants.</p>
        </div>
    </header>

    <main class="container my-5">
        <div class="row">
            <?php if (empty($restaurants)): ?>
                <div class="col">
                    <p class="text-center text-muted">No restaurants are available at the moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($restaurants as $restaurant): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 restaurant-card">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($restaurant['name']) ?></h5>
                                <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars($restaurant['address']) ?></p>
                                <a href="menu.php?restaurant_id=<?= $restaurant['id'] ?>" class="btn btn-primary mt-auto">View Menu</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="text-center text-muted py-4">
        <p>&copy; <?= date('Y') ?> Food Marketplace</p>
    </footer>

</body>
</html>
