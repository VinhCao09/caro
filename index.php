<?php
$conn = new mysqli('localhost', 'root', '', 'caro_game');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_name = $_POST['room_name'];
    $conn->query("INSERT INTO rooms (room_name) VALUES ('$room_name')");
    $room_id = $conn->insert_id;

    // Khởi tạo game trong phòng
    $board = str_repeat('-', 225); // 15x15 ô
    $conn->query("INSERT INTO games (room_id, board, current_turn, player1) VALUES ($room_id, '$board', 'X', 'Player1')");
    header("Location: game.php?room_id=$room_id");
    exit();
}

// Lấy danh sách các phòng
$rooms = $conn->query("SELECT * FROM rooms");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Caro Game</title>
</head>
<body>
    <h1>Chọn Phòng</h1>
    <form method="POST">
        <input type="text" name="room_name" placeholder="Tên phòng mới" required>
        <button type="submit">Tạo Phòng</button>
    </form>

    <h2>Danh sách phòng</h2>
    <ul>
        <?php while ($room = $rooms->fetch_assoc()): ?>
            <li>
                <a href="game.php?room_id=<?= $room['id'] ?>">
                    <?= htmlspecialchars($room['room_name']) ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>