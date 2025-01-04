<?php


function viewBoard($game_id) {
        global $pdo;
        

        if (!$game_id) {
            echo json_encode(['error' => 'Missing game_id parameter.']);
            return;
        }

        // τρέχουσα κατάσταση του παιχνιδιού
        $stmt = $pdo->prepare("SELECT board_state FROM games WHERE id = :game_id");
        $stmt->execute(['game_id' => $game_id]);
        $game = $stmt->fetch();

        if (!$game) {
            echo json_encode(['error' => 'Game not found.']);
            return;
        }

        $board_state = json_decode($game['board_state'], true);

        if (!$board_state || count($board_state) !== 20 || count($board_state[0]) !== 20) {
            echo json_encode(['error' => 'Invalid board state.']);
            return;
        }

        //  μορφή με tabs και νέες γραμμές
        $output = "{\n\t\"board\": [\n";
        foreach ($board_state as $row) {
            $output .= "\t\t[" . implode(", ", $row) . "],\n";
        }
        
        $output = rtrim($output, ",\n") ." \n\t]\n}";

        
        echo $output;
    }



    function updateBoardState($board, $piece, $position, $player_id) {
        foreach ($piece as $dx => $row) {
            foreach ($row as $dy => $value) {
                if ($value === 1) {
                    $x = $position['x'] + $dx;
                    $y = $position['y'] + $dy;

             

                    $board[$x][$y] = $player_id;
                }
            }
        }
        return $board;
    }



    
    ?>
