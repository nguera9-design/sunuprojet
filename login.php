<?php
// login.php - Page de connexion
session_start();

// Si déjà connecté, rediriger vers le tableau de bord
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (!empty($email) && !empty($mot_de_passe)) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=sunuprojet;charset=utf8", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($mot_de_passe, $user['mot_de_passe_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $erreur = 'Email ou mot de passe incorrect';
            }
        } catch(PDOException $e) {
            $erreur = 'Erreur de connexion à la base de données';
        }
    } else {
        $erreur = 'Veuillez remplir tous les champs';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion des Achats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
        }
        .login-card { 
            max-width: 420px; 
            width: 100%;
            padding: 40px; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-card h3 { 
            text-align: center; 
            margin-bottom: 10px; 
            color: #2c3e50;
            font-weight: 700;
        }
        .login-card .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .login-card .logo {
            text-align: center;
            font-size: 48px;
            margin-bottom: 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .demo-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
        }
        .demo-info strong {
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="logo">🏢</div>
            <h3>Gestion des Achats</h3>
            <p class="subtitle">Plateforme d'approvisionnement</p>
            
            <?php if ($erreur): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">📧 Email</label>
                    <input type="email" name="email" class="form-control" placeholder="exemple@email.com" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">🔒 Mot de passe</label>
                    <input type="password" name="mot_de_passe" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
            
            <div class="demo-info">
                <strong>🔑 Comptes de démonstration :</strong><br>
                <span class="badge bg-primary">Admin</span> admin@sunuprojet.sn / password123<br>
                <span class="badge bg-success">Demandeur</span> demandeur@sunuprojet.sn / password123<br>
                <span class="badge bg-warning text-dark">Acheteur</span> acheteur@sunuprojet.sn / password123
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>