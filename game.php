<?php
$conn = new mysqli('localhost', 'root', '', 'caro_game');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$room_id = $_GET['room_id'];
$game_result = $conn->query("SELECT * FROM games WHERE room_id = $room_id");
$game = $game_result->fetch_assoc();

if (!$game) {
    die("Phòng không tồn tại!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Caro - Room <?= $room_id ?></title>
    <style>
        .board {
            display: grid;
            grid-template-columns: repeat(15, 40px);
            grid-gap: 2px;
        }
        .cell {
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px;
            border: 1px solid #ccc;
            font-size: 24px;
            cursor: pointer;
        }
        .cell.taken {
            cursor: not-allowed;
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <h1>Phòng: <?= htmlspecialchars($room_id) ?></h1>
    <div id="game-board" class="board"></div>
    <p id="status"></p>

    <script>
        const roomId = <?= $room_id ?>;
        const currentPlayer = prompt("Nhập tên người chơi (X hoặc O):").toUpperCase();

        function renderBoard(board) {
            const gameBoard = document.getElementById('game-board');
            gameBoard.innerHTML = '';
            board.split('').forEach((cell, index) => {
                const cellDiv = document.createElement('div');
                cellDiv.classList.add('cell');
                if (cell !== '-') {
                    cellDiv.textContent = cell;
                    cellDiv.classList.add('taken');
                }
                cellDiv.addEventListener('click', () => makeMove(index));
                gameBoard.appendChild(cellDiv);
            });
        }

        function updateBoard() {
            fetch(`update_board.php?room_id=${roomId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderBoard(data.board);
                        document.getElementById('status').textContent = `Lượt chơi: ${data.current_turn}`;
                    }
                });
        }

        function makeMove(cellIndex) {
    fetch('make_move.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ room_id: roomId, player: currentPlayer, cell: cellIndex })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.winner) {
                alert(`Người chơi ${data.winner} đã thắng!`);
                if (data.play_again) {
                    if (confirm("Bạn có muốn chơi lại không?")) {
                        resetGame();
                    }
                }
            }
            updateBoard();
        } else {
            alert(data.message);
        }
    });
}

function resetGame() {
    fetch('reset_game.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ room_id: roomId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Trận đấu đã được khởi tạo lại!");
            updateBoard();  // Cập nhật lại bảng cờ sau khi reset
        }
    });
}

        updateBoard();
        setInterval(updateBoard, 1000); // Cập nhật bảng mỗi giây
    </script>
</body>
</html>
