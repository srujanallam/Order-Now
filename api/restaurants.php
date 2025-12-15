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
    try {
        $pdo = db();
        $stmt = $pdo->query("SELECT id, name, cuisine, address, phone, email FROM restaurants ORDER BY created_at DESC");
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $restaurants]);
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handle_post() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['name']) || empty($data['address']) || empty($data['phone']) || empty($data['email']) || empty($data['cuisine'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        return;
    }

    try {
        $pdo = db();
        $sql = "INSERT INTO restaurants (name, cuisine, address, phone, email) VALUES (:name, :cuisine, :address, :phone, :email)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':cuisine' => $data['cuisine'],
            ':address' => $data['address'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
        ]);

        $lastInsertId = $pdo->lastInsertId();
        
        // Fetch the created restaurant to return it
        $stmt = $pdo->prepare("SELECT id, name, cuisine, address, phone, email FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $lastInsertId]);
        $newRestaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $newRestaurant]);
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handle_put() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['name']) || empty($data['address']) || empty($data['phone']) || empty($data['email']) || empty($data['cuisine'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'error' => 'All fields including ID are required.']);
        return;
    }

    try {
        $pdo = db();
        $sql = "UPDATE restaurants SET name = :name, cuisine = :cuisine, address = :address, phone = :phone, email = :email WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':cuisine' => $data['cuisine'],
            ':address' => $data['address'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
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
        echo json_encode(['success' => false, 'error' => 'Restaurant ID is required.']);
        return;
    }

    try {
        $pdo = db();
        $sql = "DELETE FROM restaurants WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $data['id']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['success' => false, 'error' => 'Restaurant not found.']);
        }
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}