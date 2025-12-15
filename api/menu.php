<?php
require_once '../db/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handle_get();
        break;
    case 'POST':
        handle_post();
        break;
    case 'PUT':
        handle_put();
        break;
    case 'DELETE':
        handle_delete();
        break;
    default:
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
        break;
}

function handle_get() {
    if (empty($_GET['restaurant_id'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'error' => 'Restaurant ID is required.']);
        return;
    }
    $restaurant_id = $_GET['restaurant_id'];

    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, name, description, price, category FROM menu_items WHERE restaurant_id = :restaurant_id ORDER BY category, name");
        $stmt->execute([':restaurant_id' => $restaurant_id]);
        $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $menu_items]);
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handle_post() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['restaurant_id']) || empty($data['name']) || !isset($data['price'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'error' => 'Restaurant ID, name, and price are required.']);
        return;
    }

    try {
        $pdo = db();
        $sql = "INSERT INTO menu_items (restaurant_id, name, description, price, category) VALUES (:restaurant_id, :name, :description, :price, :category)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':restaurant_id' => $data['restaurant_id'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':category' => $data['category'] ?? null,
        ]);

        $lastInsertId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = :id");
        $stmt->execute(['id' => $lastInsertId]);
        $newItem = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $newItem]);
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handle_put() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['name']) || !isset($data['price'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'error' => 'All fields including ID are required.']);
        return;
    }

    try {
        $pdo = db();
        $sql = "UPDATE menu_items SET name = :name, description = :description, price = :price, category = :category WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':category' => $data['category'] ?? null,
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handle_delete() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'error' => 'Menu item ID is required.']);
        return;
    }

    try {
        $pdo = db();
        $sql = "DELETE FROM menu_items WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $data['id']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['success' => false, 'error' => 'Menu item not found.']);
        }
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
