<?php 


require 'game.php';
require 'db.php'; 

// Λήψη δεδομένων JSON
$input = json_decode(file_get_contents('php://input'), true);


$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], characters: '/'));

switch ($r = array_shift($request)) {
    case 'create':
        if ($method == 'POST') {
            createNewGame($input);
        } else {
            header("HTTP/1.1 405 Method Not Allowed");
        }
        break;

    case 'join':
        if ($method == 'POST') {
            $game_id = array_shift($request);
            joinGame($game_id , $input);
        } else {
            header("HTTP/1.1 405 Method Not Allowed");
        }
        break;

    case 'move':
        if ($method == 'POST') {
            $game_id = array_shift($request);
            Move($game_id,$input);
        } else {
            header("HTTP/1.1 405 Method Not Allowed");
        }
        break;

        case 'pass':
            if ($method == 'POST') {
                $game_id = array_shift($request);
                passTurn($game_id,$input);
            } else {
                header("HTTP/1.1 405 Method Not Allowed");
            }
            break;


            case 'board':
                if ($method == 'GET') {
                    $game_id = array_shift($request);
                    viewBoard($game_id);
                } else {
                    header("HTTP/1.1 405 Method Not Allowed");
                }
                break;


                case 'shapes':
                    if ($method == 'GET') {
                        $game_id = array_shift($request);
                        AvailableShapes($game_id);
                    } else {
                        header("HTTP/1.1 405 Method Not Allowed");
                    }
                    break;

    default:
        header("HTTP/1.1 404 Not Found");
        echo json_encode(['error' => "Endpoint '$r' not found."]);
        break;
}

?>