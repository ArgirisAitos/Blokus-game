    <?php

    require_once "lib/Board.php";

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

        // Αποθήκευση παιχνιδιού 
        $stmt = $pdo->prepare("INSERT INTO games (board_state, player_turn, status) VALUES (:board, :player_name, 'in_progress')");
        $stmt->execute(['board' => json_encode($board), 'player_name' => $player_name]);

        $game_id = $pdo->lastInsertId();
        // δημιουργία token 
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

        // τα διαθέσιμα σχήματα του παίκτη
        $stmt = $pdo->prepare("SELECT pieces_remaining FROM players WHERE game_id = :game_id AND id = :player_id");
        $stmt->execute(['game_id' => $game_id, 'player_id' => $player_id]);
        $player = $stmt->fetch();

        if (!$player) {
            echo json_encode(['error' => 'Player not found.']);
            return;
        }

        $available_shapes = json_decode($player['pieces_remaining'], true);

        // Ελεγχος αν το σχήμα είναι διαθέσιμο απο τον πίνακα
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

 function AvailableShapes($game_id,$request) {
        global $pdo;

       
        $player_id = $request['player_id'] ?? null;
        $token=$request['token'] ?? null;
        

        if (!$game_id || !$player_id || !$token) {
            echo json_encode(['error' => 'Game ID ,Player ID and token are required.']);
            return;
        }

        $stmt = $pdo->prepare("SELECT pieces_remaining FROM players WHERE game_id = :game_id AND name = :player_id");
        $stmt->execute(['game_id' => $game_id, 'player_id' => $player_id]);
        $player = $stmt->fetch();

        if ($player) {
            echo json_encode(['shapes' => json_decode($player['pieces_remaining'], true)]);
        } else {
            echo json_encode(['error' => 'Player not found in database.']);
        }
    }

    function initShapes($game_id, $player_name,$token) {
        global $pdo;

        $shapes = [
                [[1]], //1 τετράγωνο
                [[1, 1]], //2 τετράγωνα
                [[1, 1, 1]], //3 τετράγωνα
                [[1, 0], [1, 1]], //L-σχήμα
                [[1, 1, 1, 1]], //4 τετράγωνα 
                [[1, 1], [1, 1]], //τετράγωνο 2x2
                [[0, 1, 0], [1, 1, 1]], //T-σχήμα
                [[1, 1, 0], [0, 1, 1]],  // S-σχήμα
                [[1, 0], [1, 0], [1, 1]], // Γ-σχήμα
                [[1, 1, 1, 1, 1]], //5 τετράγωνα
                [[1, 0], [1, 0], [1, 0], [1, 1]], //Χ-σχήμα
                [[0, 1, 0], [1, 1, 1], [0, 1, 0]], // σταυρός σχήμα
                [[1, 1, 0], [0, 1, 1], [0, 0, 1]], //5 τετράγωνα πεντάγωνο Z-σχήμα
                [[1, 0, 1], [1, 1, 1]],            //L-σχήμα με 5
                [[1, 0, 0], [1, 0, 0], [1, 1, 1]], //Χ-σχήμα με 5
                [[1, 0, 0], [1, 1, 0], [0, 1, 1]], //Ζ-σχήμα με 5
                [[0, 1, 0], [1, 1, 1], [0, 1, 0]], //Π-σχήμα με 5
                [[0, 1, 1], [1, 1, 0], [0, 1, 0]], //Γ-σχήμα με 5 
                [[0, 1, 0, 0], [1, 1, 1, 1]], //Y-σχήμα με 4 
                [[1, 1], [1, 1], [1, 0]], // P-σχήμα με 4 
                [[1, 1, 0, 0], [0, 1, 1, 1]] //S-σχήμα με 4 
        ];

        $stmt = $pdo->prepare("INSERT INTO players (game_id, name, pieces_remaining,token) VALUES (:game_id, :name, :shapes,:token)");
        $stmt->execute([
            'game_id' => $game_id,
            'name' => $player_name,
            'shapes' => json_encode($shapes),
            'token' => $token
        ]);

        return $pdo->lastInsertId();
    }

    function removeShape($player_id, $used_shape) {
        global $pdo;

        $stmt = $pdo->prepare("SELECT pieces_remaining FROM players WHERE id = :player_id");
        $stmt->execute(['player_id' => $player_id]);
        $player = $stmt->fetch();

        $shapes = json_decode($player['pieces_remaining'], true);

        foreach ($shapes as $key => $shape) {
            if ($shape === $used_shape) {
                unset($shapes[$key]);
                break;
            }
        }

        $stmt = $pdo->prepare("UPDATE players SET pieces_remaining = :shapes WHERE id = :player_id");
        $stmt->execute(['shapes' => json_encode(array_values($shapes)), 'player_id' => $player_id]);
    }

    function ValidPlacement($board, $shape, $position, $player_id) {
        $rows = count($board);
        $cols = count($board[0]);
        $x_offset = $position['x'];
        $y_offset = $position['y'];

        $is_touching_corner = false;

        foreach ($shape as $dx => $row) {
            foreach ($row as $dy => $value) {
                if ($value === 1) {
                    $x = $x_offset + $dx;
                    $y = $y_offset + $dy;

                    // εκτός ορίων
                    if ($x < 0 || $x >= $rows || $y < 0 || $y >= $cols) {
                        return 'The shape is out of bounds';
                    }

                    // Έλεγχος αν καλύπτει ήδη κατειλημμένο μπλοκ
                    if ($board[$x][$y] !== 0) {
                        return 'Position already occupied';
                    }
                    
                    // Έλεγχος γωνιών (πρέπει να ακουμπά γωνία άλλου κομματιού του ίδιου παίκτη)
                    foreach ([[-1, -1], [-1, 1], [1, -1], [1, 1]] as $corner) {
                        $cx = $x + $corner[0];
                        $cy = $y + $corner[1];
                        if ($cx >= 0 && $cx < $rows && $cy >= 0 && $cy < $cols) {
                            if ($board[$cx][$cy] === $player_id) {
                                $is_touching_corner = true;
                                
                            }
                        }
                    }
                    

                    // Έλεγχος πλευρών (απαγορεύεται να ακουμπά πλευρά άλλου κομματιού του ίδιου παίκτη)
                    foreach ([[-1, 0], [1, 0], [0, -1], [0, 1]] as $side) {
                        $sx = $x + $side[0];
                        $sy = $y + $side[1];
                        if ($sx >= 0 && $sx < $rows && $sy >= 0 && $sy < $cols) {
                            if ($board[$sx][$sy] === $player_id) {
                                return 'The shape must not touch the side of another piece';
                            }
                        }
                    }

                    
            }
        }



        // Η τοποθέτηση είναι έγκυρη μόνο αν ακουμπά γωνία
        if (!$is_touching_corner) {
            return 'The shape must touch a corner of another piece';
    }
    }

        return true;
    }

    function passTurn($game_id,$request) {
        global $pdo;

        $player_name = $request['player_id'] ?? null;
        $token=$request['token'] ?? null ;

        if (!$game_id || !$player_name || !$token) {
            echo json_encode(['error' => 'Game ID and Player name and token are required.']);
            return;
        }

        // Έλεγχος αν είναι η σειρά του παίκτη
        $stmt = $pdo->prepare("SELECT player_turn, status FROM games WHERE id = :game_id");
        $stmt->execute(['game_id' => $game_id]);
        $game = $stmt->fetch();

        if (!$game) {
            echo json_encode(['error' => 'Game not found.']);
            return;
        }


        if ($game['player_turn'] != $player_name) {
            echo json_encode(['error' => 'Not your turn. Current turn: ' . $game['player_turn'] . ', Your name: ' . $player_name]);
            return;
        }

        //  ο παίκτης αποκλείεται
        $stmt = $pdo->prepare("UPDATE players SET is_excluded = 1 WHERE name = :player_name");
        $stmt->execute(['player_name' => $player_name]);

        // Έλεγχος αν μένουν άλλοι ενεργοί παίκτες
        $stmt = $pdo->prepare("SELECT COUNT(*) as active_players FROM players WHERE game_id = :game_id AND is_excluded = 0");
        $stmt->execute(['game_id' => $game_id]);
        $active_players = $stmt->fetchColumn();

        if ($active_players < 1) {
            // Αν δεν υπάρχουν ενεργοί παίκτες, τερματίζουμε το παιχνίδι
            endGame($game_id);
            return;
        }

        // Μετάβαση στον επόμενο παίκτη
        $stmt = $pdo->prepare("SELECT name FROM players WHERE game_id = :game_id AND is_excluded = 0 AND name != :player_name ORDER BY id LIMIT 1");
        $stmt->execute(['game_id' => $game_id, 'player_name' => $player_name]);
        $next_player = $stmt->fetchColumn();

        if (!$next_player) {
            // επιστρέφουμε στο πρώτο ενεργό
            $stmt = $pdo->prepare("SELECT name FROM players WHERE game_id = :game_id AND is_excluded = 0 ORDER BY id LIMIT 1");
            $stmt->execute(['game_id' => $game_id]);
            $next_player = $stmt->fetchColumn();
        }

        // Ενημέρωση σειράς
        $stmt = $pdo->prepare("UPDATE games SET player_turn = :next_player WHERE id = :game_id");
        $stmt->execute([
            'next_player' => $next_player,
            'game_id' => $game_id
        ]);

        echo json_encode(['success' => true, 'message' => 'You passed your turn and are now excluded from the game.']);
    }
        

    function checkGameStatus($game_id) {
        global $pdo;
    
        // Για playerturn
        $stmt = $pdo->prepare("SELECT  player_turn FROM games WHERE id = :game_id");
        $stmt->execute(['game_id' => $game_id]);
        $game = $stmt->fetch();
    
        // Έλεγχος για ενεργούς παίκτες
        $stmt = $pdo->prepare("SELECT name FROM players WHERE game_id = :game_id AND is_excluded = 0 ORDER BY id LIMIT 1");
        $stmt->execute(['game_id' => $game_id]);
        $next_player = $stmt->fetchColumn();
    
        // Αν δεν υπάρχουν ενεργοί παίκτες, τελείωσε το παιχνίδι
        if ($next_player === false) {
            endGame($game_id);
        } else {
            // Αν υπάρχουν ενεργοί παίκτες, εμφάνισε το μήνυμα "Game in progress"
            echo json_encode([
                'message' => 'Game in progress.',
                'player_turn' => $game['player_turn']
            ]);
        }
    }











    function endGame($game_id) {
        global $pdo;

        //  παίκτες και τα υπόλοιπα κομμάτια τους
        $stmt = $pdo->prepare("SELECT name, pieces_remaining FROM players WHERE game_id = :game_id");
        $stmt->execute(['game_id' => $game_id]);
        $players = $stmt->fetchAll();

        if (!$players) {
            echo json_encode(['error' => 'No players found.']);
            return;
        }

        // Υπολογισμός πόντων
        $scores = [];
        foreach ($players as $player) {
            $remaining_pieces = json_decode($player['pieces_remaining'], true);
            $score = 0;

            // Αφαίρεση πόντων για κάθε τετράγωνο που απομένει
            foreach ($remaining_pieces as $piece) {
                foreach ($piece as $row) {
                    $score -= array_sum($row); 
                }
            }

            // Έλεγχος αν έπαιξε όλα τα κομμάτια
            if (empty($remaining_pieces)) {
                $score += 15;
            
            }

            $scores[$player['name']] = $score;
        }

        // Για την εύρεση του νικητή 
        arsort($scores); 
        $winner = key($scores);

         
        $stmt = $pdo->prepare("UPDATE games SET status = 'finished' WHERE id = :game_id");
        $stmt->execute(['game_id' => $game_id]);

        // Εμφάνιση αποτελεσμάτων
        echo json_encode([
            'message' => 'Game ended.',
            'winner' => $winner,
            'scores' => $scores
        ]);


    }

    ?>
