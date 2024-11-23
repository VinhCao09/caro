<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'caro_game');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối cơ sở dữ liệu thất bại']);
    exit();
}

// Lấy dữ liệu từ yêu cầu POST
$data = json_decode(file_get_contents('php://input'), true);
$room_id = $data['room_id'];
$player = $data['player'];
$cell = $data['cell'];

// Kiểm tra tính hợp lệ của thông tin
if (!isset($room_id, $player, $cell)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết']);
    exit();
}

// Lấy thông tin game của phòng
$result = $conn->query("SELECT * FROM games WHERE room_id = $room_id");
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Phòng không tồn tại']);
    exit();
}

$game = $result->fetch_assoc();
$board = $game['board'];
$current_turn = $game['current_turn'];

// Kiểm tra xem có phải lượt của người chơi hiện tại không
if ($player !== $current_turn) {
    echo json_encode(['success' => false, 'message' => 'Không phải lượt của bạn']);
    exit();
}

// Kiểm tra ô đã được đánh chưa
if ($board[$cell] !== '-') {
    echo json_encode(['success' => false, 'message' => 'Ô đã được đánh']);
    exit();
}

// Cập nhật ô cờ và chuyển lượt
$board[$cell] = $player;
$new_turn = ($player === 'X') ? 'O' : 'X';

// Kiểm tra chiến thắng
function checkWinner($board, $player) {
    $size = 15; // Bàn cờ 15x15
    $win_condition = 5; // 5 ô liên tiếp

    // Chuyển bảng từ chuỗi thành mảng 2D
    $board_array = str_split($board);
    $grid = array_chunk($board_array, $size);

    // Kiểm tra theo hàng, cột, đường chéo
    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            if (
                checkDirection($grid, $i, $j, 0, 1, $player, $win_condition) || // Hàng ngang
                checkDirection($grid, $i, $j, 1, 0, $player, $win_condition) || // Hàng dọc
                checkDirection($grid, $i, $j, 1, 1, $player, $win_condition) || // Chéo xuống
                checkDirection($grid, $i, $j, 1, -1, $player, $win_condition)   // Chéo lên
            ) {
                return true;
            }
        }
    }
    return false;
}

// Kiểm tra một hướng nhất định
function checkDirection($grid, $x, $y, $dx, $dy, $player, $win_condition) {
    $count = 0;
    for ($k = 0; $k < $win_condition; $k++) {
        $nx = $x + $k * $dx;
        $ny = $y + $k * $dy;
        if (isset($grid[$nx][$ny]) && $grid[$nx][$ny] === $player) {
            $count++;
        } else {
            break;
        }
    }
    return $count === $win_condition;
}

// Cập nhật trạng thái game
$winner = checkWinner($board, $player);
if ($winner) {
    $conn->query("UPDATE games SET board = '$board', current_turn = NULL WHERE id = {$game['id']}");
    echo json_encode(['success' => true, 'winner' => $player, 'message' => 'Người chơi ' . $player . ' đã thắng!']);
    exit();
}

// Cập nhật bảng và chuyển lượt
$conn->query("UPDATE games SET board = '$board', current_turn = '$new_turn' WHERE id = {$game['id']}");

echo json_encode(['success' => true, 'board' => $board, 'current_turn' => $new_turn]);
?>
