<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'caro_game');

$room_id = $_GET['room_id'];
$result = $conn->query("SELECT * FROM games WHERE room_id = $room_id");

if (!$result || $result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Game không tồn tại']);
    exit();
}

$game = $result->fetch_assoc();
echo json_encode([
    'success' => true,
    'board' => $game['board'],
    'current_turn' => $game['current_turn']
]);
?>
