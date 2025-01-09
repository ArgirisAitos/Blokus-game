# ADISE24_2020002 Blokus
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blokus Game API Documentation</title>
</head>
<body>
    <h1>Blokus Game API Documentation</h1>
    <p>Η εφαρμογή είναι διαθέσιμη στη διεύθυνση:</p>
    <p><b><a href="https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php" target="_blank">https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php</a></b></p>

    <h2>Περιγραφή Project</h2>
    <p>Το Blokus είναι ένα online παιχνίδι που αναπτύχθηκε ως μέρος της εργασίας στο μάθημα Ανάπτυξη Διαδικτυακών Συστημάτων και Εφαρμογών. Το API επιτρέπει τη δημιουργία παιχνιδιών, την ένταξη παικτών, τη διαχείριση κινήσεων και την παρακολούθηση της κατάστασης του παιχνιδιού μέσω CLI εργαλείων όπως το curl.</p>

    <h3>Τεχνολογίες</h3>
    <ul>
        <li>Backend: PHP</li>
        <li>Database: MySQL</li>
        <li>Data Format: JSON</li>
    </ul>

    <h2>Βάση Δεδομένων</h2>
    <p><strong>Πίνακες:</strong></p>
    <ul>
        <li><b>games</b>: Αποθηκεύει τις πληροφορίες των παιχνιδιών.</li>
        <li><b>moves</b>: Αποθηκεύει τις κινήσεις των παικτών.</li>
        <li><b>players</b>: Αποθηκεύει τους παίκτες και τα δεδομένα τους.</li>
    </ul>

    <h2>API Endpoints</h2>

    <h3>1. Δημιουργία Νέου Παιχνιδιού</h3>
    <p><strong>Endpoint:</strong> /create</p>
    <p><strong>Method:</strong> POST</p>
    <p><strong>Περιγραφή:</strong> Δημιουργεί ένα νέο παιχνίδι. Επιστρέφεται ένα μοναδικό game_id και ένα token.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/create/ -H "Content-Type: application/json" -d '{"player_id": "player1"}'</pre>
    <p><strong>Response:</strong></p>
    <pre>
{
    "game_id": 1,
    "player_id": "player1",
    "token": "c68d9940",
    "message": "Game created, First player: start corner (0, 0). Waiting for second player."
}
    </pre>

    <h3>2. Εντάσσεται σε Παιχνίδι</h3>
    <p><strong>Endpoint:</strong> /join/{game_id}</p>
    <p><strong>Method:</strong> POST</p>
    <p><strong>Περιγραφή:</strong> Επιτρέπει σε έναν δεύτερο παίκτη να ενταχθεί σε ένα παιχνίδι.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/join/1 -H "Content-Type: application/json" -d '{"player_id": "player2"}'</pre>
    <p><strong>Response:</strong></p>
    <pre>
{
    "player_id": "player2",
    "token": "c68d9940",
    "message": "Game started, Second player: start corner (19, 19). Player 1's turn to play first."
}
    </pre>

    <h3>3. Κίνηση Παίκτη</h3>
    <p><strong>Endpoint:</strong> /move/{game_id}</p>
    <p><strong>Method:</strong> POST</p>
    <p><strong>Περιγραφή:</strong> Κάνει μία κίνηση για έναν παίκτη.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/move/1 -H "Content-Type: application/json" -d '{"player_id": "player1","token":"c68d9940","piece": [[1]], "position": {"x": 0, "y": 0}}'</pre>
    <p><strong>Response:</strong></p>
    <pre>
{
    "success": "Move completed successfully."
}
    </pre>

    <h3>4. Προβολή Πίνακα</h3>
    <p><strong>Endpoint:</strong> /board/{game_id}</p>
    <p><strong>Method:</strong> GET</p>
    <p><strong>Περιγραφή:</strong> Εμφανίζει την τρέχουσα κατάσταση του πίνακα.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X GET https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/board/1</pre>
    <p><strong>Response:</strong></p>
    <pre>
{
    "board": [
        [1, 0, 0],
        [0, 0, 0],
        [0, 0, 0]
    ]
}
    </pre>

    <h3>5. Παράλειψη Σειράς</h3>
    <p><strong>Endpoint:</strong> /pass/{game_id}</p>
    <p><strong>Method:</strong> POST</p>
    <p><strong>Περιγραφή:</strong> Ο παίκτης παραλείπει τη σειρά του.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/pass/1 -d '{"player_id": "player1","token":"c68d9940"}'</pre>
    <p><strong>Response:</strong></p>
    <pre>
{
    "success": true,
    "message": "You passed your turn and are now excluded from the game."
}
    </pre>

    <h3>6. Κατάσταση Παιχνιδιού</h3>
    <p><strong>Endpoint:</strong> /status/{game_id}</p>
    <p><strong>Method:</strong> GET</p>
    <p><strong>Περιγραφή:</strong> Εμφανίζει την κατάσταση του παιχνιδιού και ποιος παίκτης είναι η σειρά του.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X GET https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/status/1</pre>
    <p><strong>Response:</strong></p>
    <pre>
{
    "message": "Game in progress.",
    "player_turn": "player2"
}
    </pre>

    <h3>7. Διαθέσιμα Σχήματα</h3>
    <p><strong>Endpoint:</strong> /shapes/{game_id}</p>
    <p><strong>Method:</strong> GET</p>
    <p><strong>Περιγραφή:</strong> Εμφανίζει τα διαθέσιμα σχήματα για τον παίκτη.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X GET https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/shapes/1</pre>
    <p><strong>Response:</strong></p>
    <pre>
{
    "shapes": [[[1]], [[1, 1]], [[1, 1, 1]], [[1, 0], [1, 1]], [[1, 1, 1, 1]], [[1, 1], [1, 1]], [[0, 1, 0], [1, 1, 1]]]
}
    </pre>

    <h2>Οδηγίες Εγκατάστασης</h2>
    <ol>
        <li>Clone το repository: <code>git clone https://github.com/&lt;username&gt;/ADISE24_2020002.git</code></li>
        <li>Δημιουργία βάσης δεδομένων: Δημιουργήστε τη βάση <code>blokus</code> και εισάγετε το αρχείο <code>schema.sql</code>.</li>
        <li>Ρυθμίστε τα διαπιστευτήρια της βάσης στο <code>db_upass.php</code>.</li>
    </ol>
</body>
</html>
