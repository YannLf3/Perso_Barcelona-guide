<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h1>Diagnostic de l'environnement</h1>";

// Test de connexion à la base de données
// Il est préférable d'utiliser des variables d'environnement pour la configuration
$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '1BUpR1b?A';
$db   = getenv('DB_NAME') ?: 'barcelonaguide';

echo "<ul>";
echo "<li>Hôte : <code>$host</code></li>";
echo "<li>Base : <code>$db</code></li>";
echo "<li>Utilisateur : <code>$user</code></li>";
echo "</ul>";

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $conn = new PDO($dsn, $user, $pass, $options);
    echo "<h3>Connexion à MySQL réussie !</h3>";

    // Vérification des tables existantes
    echo "<h4>Tables trouvées dans la base :</h4>";
    $query = $conn->query("SHOW TABLES");
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color:orange;'>Attention : Aucune table trouvée. Avez-vous importé le fichier SQL ?</p>";
    } else {
        echo "<ul><li>" . implode("</li><li>", $tables) . "</li></ul>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erreur de connexion : " . $e->getMessage() . "</p>";
}
