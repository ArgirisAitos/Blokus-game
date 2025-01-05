<?php
$host = 'localhost';
$db = 'blokus';
require_once "db_upass.php"; 

$user = $DB_USER;
$pass = $DB_PASS;

try {
    if (gethostname() == 'users.iee.ihu.gr') {
        
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8;unix_socket=/home/student/iee/2020/iee2020002/mysql/run/mysql.sock", $user, $pass);
    } else {
        
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, null);
    }

    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
?>
