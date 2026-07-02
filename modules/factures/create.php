<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$erreur = '';
$succes = '';
$bon_id = $_GET['bon_id'] ?? '';

// Récupérer les bons de commande livrés
$bons = $pdo->query("
    SELECT bc.id, bc.numero, f.nom as fournisseur_nom, bc.total_ttc
    FROM bons_commande bc
    LEFT JOIN fournisseurs f ON bc.fournisseur_id = f.id
    WHERE bc.statut IN ('recu_total', 'recu_partiel', 'facture')
    ORDER BY bc.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les fournisseurs
$fournisseurs = $pdo->query("SELECT id, nom FROM fournisseurs WHERE actif = 1 ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Si un BC est sélectionné, pré-remplir
$bc_selected = null;
if (!empty($bon_id)) {
    $stmt = $pdo->prepare("
        SELECT bc.*, f.nom as fournisseur_nom, f.id as fournisseur_id
        FROM bons_commande bc
        LEFT JOIN fournisseurs f ON bc.fournisseur_id = f.id
        WHERE bc.id = ?
    ");
    $stmt->execute([$bon_id]);
    $bc_selected = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fournisseur_id = $_POST['fournisseur_id'] ?? '';
    $bon_commande_id = $_POST['bon_commande_id'] ?? null;
    $date_emission = $_POST['date_emission'] ?? date('Y-m-d');
    $date_echeance = $_POST['date_echeance'] ?? '';
    $total_ht = $_POST['total_ht'] ?? 0;
    $total_tva = $_POST['total_tva'] ?? 0;
    $total_ttc = $_POST['total_ttc'] ?? 0;
    $numero_facture_fournisseur = $_POST['numero_facture_fournisseur'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($fournisseur_id) || empty($date_echeance)) {
        $erreur = 'Veuillez remplir tous les champs obligatoires';
    } else {
        try {
            // Générer le numéro de facture
            $annee = date('Y');
            $stmt = $pdo->query("SELECT COUNT(*) FROM factures WHERE YEAR(created_at) = $annee");
            $count = $stmt->fetchColumn() + 1;
            $numero = 'FACT-' . $annee . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            $facture_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff) | 0x4000,
                mt_rand(0, 0xffff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            $stmt = $pdo->prepare("
                INSERT INTO factures (
                    id, numero, fournisseur_id, bon_commande_id,
                    date_emission, date_echeance,
                    total_ht, total_tva, total_ttc,
                    numero_facture_fournisseur, notes,
                    statut, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', ?)
            ");
            $stmt->execute([
                $facture_id, $numero, $fournisseur_id, $bon_commande_id ?: null,
                $date_emission, $date_echeance,
                $total_ht, $total_tva, $total_ttc,
                $numero_facture_fournisseur, $notes,
                $_SESSION['user_id']
            ]);
            
            // Mettre à jour le statut du BC
            if (!empty($bon_commande_id)) {
                $stmt = $pdo->prepare("UPDATE bons_commande SET statut = 'facture' WHERE id = ?");
                $stmt->execute([$bon_commande_id]);
            }
            
            $succes = 'Facture créée avec succès ! N° ' . $numero;
            
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
    <title>Nouvelle facture</title>
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

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar" style="min-height: 100vh; padding: 20px 0;">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="../../dashboard.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="../bons_commande/index.php"><i class="bi bi-receipt"></i> Bons de commande</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-credit-card"></i> Factures</a></li>
                </ul>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h2>➕ Nouvelle facture fournisseur</h2>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                    <a href="index.php" class="btn btn-primary">Voir les factures</a>
                <?php endif; ?>

                <?php if (!$succes): ?>
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fournisseur *</label>
                        <select name="fournisseur_id" class="form-select" required>
                            <option value="">Sélectionner</option>
                            <?php foreach ($fournisseurs as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= ($bc_selected && $bc_selected['fournisseur_id'] == $f['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bon de commande</label>
                        <select name="bon_commande_id" class="form-select">
                            <option value="">Sans BC</option>
                            <?php foreach ($bons as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($bc_selected && $bc_selected['id'] == $b['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['numero']) ?> - <?= htmlspecialchars($b['fournisseur_nom']) ?> 
                                    (<?= number_format($b['total_ttc'], 0, ',', ' ') ?> FCFA)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date d'émission</label>
                        <input type="date" name="date_emission" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date d'échéance *</label>
                        <input type="date" name="date_echeance" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">N° facture fournisseur</label>
                        <input type="text" name="numero_facture_fournisseur" class="form-control" placeholder="Ex: F2025-001">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total HT</label>
                        <input type="number" name="total_ht" class="form-control" step="1" value="<?= $bc_selected ? $bc_selected['total_ht'] : 0 ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total TVA</label>
                        <input type="number" name="total_tva" class="form-control" step="1" value="<?= $bc_selected ? $bc_selected['total_tva'] : 0 ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total TTC</label>
                        <input type="number" name="total_ttc" class="form-control" step="1" value="<?= $bc_selected ? $bc_selected['total_ttc'] : 0 ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">💾 Enregistrer</button>
                        <a href="index.php" class="btn btn-secondary btn-lg">Annuler</a>
                    </div>
                </form>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>