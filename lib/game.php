<?php

session_start();
function generateToken() {
    return bin2hex(random_bytes(4)); 
}


function createNewGame($request) {
    global $pdo;

    $player_name = $request['player_id'] ?? null;
    if (!$player_name) {
        echo json_encode(['error' => 'Player name is required.']);
        return;
    }

    // Δημιουργία κενής κατάστασης board
    $board = array_fill(0, 20, array_fill(0, 20, 0));

    // Αποθήκευση παιχνιδιού στη βάση
    $stmt = $pdo->prepare("INSERT INTO games (board_state, player_turn, status) VALUES (:board, :player_name, 'in_progress')");
    $stmt->execute(['board' => json_encode($board), 'player_name' => $player_name]);

    $game_id = $pdo->lastInsertId();
    // Δημιουργία token για τον παίκτη
    $token = generateToken();
    // Ανάθεση σχημάτων στον πρώτο παίκτη
    initShapes($game_id, $player_name, $token);

    // Αποθήκευση session
    $_SESSION['player_id'] = $player_name;
    $_SESSION['game_id'] = $game_id;

    echo json_encode([
        'game_id' => $game_id,
        'player_id' => $player_name,
        'token' => $token, // Επιστροφή του token
        'message' => 'Game created, First player: start corner (0, 0). Waiting for second player.'
    ]);
}

function joinGame($game_id,$request) {
    global $pdo;

    $player_name = $request['player_id'] ?? null;

    // Δημιουργία token για τον παίκτη
    $token = generateToken();
    if (!$game_id || !$player_name) {
        echo json_encode(['error' => 'Game ID and Player name are required.']);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id AND status = 'in_progress'");
    $stmt->execute(['id' => $game_id]);
    $game = $stmt->fetch();

    if (!$game) {
        echo json_encode(['error' => 'Game not found or already started.']);
        return;
    }

    // Δημιουργία σχημάτων 
    initShapes($game_id, $player_name, $token);

    // Ενημέρωση κατάστασης 
    $stmt = $pdo->prepare("UPDATE games SET status = 'in_progress' WHERE id = :id");
    $stmt->execute(['id' => $game_id]);

    $_SESSION['player_id'] = $player_name;
    $_SESSION['game_id'] = $game_id;

    echo json_encode([
        'token' => $token,
        'player_id' => $player_name,
        'message' => ' Game started, Second player: start corner (19, 19), Player 1\'s turn.'
        
    ]);
}

?>