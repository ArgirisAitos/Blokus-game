<?php

require "lib/Board.php";

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


function Move($game_id,$request) {
    global $pdo;

    $player_name = $request['player_id'];
    $token = $request['token'];
    $piece = $request['piece'];
    $position = $request['position'];

    // τρέχουσα κατάσταση του παιχνιδιού
    $stmt = $pdo->prepare("SELECT board_state, player_turn FROM games WHERE id = :game_id");
    $stmt->execute(['game_id' => $game_id]);
    $game = $stmt->fetch();

    if (!$game) {
        echo json_encode(['error' => 'Game not found.']);
        return;
    }

    if ($game['player_turn'] != $player_name) {
        echo json_encode(['error' => 'Not your turn.']);
        return;
    }

    // το player_id από το όνομα του παίκτη
    $stmt = $pdo->prepare("SELECT id FROM players WHERE game_id = :game_id AND name = :player_name AND token = :token");
    $stmt->execute(['game_id' => $game_id, 'player_name' => $player_name, 'token' => $token]);
    $player_id = $stmt->fetchColumn();

    if (!$player_id) {
        echo json_encode(['error' => 'Player not found.']);
        return;
    }

    // διαθέσιμα σχήματα του παίκτη
    $stmt = $pdo->prepare("SELECT pieces_remaining FROM players WHERE game_id = :game_id AND id = :player_id");
    $stmt->execute(['game_id' => $game_id, 'player_id' => $player_id]);
    $player = $stmt->fetch();

    if (!$player) {
        echo json_encode(['error' => 'Player not found.']);
        return;
    }

    $available_shapes = json_decode($player['pieces_remaining'], true);

    // Έλεγχος αν το σχήμα είναι διαθέσιμο απο τον πίνακα
    $is_available = in_array($piece, $available_shapes, true);

    if (!$is_available) {
        echo json_encode(['error' => 'Shape not available.']);
        return;
    }

    $board_state = json_decode($game['board_state'], true);

    // Έλεγχος αν είναι η πρώτη τοποθέτηση του παίκτη
    $stmt = $pdo->prepare("SELECT COUNT(*) as moves FROM moves WHERE game_id = :game_id AND player_id = :player_id");
    $stmt->execute(['game_id' => $game_id, 'player_id' => $player_id]);
    $moves_count = $stmt->fetchColumn();

    if ($moves_count == 0) {
        // τοποθέτηση σε προκαθορισμένες γωνίες
        $starting_corners = StartingCorners($game_id);
        $expected_corner = $starting_corners[$player_id] ?? null;

        if (!$expected_corner) {
            echo json_encode(['error' => 'Invalid player ID.']);
            return;
        }

        $covers_corner = false;
        foreach ($piece as $dx => $row) {
            foreach ($row as $dy => $value) {
                if ($value === 1) {
                    $x = $position['x'] + $dx;
                    $y = $position['y'] + $dy;

                    if ($x == $expected_corner['x'] && $y == $expected_corner['y']) {
                        $covers_corner = true;
                        break 2;
                    }
                }
            }
        }

        if (!$covers_corner) {
            echo json_encode(['error' => 'Your first move must cover your starting corner.']);
            return;
        }
    } else {
        $placement_result = ValidPlacement($board_state, $piece, $position, $player_id);
        if ($placement_result !== true) {
            echo json_encode(['error' => $placement_result]);
            return;
        }
    }
    
    // Έλεγχος αν το κομμάτι τοποθετείται σε κατειλημμένη θέση
    foreach ($piece as $dx => $row) {
        foreach ($row as $dy => $value) {
            if ($value === 1) {
                $x = $position['x'] + $dx;
                $y = $position['y'] + $dy;
    
                if ($board_state[$x][$y] !== 0) {
                    echo json_encode(['error' => 'Position already occupied']);
                    return;
                }
            }
        }
    }
    

    $board_state = updateBoardState($board_state, $piece, $position, $player_id);

    // Ενημέρωση της κατάστασης του πίνακα στο παιχνίδι
    $stmt = $pdo->prepare("UPDATE games SET board_state = :board WHERE id = :game_id");
    $stmt->execute([
        'board' => json_encode($board_state),
        'game_id' => $game_id
    ]);

    // Αποθήκευση της κίνησης στον πίνακα moves
    $stmt = $pdo->prepare("INSERT INTO moves (game_id, player_id, piece, position) VALUES (:game_id, :player_id, :piece, :position)");
    $stmt->execute([
        'game_id' => $game_id,
        'player_id' => $player_id,
        'piece' => json_encode($piece),
        'position' => json_encode($position)
    ]);

    // Ενημέρωση της σειράς του επόμενου παίκτη
    $stmt = $pdo->prepare("SELECT name FROM players WHERE game_id = :game_id AND name != :current_player AND is_excluded = 0 ORDER BY id LIMIT 1");
    $stmt->execute(['game_id' => $game_id, 'current_player' => $player_name]);
    $next_player = $stmt->fetchColumn();

    if (!$next_player) {
        $stmt = $pdo->prepare("SELECT name FROM players WHERE game_id = :game_id AND is_excluded = 0 ORDER BY id LIMIT 1");
        $stmt->execute(['game_id' => $game_id]);
        $next_player = $stmt->fetchColumn();
    }

    $stmt = $pdo->prepare("UPDATE games SET player_turn = :next_player WHERE id = :game_id");
    $stmt->execute([
        'next_player' => $next_player,
        'game_id' => $game_id
    ]);

    // Ενημέρωση των διαθέσιμων σχημάτων του παίκτη
    $available_shapes = array_values(array_filter($available_shapes, fn($shape) => $shape !== $piece));
    $stmt = $pdo->prepare("UPDATE players SET pieces_remaining = :pieces_remaining WHERE game_id = :game_id AND id = :player_id");
    $stmt->execute([
        'pieces_remaining' => json_encode($available_shapes),
        'game_id' => $game_id,
        'player_id' => $player_id
    ]);

    //  αν ο παίκτης έχει τοποθετήσει όλα τα σχήματά του 
    if (empty($available_shapes)) { 
    endGame($game_id); }


    echo json_encode(['success' =>'Move completed successfully.']);

    
}

function StartingCorners($game_id) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id FROM players WHERE game_id = :game_id ORDER BY id");
    $stmt->execute(['game_id' => $game_id]);
    $players = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $starting_corners = [];
    $corners = [
        ['x' => 0, 'y' => 0] ,
        ['x' => 19, 'y' => 19],
    ];

    foreach ($players as $index => $player_id) {
        $starting_corners[$player_id] = $corners[$index];
    }

    return $starting_corners;
}








?>