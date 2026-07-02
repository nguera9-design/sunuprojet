<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';
$erreur = '';
$succes = '';

if (empty($id)) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $role = $_POST['role'] ?? '';
    $service = $_POST['service'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $actif = $_POST['actif'] ?? 0;

    if (empty($nom) || empty($prenom) || empty($role)) {
        $erreur = 'Les champs nom, prénom et rôle sont obligatoires';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE utilisateurs 
                SET nom = ?, prenom = ?, role = ?, service = ?, telephone = ?, actif = ?
                WHERE id = ?
            ");
            $stmt->execute([$nom, $prenom, $role, $service, $telephone, $actif, $id]);
            $succes = 'Utilisateur modifié avec succès !';
            // Recharger
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Modifier un utilisateur</title>
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
                <h2>✏️ Modifier un utilisateur</h2>

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                <?php endif; ?>

                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rôle *</label>
                        <select name="role" class="form-select" required>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrateur</option>
                            <option value="acheteur" <?= $user['role'] == 'acheteur' ? 'selected' : '' ?>>Acheteur</option>
                            <option value="demandeur" <?= $user['role'] == 'demandeur' ? 'selected' : '' ?>>Demandeur</option>
                            <option value="validateur" <?= $user['role'] == 'validateur' ? 'selected' : '' ?>>Validateur</option>
                            <option value="responsable_stock" <?= $user['role'] == 'responsable_stock' ? 'selected' : '' ?>>Responsable stock</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Service</label>
                        <input type="text" name="service" class="form-control" value="<?= htmlspecialchars($user['service'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut</label>
                        <select name="actif" class="form-select">
                            <option value="1" <?= $user['actif'] == 1 ? 'selected' : '' ?>>Actif</option>
                            <option value="0" <?= $user['actif'] == 0 ? 'selected' : '' ?>>Inactif</option>
                        </select>
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