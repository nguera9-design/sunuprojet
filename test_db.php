<?php
// test_db.php - Vérification de la base de données

echo "<h1>🔍 Diagnostic de la base de données</h1>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sunuprojet;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✅ Connexion à la base réussie</p>";
} catch(PDOException $e) {
    die("<p style='color:red'>❌ Erreur de connexion : " . $e->getMessage() . "</p>");
}

// Vérifier les utilisateurs
$stmt = $pdo->query("SELECT id, email, mot_de_passe_hash, role FROM utilisateurs");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>📋 Liste des utilisateurs</h2>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Email</th><th>Hash (extrait)</th><th>Rôle</th></tr>";

foreach ($users as $user) {
    $hash_extrait = substr($user['mot_de_passe_hash'], 0, 20) . '...';
    echo "<tr>";
    echo "<td>" . htmlspecialchars($user['id']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . htmlspecialchars($hash_extrait) . "</td>";
    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Tester le mot de passe 'password123'
echo "<h2>🔑 Test du mot de passe 'password123'</h2>";

$hash_a_tester = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
$password = 'password123';

if (password_verify($password, $hash_a_tester)) {
    echo "<p style='color:green'>✅ Le hash est valide pour 'password123'</p>";
} else {
    echo "<p style='color:red'>❌ Le hash ne correspond pas à 'password123'</p>";
}

// Afficher la version de PHP
echo "<h2>📌 Informations PHP</h2>";
echo "<p>Version PHP : " . phpversion() . "</p>";
echo "<p>Extension password_hash disponible : " . (function_exists('password_hash') ? '✅ Oui' : '❌ Non') . "</p>";
?>