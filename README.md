# ADISE24_2020002 Blokus

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blokus Game API Documentation</title>	
</head>
<body>
   <h1>Blokus Game API Documentation</h1>
	<p>
	Η εφαρμογή είναι διαθέσιμη στη διεύθυνση :
	
	<b>https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php</b>
	
	</p>
	
     <h2>Περιγραφή Project</h2>
    <p>Το Blokus είναι ένα online παιχνίδι που αναπτύχθηκε ως μέρος της εργασίας στο μάθημα Ανάπτυξη Διαδικτυακών Συστημάτων και Εφαρμογών. Το API επιτρέπει τη δημιουργία παιχνιδιών, την ένταξη παικτών, τη διαχείριση κινήσεων και την παρακολούθηση της κατάστασης του παιχνιδιού μέσω CLI εργαλείων όπως το curl.</p>

    <h2>Τεχνολογίες</h2>
    <ul>
        <li>Backend: PHP</li>
        <li>Database: MySQL</li>
        <li>Data Format: JSON</li>
    </ul>
	
<h2>Βάση Δεδομένων</h2>
<p>
 <strong> Πίνακες :  </strong> 
1. **games**: Αποθηκεύει τις πληροφορίες των παιχνιδιών.  
2. **moves**: Αποθηκεύει τις κινήσεις των παικτών.  
3. **players**: Αποθηκεύει τους παίκτες και τα δεδομένα τους. 
</p>



    <h2>API Endpoints</h2>

    <h3>1. Δημιουργία Νέου Παιχνιδιού</h3>
    <p><strong>Endpoint:</strong> /create</p>
    <p><strong>Method:</strong> POST</p>
    <p><strong>Περιγραφή:</strong> Δημιουργεί ένα νέο παιχνίδι. Κατά τη δημιουργία του παιχνιδιού, το σύστημα δημιουργεί μια κενή κατάσταση του πίνακα (board) και την αποθηκεύει στη βάση δεδομένων. Επιστρέφεται ένα μοναδικό game_id για να αναγνωρίσετε το παιχνίδι, καθώς και ένα token που θα χρησιμοποιηθεί για την επικύρωση των κινήσεων κατά τη διάρκεια του παιχνιδιού. Επίσης, το σύστημα αναθέτει τα σχήματα στον πρώτο παίκτη, ώστε να είναι έτοιμος να ξεκινήσει το παιχνίδι.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/create/ -H "Content-Type: application/json" -d "{\"player_id\": \"player1\"}"</pre>
	<p><strong>Response:</strong></p>
<pre>
{
    "game_id": 1,
	"player_id" : player1
    "token": "c68d9940",
    "message": "Game created,  First player: start corner (0, 0).  Waiting for second player.."
}
</pre>


    <h3>2. Εντάσσεται σε Παιχνίδι</h3>
    <p><strong>Endpoint:</strong> /join/{game_id}</p>
    <p><strong>Method:</strong> POST</p>
    <p><strong>Περιγραφή:</strong> Επιτρέπει σε έναν δεύτερο παίκτη να ενταχθεί σε ένα παιχνίδι που βρίσκεται σε εξέλιξη. Ο παίκτης εισάγει το όνομά του και το game_id του παιχνιδιού στο οποίο επιθυμεί να συμμετάσχει. Αν το παιχνίδι υπάρχει και είναι διαθέσιμο το σύστημα του αναθέτει τα σχήματα που θα χρησιμοποιήσει για να τοποθετήσει τα κομμάτια του στο ταμπλό. Επίσης, του εκχωρεί ένα νέο token για να ταυτοποιηθεί και να συμμετάσχει στη συνέχεια του παιχνιδιού. Αφότου ο δεύτερος παίκτης ενταχθεί, το παιχνίδι ξεκινά.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/join/1 -H "Content-Type: application/json" -d "{\"player_id\": \"player2\"}'</pre>
	
	
		<p><strong>Response:</strong></p>
<pre>
{
    
	"player_id" : player1
    "token": "c68d9940",
    "message":" Game started, Second player:  start corner (19, 19),   Player 1's turn to play first"
}
</pre>

   <h3>3. Κίνηση Παίκτη</h3>
    <p><strong>Endpoint: </strong>  /move/{game_id}</p>
    <p><strong>Method:</strong> POST</p>
    <p><strong>Περιγραφή:</strong> Κάνει μία κίνηση για έναν παίκτη.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/move/1 -H "Content-Type: application/json" -d "{\"player_id\": \"player1\",\"token\":\"c68d9940\",\"piece\": [[1]], \"position\": {\"x\": 0, \"y\": 0}}"</pre>
			<p><strong>Response:</strong></p>
<pre>
{
     {"success":"Move completed successfully."}
}
</pre>
	
	
<h3>4. Προβολή Πίνακα</h3>
    <p><strong>Endpoint:</strong> /board/{game_id}</p>
    <p><strong>Method:</strong> GET</p>
    <p><strong>Περιγραφή:</strong> Εμφανίζει την τρέχουσα κατάσταση του πίνακα.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X GET   https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/board/1</pre>
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
    <pre>curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/board/1 -d '{"{\"player_id\": \"player1\",\"token\":\"c68d9940\"}'</pre>
	<p><strong>Response:</strong></p>
	<pre>
{
   "success": true, "message":"You passed your turn and are now excluded from the game."
}
</pre>
	

    <h3>6. Κατάσταση Παιχνιδιού</h3>
    <p><strong>Endpoint:</strong>  /status/{game_id}</p>
    <p><strong>Method:</strong> GET</p>
    <p><strong>Περιγραφή:</strong> Εμφανίζει την κατάσταση του παιχνιδιού και ποιος παίκτης είναι η σειρά του.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X GET https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/status/1  </pre>
	
	<p><strong>Response:</strong></p>
	<pre>
	
   {
   "message": "Game in progress.","player_turn": "player2"
   }
   
   </pre>
	
	    <h3>7. Διαθέσιμα σχήματα</h3>
    <p><strong>Endpoint:</strong> /shapes/{game_id}</p>
    <p><strong>Method:</strong> GET</p>
    <p><strong>Περιγραφή:</strong> Εμφανίζει την κατάσταση του παιχνιδιού και ποιος παίκτης είναι η σειρά του.</p>
    <p><strong>Παράδειγμα:</strong></p>
    <pre>curl -X GET curl -X POST https://users.iee.ihu.gr/~iee2020002/ADISE24_2020002/blokus.php/shapes/1  -H "Content-Type: application/json" -d "{\"player_id\": \"player1\",\"token\":\"c68d9940\"}" </pre>
		<p><strong>Response:</strong></p>
	<pre>
	
   {
  "shapes":[[[1]],[[1,1]],[[1,1,1]],[[1,0],[1,1]],[[1,1,1,1]],[[1,1],[1,1]],[[0,1,0],[1,1,1]]
   }
   
   </pre>
	
	

    <h2>Οδηγίες Εγκατάστασης</h2>
    <ol>
        <li>Clone το repository: <code>git clone https://github.com/<username>/ADISE24_2020002.git</code></li>
        <li>Δημιουργία βάσης δεδομένων: Δημιουργήστε τη βάση <code>blokus</code> και εισάγετε το αρχείο <code>schema.sql</code>.</li>
        <li>Ρυθμίστε τα διαπιστευτήρια της βάσης στο <code>db_upass.php</code>.</li>
        
    </ol>

    
</body>
</html>
