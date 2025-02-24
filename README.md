# ADISE24_2020002 Blokus


# Blokus Game   API Documentation

The application is available at:
**https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php**

## Project Description

Blokus is an online strategy board game where two players place shapes on the board aiming to cover as much space as possible. It was developed as part of the coursework for the Development of Web Systems and Applications course. The API allows game creation, player joining, move management, and game status tracking through CLI tools such as curl.


### Technologies

- **Backend:** PHP
- **Database:** MySQL
- **Data Format:** JSON

## Database

**Tables:**

- **games:** Stores game information.
- **moves:** Stores player moves.
- **players:** Stores player information and their data.

## API Endpoints

### 1. Create New Game

**Endpoint:** `/create`  
**Method:** POST  
**Description:** Creates a new game with an empty board (20x20), generates a game_id and a token, stores the game in the database, assigns shapes to the player, and returns the `game_id`, `player_id`,` token`, and a `message`.

**Example:**
```bash
curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/create/ \
-H "Content-Type: application/json" \
-d '{"player_id": "player1"}'
```

**Response:**
```json
{
    "game_id": 1,
    "player_id": "player1",
    "token": "c68d9940",
    "message": "Game created, First player: start corner (0, 0). Waiting for second player."
}
```

### 2. Join Game

**Endpoint:** `/join/{game_id}`  
**Method:** POST  
**Description:**  Adds a player to an existing game, generates a token for the player, assigns shapes to them, and returns the `player_id`, `token` and a `message`.

**Example:**
```bash
curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/join/1 \
-H "Content-Type: application/json" \
-d '{"player_id": "player2"}'
```

**Response:**
```json
{
    "player_id": "player2",
    "token": "c68d9940",
    "message": "Game started, Second player: start corner (19, 19). Player 1's turn to play first."
}
```

### 3. Player Move

**Endpoint:** `/move/{game_id}`  
**Method:** POST  
**Description:** The player places a shape on the board following the game's rules.


**Example:**
```bash
curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/move/1 \
-H "Content-Type: application/json" \
-d '{"player_id": "player1","token":"c68d9940","piece": [[1]], "position": {"x": 0, "y": 0}}'
```

**Response:**
```json
{
    "success": "Move completed successfully."
}
```

### 4. View Board

**Endpoint:** `/board/{game_id}`  
**Method:** GET  
**Description:** Displays the current state of the game board..

**Example:**
```bash
curl -X GET https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/board/1
```

**Response:**
```json
{
    "board": [
        [1, 0, 0],
        [0, 0, 0],
        [0, 0, 0]
    ]
}
```

### 5. Pass Turn

**Endpoint:** `/pass/{game_id}`  
**Method:** POST  
**Description:** The player skips their turn.

**Example:**
```bash
curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/pass/1 -H "Content-Type: application/json"
-d '{"player_id": "player1","token":"c68d9940"}'
```

**Response:**
```json
{
    "success": true,
    "message": "You passed your turn and are now excluded from the game."
}
```

### 6. Game Status

**Endpoint:** `/status/{game_id}`  
**Method:** GET  
**Description:** Displays the game status and whose turn it is.

**Example:**
```bash
curl -X GET https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/status/1
```

**Response:**
```json
{
    "message": "Game in progress.",
    "player_turn": "player2"
}
```

### 7. Available Shapes

**Endpoint:** `/shapes/{game_id}`  
**Method:** GET  
**Description:** Returns the available shapes that a player can place.

**Example:**
```bash
curl -X GET https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/shapes/1 -H "Content-Type: application/json" -d "{\"player_id\": \"player1\",\"token\":\"c68d9940\"}"
```

**Response:**
```json
{
    "shapes": [[[1]], [[1, 1]], [[1, 1, 1]], [[1, 0], [1, 1]], [[1, 1, 1, 1]], [[1, 1], [1, 1]], [[0, 1, 0], [1, 1, 1]]]
}
```

## Installation Instructions

1. Clone το repository:  
   ```bash
   git clone https://github.com/<username>/ADISE24_2020002.git
   ```
2. Create the database: Create the blokus database and import the `schema.sql` file.
3. Configure database credentials in `db_upass.php`.
