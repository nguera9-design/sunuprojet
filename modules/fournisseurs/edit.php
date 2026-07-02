<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
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
$fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

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
    
    // Notation sur 4 critères
    $notation_qualite = intval($_POST['notation_qualite'] ?? 0);
    $notation_delai = intval($_POST['notation_delai'] ?? 0);
    $notation_prix = intval($_POST['notation_prix'] ?? 0);
    $notation_service = intval($_POST['notation_service'] ?? 0);
    
    if (empty($code) || empty($nom)) {
        $erreur = 'Le code et le nom sont obligatoires';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE fournisseurs 
                SET code = ?, nom = ?, adresse_ligne1 = ?, code_postal = ?, ville = ?, pays = ?, 
                    telephone = ?, email = ?, notation_qualite = ?, notation_delai = ?, 
                    notation_prix = ?, notation_service = ?, updated_by = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $code, $nom, $adresse_ligne1, $code_postal, $ville, $pays,
                $telephone, $email, $notation_qualite, $notation_delai, 
                $notation_prix, $notation_service, $_SESSION['user_id'], $id
            ]);
            
            $succes = 'Fournisseur modifié avec succès !';
            
            // Recharger les données
            $stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id = ?");
            $stmt->execute([$id]);
            $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .star-rating { display: flex; gap: 5px; }
        .star-rating label { cursor: pointer; font-size: 24px; }
        .star-rating input { display: none; }
        .star-rating .star { color: #ddd; transition: color 0.2s; }
        .star-rating .star.active, .star-rating .star:hover { color: #ffc107; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>✏️ Modifier un fournisseur</h2>
                
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
                        <input type="text" name="adresse_ligne1" class="form-control" value="<?= htmlspecialchars($fournisseur['adresse_ligne1'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Code postal</label>
                        <input type="text" name="code_postal" class="form-control" value="<?= htmlspecialchars($fournisseur['code_postal'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($fournisseur['ville'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pays</label>
                        <input type="text" name="pays" class="form-control" value="<?= htmlspecialchars($fournisseur['pays'] ?? 'Sénégal') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($fournisseur['telephone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($fournisseur['email'] ?? '') ?>">
                    </div>

                    <!-- Notation sur 4 critères -->
                    <div class="col-12">
                        <h5 class="mt-3">📊 Notation du fournisseur</h5>
                        <hr>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Qualité</label>
                        <select name="notation_qualite" class="form-select">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= ($fournisseur['notation_qualite'] ?? 0) == $i ? 'selected' : '' ?>>
                                    <?= $i ?> ⭐
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Délai</label>
                        <select name="notation_delai" class="form-select">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= ($fournisseur['notation_delai'] ?? 0) == $i ? 'selected' : '' ?>>
                                    <?= $i ?> ⭐
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix</label>
                        <select name="notation_prix" class="form-select">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= ($fournisseur['notation_prix'] ?? 0) == $i ? 'selected' : '' ?>>
                                    <?= $i ?> ⭐
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Service</label>
                        <select name="notation_service" class="form-select">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= ($fournisseur['notation_service'] ?? 0) == $i ? 'selected' : '' ?>>
                                    <?= $i ?> ⭐
                                </option>
                            <?php endfor; ?>
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