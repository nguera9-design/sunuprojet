<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    header('Location: index.php');
    exit();
}

$erreur = '';
$succes = '';

// Récupérer le fournisseur
$stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id = ?");
$stmt->execute([$id]);
$fournisseur = $stmt->fetch();

if (!$fournisseur) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $adresse_ligne1 = $_POST['adresse_ligne1'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $pays = $_POST['pays'] ?? 'Sénégal';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (empty($code) || empty($nom)) {
        $erreur = 'Le code et le nom sont obligatoires';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE fournisseurs 
                SET code = ?, nom = ?, adresse_ligne1 = ?, code_postal = ?, ville = ?, pays = ?, telephone = ?, email = ?, updated_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$code, $nom, $adresse_ligne1, $code_postal, $ville, $pays, $telephone, $email, $_SESSION['user_id'], $id]);
            
            $succes = 'Fournisseur modifié avec succès !';
            // Recharger les données
            $stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id = ?");
            $stmt->execute([$id]);
            $fournisseur = $stmt->fetch();
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
    <title>Modifier un fournisseur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php">🏢 Gestion Achats</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../../logout.php">Déconnexion</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar" style="min-height: 100vh; padding: 20px 0;">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="../../dashboard.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="../demandes/index.php"><i class="bi bi-file-earmark-plus"></i> Demandes d'achat</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-building"></i> Fournisseurs</a></li>
                    <li class="nav-item"><a class="nav-link" href="../produits/index.php"><i class="bi bi-box"></i> Produits</a></li>
                    <li class="nav-item"><a class="nav-link" href="../bons_commande/index.php"><i class="bi bi-receipt"></i> Bons de commande</a></li>
                    <li class="nav-item"><a class="nav-link" href="../livraisons/index.php"><i class="bi bi-truck"></i> Livraisons</a></li>
                    <li class="nav-item"><a class="nav-link" href="../stock/index.php"><i class="bi bi-clipboard-data"></i> Stock</a></li>
                </ul>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>✏️ Modifier le fournisseur</h2>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                <?php endif; ?>

                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Code *</label>
                        <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($fournisseur['code']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($fournisseur['nom']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="adresse_ligne1" class="form-control" value="<?= htmlspecialchars($fournisseur['adresse_ligne1']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Code postal</label>
                        <input type="text" name="code_postal" class="form-control" value="<?= htmlspecialchars($fournisseur['code_postal']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($fournisseur['ville']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pays</label>
                        <input type="text" name="pays" class="form-control" value="<?= htmlspecialchars($fournisseur['pays']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($fournisseur['telephone']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($fournisseur['email']) ?>">
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