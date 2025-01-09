# ADISE24_2020002 Blokus

# Blokus Game API Documentation

Η εφαρμογή είναι διαθέσιμη στη διεύθυνση:  
**[https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php](https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php)**

## Περιγραφή Project

Το Blokus είναι ένα online παιχνίδι που αναπτύχθηκε ως μέρος της εργασίας στο μάθημα Ανάπτυξη Διαδικτυακών Συστημάτων και Εφαρμογών. Το API επιτρέπει τη δημιουργία παιχνιδιών, την ένταξη παικτών, τη διαχείριση κινήσεων και την παρακολούθηση της κατάστασης του παιχνιδιού μέσω CLI εργαλείων όπως το `curl`.

### Τεχνολογίες

- **Backend:** PHP
- **Database:** MySQL
- **Data Format:** JSON

## Βάση Δεδομένων

**Πίνακες:**

- **games:** Αποθηκεύει τις πληροφορίες των παιχνιδιών.
- **moves:** Αποθηκεύει τις κινήσεις των παικτών.
- **players:** Αποθηκεύει τους παίκτες και τα δεδομένα τους.

## API Endpoints

### 1. Δημιουργία Νέου Παιχνιδιού

**Endpoint:** `/create`  
**Method:** POST  
**Περιγραφή:** Δημιουργεί ένα νέο παιχνίδι. Επιστρέφεται ένα μοναδικό `game_id` και ένα `token`.

**Παράδειγμα:**
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

### 2. Εντάσσεται σε Παιχνίδι

**Endpoint:** `/join/{game_id}`  
**Method:** POST  
**Περιγραφή:** Επιτρέπει σε έναν δεύτερο παίκτη να ενταχθεί σε ένα παιχνίδι.

**Παράδειγμα:**
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

### 3. Κίνηση Παίκτη

**Endpoint:** `/move/{game_id}`  
**Method:** POST  
**Περιγραφή:** Κάνει μία κίνηση για έναν παίκτη.

**Παράδειγμα:**
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

### 4. Προβολή Πίνακα

**Endpoint:** `/board/{game_id}`  
**Method:** GET  
**Περιγραφή:** Εμφανίζει την τρέχουσα κατάσταση του πίνακα.

**Παράδειγμα:**
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

### 5. Παράλειψη Σειράς

**Endpoint:** `/pass/{game_id}`  
**Method:** POST  
**Περιγραφή:** Ο παίκτης παραλείπει τη σειρά του.

**Παράδειγμα:**
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

### 6. Κατάσταση Παιχνιδιού

**Endpoint:** `/status/{game_id}`  
**Method:** GET  
**Περιγραφή:** Εμφανίζει την κατάσταση του παιχνιδιού και ποιος παίκτης είναι η σειρά του.

**Παράδειγμα:**
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

### 7. Διαθέσιμα Σχήματα

**Endpoint:** `/shapes/{game_id}`  
**Method:** GET  
**Περιγραφή:** Εμφανίζει τα διαθέσιμα σχήματα για τον παίκτη.

**Παράδειγμα:**
```bash
curl -X GET https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/shapes/1 -H "Content-Type: application/json" -d "{\"player_id\": \"player1\",\"token\":\"c68d9940\"}"
```

**Response:**
```json
{
    "shapes": [[[1]], [[1, 1]], [[1, 1, 1]], [[1, 0], [1, 1]], [[1, 1, 1, 1]], [[1, 1], [1, 1]], [[0, 1, 0], [1, 1, 1]]]
}
```

## Οδηγίες Εγκατάστασης

1. Clone το repository:  
   ```bash
   git clone https://github.com/<username>/ADISE24_2020002.git
   ```
2. Δημιουργία βάσης δεδομένων: Δημιουργήστε τη βάση `blokus` και εισάγετε το αρχείο `schema.sql`.
3. Ρυθμίστε τα διαπιστευτήρια της βάσης στο `db_upass.php`.
