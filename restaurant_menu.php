<?php
require_once 'db/config.php';

// Get restaurant ID from URL
if (!isset($_GET['restaurant_id']) || !is_numeric($_GET['restaurant_id'])) {
    die("A valid restaurant ID is required.");
}
$restaurant_id = intval($_GET['restaurant_id']);

// Fetch restaurant details
$restaurant_name = 'Unknown Restaurant';
try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT name FROM restaurants WHERE id = :id");
    $stmt->execute(['id' => $restaurant_id]);
    $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($restaurant) {
        $restaurant_name = htmlspecialchars($restaurant['name']);
    } else {
        die("Restaurant not found.");
    }
} catch (PDOException $e) {
    die("Database error while fetching restaurant details.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu for <?php echo $restaurant_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F8F9FA; }
        .btn-primary { background-color: #4682B4; border-color: #4682B4; }
        .btn-primary:hover { background-color: #3A6A92; border-color: #3A6A92; }
        .table { background-color: #FFFFFF; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .card, .modal-content { border-radius: 0.5rem; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="admin_restaurants.php" class="btn btn-sm btn-outline-secondary mb-2"><i class="bi bi-arrow-left"></i> Back to Restaurants</a>
                <h1>Manage Menu</h1>
                <h5 class="text-muted">for <?php echo $restaurant_name; ?></h5>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menuItemModal" id="addNewBtn">
                <i class="bi bi-plus-lg"></i> Add New Item
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="menuItemsTableBody">
                        <!-- Menu items will be loaded here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Menu Item Modal -->
    <div class="modal fade" id="menuItemModal" tabindex="-1" aria-labelledby="menuItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="menuItemModalLabel">Add New Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="menuItemForm">
                        <input type="hidden" id="menuItemId" name="id">
                        <input type="hidden" id="restaurantId" name="restaurant_id" value="<?php echo $restaurant_id; ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" placeholder="e.g., Appetizer, Main, Dessert">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Item</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const restaurantId = <?php echo $restaurant_id; ?>;
            const menuItemModal = new bootstrap.Modal(document.getElementById('menuItemModal'));
            const menuItemForm = document.getElementById('menuItemForm');
            const menuItemModalLabel = document.getElementById('menuItemModalLabel');
            const tableBody = document.getElementById('menuItemsTableBody');
            
            fetchMenuItems();

            document.getElementById('addNewBtn').addEventListener('click', function() {
                menuItemForm.reset();
                document.getElementById('menuItemId').value = '';
                document.getElementById('restaurantId').value = restaurantId; // Ensure restaurantId is set on new items
                menuItemModalLabel.textContent = 'Add New Menu Item';
            });

            menuItemForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                const menuItemId = document.getElementById('menuItemId').value;

                const isEdit = menuItemId !== '';
                const method = isEdit ? 'PUT' : 'POST';

                fetch('api/menu.php', {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        menuItemModal.hide();
                        fetchMenuItems();
                    } else {
                        alert('Error: ' + result.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            });

            function fetchMenuItems() {
                fetch(`api/menu.php?restaurant_id=${restaurantId}`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            renderTable(result.data);
                        } else {
                            tableBody.innerHTML = `<tr><td colspan="5" class="text-center">Could not load menu items.</td></tr>`;
                        }
                    })
                    .catch(error => {
                        tableBody.innerHTML = `<tr><td colspan="5" class="text-center">Error loading menu items.</td></tr>`;
                    });
            }

            function renderTable(items) {
                tableBody.innerHTML = '';
                if (items.length === 0) {
                     tableBody.innerHTML = `<tr><td colspan="5" class="text-center">No menu items found. Add one to get started.</td></tr>`;
                     return;
                }

                items.forEach(item => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', item.id);
                    row.innerHTML = `
                        <td data-field="name">${item.name}</td>
                        <td data-field="description">${item.description || ''}</td>
                        <td data-field="price">${parseFloat(item.price).toFixed(2)}</td>
                        <td data-field="category">${item.category || ''}</td>
                        <td>
                            <button class="btn btn-sm btn-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
                
                addEventListeners();
            }
            
            function addEventListeners() {
                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const menuItemId = row.dataset.id;
                        
                        document.getElementById('menuItemId').value = menuItemId;
                        document.getElementById('name').value = row.querySelector('[data-field="name"]').textContent;
                        document.getElementById('description').value = row.querySelector('[data-field="description"]').textContent;
                        document.getElementById('price').value = row.querySelector('[data-field="price"]').textContent;
                        document.getElementById('category').value = row.querySelector('[data-field="category"]').textContent;
                        
                        menuItemModalLabel.textContent = 'Edit Menu Item';
                        menuItemModal.show();
                    });
                });

                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const menuItemId = row.dataset.id;

                        if (confirm('Are you sure you want to delete this menu item?')) {
                            fetch(`api/menu.php`, {
                                method: 'DELETE',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: menuItemId })
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    row.remove();
                                } else {
                                    alert('Error: ' + result.error);
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        }
                    });
                });
            }
        });
    </script>

</body>
</html>
