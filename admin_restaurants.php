<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Restaurants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #F8F9FA;
            color: #212529;
        }
        .btn-primary {
            background-color: #FF6347;
            border-color: #FF6347;
        }
        .btn-primary:hover {
            background-color: #E5533D;
            border-color: #E5533D;
        }
        .table {
            background-color: #FFFFFF;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .card {
            border-radius: 0.5rem;
        }
        .modal-content {
            border-radius: 0.5rem;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Restaurants</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#restaurantModal" id="addNewBtn">
                <i class="bi bi-plus-lg"></i> Add New Restaurant
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="restaurantsTableBody">
                        <!-- Restaurants will be loaded here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Restaurant Modal -->
    <div class="modal fade" id="restaurantModal" tabindex="-1" aria-labelledby="restaurantModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restaurantModalLabel">Add New Restaurant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="restaurantForm">
                        <input type="hidden" id="restaurantId" name="id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Restaurant Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Restaurant</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const restaurantModal = new bootstrap.Modal(document.getElementById('restaurantModal'));
            const restaurantForm = document.getElementById('restaurantForm');
            const restaurantModalLabel = document.getElementById('restaurantModalLabel');
            const tableBody = document.getElementById('restaurantsTableBody');
            
            // Fetch and display restaurants on page load
            fetchRestaurants();

            // Handle "Add New" button click
            document.getElementById('addNewBtn').addEventListener('click', function() {
                restaurantForm.reset();
                document.getElementById('restaurantId').value = '';
                restaurantModalLabel.textContent = 'Add New Restaurant';
            });

            // Handle form submission for both add and edit
            restaurantForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                const restaurantId = document.getElementById('restaurantId').value;

                const isEdit = restaurantId !== '';
                const url = isEdit ? `api/restaurants.php` : 'api/restaurants.php';
                const method = isEdit ? 'PUT' : 'POST';

                fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        restaurantModal.hide();
                        fetchRestaurants(); // Refresh the table
                    } else {
                        alert('Error: ' + result.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred.');
                });
            });

            function fetchRestaurants() {
                fetch('api/restaurants.php')
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            renderTable(result.data);
                        } else {
                            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">Could not load restaurants.</td></tr>`;
                        }
                    })
                    .catch(error => {
                         tableBody.innerHTML = `<tr><td colspan="6" class="text-center">Error loading restaurants.</td></tr>`;
                    });
            }

            function renderTable(restaurants) {
                tableBody.innerHTML = '';
                if (restaurants.length === 0) {
                     tableBody.innerHTML = `<tr><td colspan="6" class="text-center">No restaurants found.</td></tr>`;
                     return;
                }

                restaurants.forEach(r => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', r.id);
                    row.innerHTML = `
                        <td>${r.id}</td>
                        <td data-field="name">${r.name}</td>
                        <td data-field="address">${r.address}</td>
                        <td data-field="phone">${r.phone}</td>
                        <td data-field="email">${r.email}</td>
                        <td>
                            <a href="restaurant_menu.php?restaurant_id=${r.id}" class="btn btn-sm btn-info menu-btn" title="Manage Menu"><i class="bi bi-card-list"></i></a>
                            <button class="btn btn-sm btn-secondary edit-btn" title="Edit Restaurant"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-btn" title="Delete Restaurant"><i class="bi bi-trash"></i></button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
                
                // Add event listeners for the new buttons
                addEventListeners();
            }
            
            function addEventListeners() {
                // Edit button handler
                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const restaurantId = row.dataset.id;
                        
                        document.getElementById('restaurantId').value = restaurantId;
                        document.getElementById('name').value = row.querySelector('[data-field="name"]').textContent;
                        document.getElementById('address').value = row.querySelector('[data-field="address"]').textContent;
                        document.getElementById('phone').value = row.querySelector('[data-field="phone"]').textContent;
                        document.getElementById('email').value = row.querySelector('[data-field="email"]').textContent;
                        
                        restaurantModalLabel.textContent = 'Edit Restaurant';
                        restaurantModal.show();
                    });
                });

                // Delete button handler
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const restaurantId = row.dataset.id;

                        if (confirm('Are you sure you want to delete this restaurant?')) {
                            fetch(`api/restaurants.php`, {
                                method: 'DELETE',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: restaurantId })
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    row.remove();
                                } else {
                                    alert('Error: ' + result.error);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An unexpected error occurred.');
                            });
                        }
                    });
                });
            }
        });
    </script>

</body>
</html>
