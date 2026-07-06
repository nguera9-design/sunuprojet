<?php
// reset_password.php - Réinitialiser tous les mots de passe

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sunuprojet;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Nouveau mot de passe
    $password = 'password123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<h1>🔄 Réinitialisation des mots de passe</h1>";
    echo "<p>Nouveau hash généré : <code>" . $hash . "</code></p>";
    
    // Mettre à jour tous les utilisateurs
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe_hash = ?");
    $stmt->execute([$hash]);
    
    $count = $stmt->rowCount();
    echo "<p style='color:green; font-size:18px;'>✅ $count utilisateur(s) mis à jour avec le mot de passe 'password123'</p>";
    
    echo "<hr>";
    echo "<p><a href='login.php'>🔐 Aller à la page de connexion</a></p>";
    
} catch(PDOException $e) {
    die("<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>");
}
?>