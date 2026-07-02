<?php
// reset_password.php - Réinitialiser tous les mots de passe

echo "<h1>🔄 Réinitialisation des mots de passe</h1>";

try {
    // Connexion à la base
    $pdo = new PDO("mysql:host=localhost;dbname=sunuprojet;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Nouveau mot de passe
    $password = 'password123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<p>✅ Nouveau hash généré : <code>" . $hash . "</code></p>";
    
    // Mettre à jour tous les utilisateurs
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe_hash = ?");
    $stmt->execute([$hash]);
    
    $count = $stmt->rowCount();
    echo "<p style='color:green; font-size:18px;'>✅ $count utilisateur(s) mis à jour avec le mot de passe 'password123'</p>";
    
    // Vérifier que ça a fonctionné
    echo "<h2>🔍 Vérification</h2>";
    $verif = $pdo->query("SELECT email, role FROM utilisateurs");
    echo "<ul>";
    while ($row = $verif->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>" . $row['email'] . " - Rôle : " . $row['role'] . "</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<a href='login.php' style='font-size:20px;'>🔐 Aller à la page de connexion</a>";
    
} catch(PDOException $e) {
    die("<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>");
}
?>