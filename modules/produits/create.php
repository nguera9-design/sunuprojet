<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$erreur = '';
$succes = '';

// Récupérer les catégories
$categories = $pdo->query("SELECT * FROM categories WHERE actif = 1 ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $categorie_id = $_POST['categorie_id'] ?? null;
    $unite_mesure = $_POST['unite_mesure'] ?? '';
    $prix_reference_ht = $_POST['prix_reference_ht'] ?? 0;
    $taux_tva = $_POST['taux_tva'] ?? 18;
    $seuil_alerte = $_POST['seuil_alerte'] ?? 0;
    $stock_minimum = $_POST['stock_minimum'] ?? 0;
    $stock_maximum = $_POST['stock_maximum'] ?? 0;
    $stock_actuel = $_POST['stock_actuel'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    if (empty($code) || empty($nom) || empty($unite_mesure)) {
        $erreur = 'Le code, le nom et l\'unité sont obligatoires';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO produits (id, code, nom, description, categorie_id, unite_mesure, prix_reference_ht, taux_tva, seuil_alerte, stock_minimum, stock_maximum, stock_actuel, created_by)
                VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$code, $nom, $description, $categorie_id ?: null, $unite_mesure, $prix_reference_ht, $taux_tva, $seuil_alerte, $stock_minimum, $stock_maximum, $stock_actuel, $_SESSION['user_id']]);
            
            $succes = 'Produit ajouté avec succès !';
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
    <title>Ajouter un produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php">🏢 Gestion Achats</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link text-danger" href="../../logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar" style="min-height: 100vh; padding: 20px 0;">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="../../dashboard.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="../fournisseurs/index.php"><i class="bi bi-building"></i> Fournisseurs</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-box"></i> Produits</a></li>
                </ul>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>➕ Ajouter un produit</h2>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                <?php endif; ?>

                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Code *</label>
                        <input type="text" name="code" class="form-control" required placeholder="Ex: PROD-001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Catégorie</label>
                        <select name="categorie_id" class="form-select">
                            <option value="">Sans catégorie</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unité de mesure *</label>
                        <input type="text" name="unite_mesure" class="form-control" required placeholder="Ex: pièce, kg, litre">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prix référence HT (FCFA)</label>
                        <input type="number" name="prix_reference_ht" class="form-control" step="1" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">TVA (%)</label>
                        <input type="number" name="taux_tva" class="form-control" step="0.01" value="18">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Seuil d'alerte</label>
                        <input type="number" name="seuil_alerte" class="form-control" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock minimum</label>
                        <input type="number" name="stock_minimum" class="form-control" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock maximum</label>
                        <input type="number" name="stock_maximum" class="form-control" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Stock actuel</label>
                        <input type="number" name="stock_actuel" class="form-control" value="0">
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