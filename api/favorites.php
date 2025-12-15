<?php
session_start();
header('Content-Type: application/json');

require_once '../db/config.php';

$response = ['success' => false, 'loggedIn' => false, 'isFavorite' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to favorite a restaurant.';
    echo json_encode($response);
    exit;
}

$response['loggedIn'] = true;
$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$restaurant_id = $data['restaurant_id'] ?? null;

if (!$restaurant_id || !is_numeric($restaurant_id)) {
    $response['message'] = 'Invalid restaurant ID.';
    echo json_encode($response);
    exit;
}

$pdo = db();

// Check if it's already a favorite
$stmt = $pdo->prepare("SELECT id FROM favorite_restaurants WHERE user_id = ? AND restaurant_id = ?");
$stmt->execute([$user_id, $restaurant_id]);
$existing_favorite = $stmt->fetch();

if ($existing_favorite) {
    // Remove from favorites
    $stmt = $pdo->prepare("DELETE FROM favorite_restaurants WHERE id = ?");
    if ($stmt->execute([$existing_favorite['id']])) {
        $response['success'] = true;
        $response['isFavorite'] = false;
        $response['message'] = 'Restaurant removed from favorites.';
    } else {
        $response['message'] = 'Failed to remove from favorites.';
    }
} else {
    // Add to favorites
    $stmt = $pdo->prepare("INSERT INTO favorite_restaurants (user_id, restaurant_id) VALUES (?, ?)");
    if ($stmt->execute([$user_id, $restaurant_id])) {
        $response['success'] = true;
        $response['isFavorite'] = true;
        $response['message'] = 'Restaurant added to favorites.';
    } else {
        $response['message'] = 'Failed to add to favorites.';
    }
}

echo json_encode($response);
