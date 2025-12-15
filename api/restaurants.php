<?php
require_once '../db/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        handle_create();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function handle_create() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['name']) || empty($data['address']) || empty($data['phone']) || empty($data['email'])) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        return;
    }

    try {
        $pdo = db();
        $sql = "INSERT INTO restaurants (name, address, phone, email) VALUES (:name, :address, :phone, :email)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':address' => $data['address'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
