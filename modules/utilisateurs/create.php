<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $service = $_POST['service'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? 'password123';

    if (empty($nom) || empty($prenom) || empty($email) || empty($role)) {
        $erreur = 'Les champs nom, prénom, email et rôle sont obligatoires';
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $erreur = 'Cet email est déjà utilisé';
            } else {
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO utilisateurs (id, nom, prenom, email, mot_de_passe_hash, role, service, telephone, actif)
                    VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([$nom, $prenom, $email, $hash, $role, $service, $telephone]);
                $succes = 'Utilisateur créé avec succès ! Mot de passe : ' . $mot_de_passe;
            }
        } catch(PDOException $e) {
            $erreur = 'Erreur : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>➕ Ajouter un utilisateur</h2>

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                <?php endif; ?>

                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rôle *</label>
                        <select name="role" class="form-select" required>
                            <option value="">Sélectionner</option>
                            <option value="admin">Administrateur</option>
                            <option value="acheteur">Acheteur</option>
                            <option value="demandeur">Demandeur</option>
                            <option value="validateur">Validateur</option>
                            <option value="responsable_stock">Responsable stock</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service</label>
                        <input type="text" name="service" class="form-control" placeholder="Ex: RH, Production...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mot de passe</label>
                        <input type="text" name="mot_de_passe" class="form-control" value="password123">
                        <small class="text-muted">Par défaut : password123</small>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                        <a href="index.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>