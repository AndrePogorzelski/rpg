<?php
// config/database.php - SEM criação automática de tabelas
function getDBConnection() {
    $host = 'localhost';
    $dbname = 'rpg_system';
    $username = 'root';
    $password = '123456';  // Sua senha do MySQL
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}
?>