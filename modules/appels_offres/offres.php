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

// Récupérer l'appel d'offres
$stmt = $pdo->prepare("SELECT * FROM appels_offres WHERE id = ?");
$stmt->execute([$id]);
$appel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appel) {
    header('Location: index.php');
    exit();
}

// Récupérer les offres reçues
$offres = $pdo->prepare("
    SELECT of.*, f.nom as fournisseur_nom, f.code as fournisseur_code
    FROM offres_fournisseurs of
    LEFT JOIN fournisseurs f ON of.fournisseur_id = f.id
    WHERE of.appel_offre_id = ?
    ORDER BY of.montant_ht ASC
");
$offres->execute([$id]);
$offres = $offres->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les fournisseurs pour ajouter une offre
$fournisseurs = $pdo->query("SELECT id, code, nom FROM fournisseurs WHERE actif = 1 ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

$erreur = '';
$succes = '';

// Ajouter une offre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ajouter') {
    $fournisseur_id = $_POST['fournisseur_id'] ?? '';
    $montant_ht = $_POST['montant_ht'] ?? 0;
    $delai_livraison_jours = $_POST['delai_livraison_jours'] ?? 0;
    $conditions_paiement = $_POST['conditions_paiement'] ?? '';

    if (empty($fournisseur_id) || $montant_ht <= 0) {
        $erreur = 'Veuillez sélectionner un fournisseur et saisir un montant';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO offres_fournisseurs (id, appel_offre_id, fournisseur_id, montant_ht, delai_livraison_jours, conditions_paiement)
                VALUES (UUID(), ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id, $fournisseur_id, $montant_ht, $delai_livraison_jours, $conditions_paiement]);
            $succes = 'Offre ajoutée avec succès !';
            // Recharger les offres
            $offres = $pdo->prepare("
                SELECT of.*, f.nom as fournisseur_nom, f.code as fournisseur_code
                FROM offres_fournisseurs of
                LEFT JOIN fournisseurs f ON of.fournisseur_id = f.id
                WHERE of.appel_offre_id = ?
                ORDER BY of.montant_ht ASC
            ");
            $offres->execute([$id]);
            $offres = $offres->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $erreur = 'Erreur : ' . $e->getMessage();
        }
    }
}

// Sélectionner une offre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'selectionner') {
    $offre_id = $_POST['offre_id'] ?? '';
    $justification = $_POST['justification'] ?? '';

    if (empty($offre_id)) {
        $erreur = 'Veuillez sélectionner une offre';
    } else {
        try {
            $pdo->beginTransaction();

            // Mettre à jour les offres
            $pdo->prepare("UPDATE offres_fournisseurs SET est_selectionnee = 0 WHERE appel_offre_id = ?")->execute([$id]);
            $pdo->prepare("UPDATE offres_fournisseurs SET est_selectionnee = 1 WHERE id = ?")->execute([$offre_id]);

            // Récupérer le fournisseur sélectionné
            $stmt = $pdo->prepare("SELECT fournisseur_id FROM offres_fournisseurs WHERE id = ?");
            $stmt->execute([$offre_id]);
            $fournisseur_id = $stmt->fetchColumn();

            // Clôturer l'appel d'offres
            $pdo->prepare("UPDATE appels_offres SET statut = 'clos' WHERE id = ?")->execute([$id]);

            $pdo->commit();
            $succes = 'Offre sélectionnée avec succès ! Appel d\'offres clôturé.';

            // Recharger
            $appel = $pdo->prepare("SELECT * FROM appels_offres WHERE id = ?");
            $appel->execute([$id]);
            $appel = $appel->fetch(PDO::FETCH_ASSOC);

            $offres = $pdo->prepare("
                SELECT of.*, f.nom as fournisseur_nom, f.code as fournisseur_code
                FROM offres_fournisseurs of
                LEFT JOIN fournisseurs f ON of.fournisseur_id = f.id
                WHERE of.appel_offre_id = ?
                ORDER BY of.montant_ht ASC
            ");
            $offres->execute([$id]);
            $offres = $offres->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            $pdo->rollBack();
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
    <title>Offres - <?= htmlspecialchars($appel['numero']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .offre-selectionnee { background: #d4edda; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>📋 Offres - <?= htmlspecialchars($appel['numero']) ?></h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>

                <div class="alert alert-info">
                    <strong><?= htmlspecialchars($appel['titre']) ?></strong><br>
                    Date limite : <?= date('d/m/Y', strtotime($appel['date_limite_reponse'])) ?>
                    <?php if ($appel['statut'] == 'clos'): ?>
                        <span class="badge bg-success">Clôturé</span>
                    <?php endif; ?>
                </div>

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                <?php endif; ?>

                <?php if ($appel['statut'] == 'en_cours'): ?>
                <!-- Formulaire d'ajout d'offre -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-plus-circle"></i> Ajouter une offre
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="ajouter">
                            <div class="col-md-4">
                                <label class="form-label">Fournisseur *</label>
                                <select name="fournisseur_id" class="form-select" required>
                                    <option value="">Sélectionner</option>
                                    <?php foreach ($fournisseurs as $f): ?>
                                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['code']) ?> - <?= htmlspecialchars($f['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Montant HT *</label>
                                <input type="number" name="montant_ht" class="form-control" step="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Délai (jours)</label>
                                <input type="number" name="delai_livraison_jours" class="form-control" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Conditions paiement</label>
                                <input type="text" name="conditions_paiement" class="form-control" placeholder="Ex: 30 jours">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">💾 Ajouter l'offre</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Liste des offres -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <i class="bi bi-list-check"></i> Offres reçues (<?= count($offres) ?>)
                    </div>
                    <div class="card-body">
                        <?php if (empty($offres)): ?>
                            <p class="text-muted">Aucune offre reçue pour le moment.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fournisseur</th>
                                            <th>Montant HT</th>
                                            <th>Délai (jours)</th>
                                            <th>Conditions</th>
                                            <th>Statut</th>
                                            <?php if ($appel['statut'] == 'en_cours'): ?>
                                                <th>Action</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($offres as $o): ?>
                                        <tr class="<?= $o['est_selectionnee'] ? 'offre-selectionnee' : '' ?>">
                                            <td><?= htmlspecialchars($o['fournisseur_code'] ?? '') ?> - <?= htmlspecialchars($o['fournisseur_nom'] ?? '') ?></td>
                                            <td><strong><?= number_format($o['montant_ht'], 0, ',', ' ') ?> FCFA</strong></td>
                                            <td><?= $o['delai_livraison_jours'] ?></td>
                                            <td><?= htmlspecialchars($o['conditions_paiement'] ?? '—') ?></td>
                                            <td>
                                                <?php if ($o['est_selectionnee']): ?>
                                                    <span class="badge bg-success">✅ Sélectionnée</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">En attente</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if ($appel['statut'] == 'en_cours' && !$o['est_selectionnee']): ?>
                                                <td>
                                                    <form method="POST" onsubmit="return confirm('Sélectionner cette offre ?')">
                                                        <input type="hidden" name="action" value="selectionner">
                                                        <input type="hidden" name="offre_id" value="<?= $o['id'] ?>">
                                                        <input type="hidden" name="justification" value="Meilleur rapport qualité/prix">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="bi bi-check-circle"></i> Sélectionner
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>