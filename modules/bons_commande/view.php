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

$stmt = $pdo->prepare("
    SELECT bc.*, f.nom as fournisseur_nom, f.email as fournisseur_email, f.telephone as fournisseur_telephone
    FROM bons_commande bc
    LEFT JOIN fournisseurs f ON bc.fournisseur_id = f.id
    WHERE bc.id = ?
");
$stmt->execute([$id]);
$bc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bc) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT bcl.*, p.code as produit_code, p.nom as produit_nom, p.unite_mesure
    FROM bons_commande_lignes bcl
    LEFT JOIN produits p ON bcl.produit_id = p.id
    WHERE bcl.bon_commande_id = ?
");
$stmt->execute([$id]);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statut_couleur = [
    'brouillon' => 'secondary',
    'envoye' => 'primary',
    'confirme' => 'info',
    'expedie' => 'warning',
    'recu_partiel' => 'warning',
    'recu_total' => 'success',
    'facture' => 'success',
    'paye' => 'success',
    'annule' => 'danger'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail BC - <?= htmlspecialchars($bc['numero']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        @media print {
            .no-print { display: none !important; }
            .sidebar { display: none !important; }
            main { width: 100% !important; margin: 0 !important; padding: 20px !important; }
        }
        .badge-statut { font-size: 14px; padding: 6px 14px; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <h2>📄 Détail du bon de commande</h2>
                    <div>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                        <!-- Export PDF avec la version HTML -->
                        <a href="pdf_simple.php?id=<?= $bc['id'] ?>" target="_blank" class="btn btn-danger">
                            <i class="bi bi-file-pdf"></i> Télécharger PDF
                        </a>
                        <?php if ($bc['statut'] == 'brouillon' && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur')): ?>
                            <a href="envoyer.php?id=<?= $bc['id'] ?>" class="btn btn-primary" onclick="return confirm('Envoyer ce bon de commande au fournisseur ?')">
                                <i class="bi bi-send"></i> Envoyer
                            </a>
                        <?php endif; ?>
                        <?php if ($bc['statut'] != 'annule' && $bc['statut'] != 'paye' && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur')): ?>
                            <a href="annuler.php?id=<?= $bc['id'] ?>" class="btn btn-warning" onclick="return confirm('Annuler ce bon de commande ?')">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <i class="bi bi-info-circle"></i> Informations
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><th>N°</th><td><strong><?= htmlspecialchars($bc['numero']) ?></strong></td></tr>
                                    <tr>
                                        <th>Statut</th>
                                        <td>
                                            <span class="badge bg-<?= $statut_couleur[$bc['statut']] ?? 'secondary' ?> badge-statut">
                                                <?= str_replace('_', ' ', $bc['statut']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr><th>Date émission</th><td><?= date('d/m/Y', strtotime($bc['date_emission'])) ?></td></tr>
                                    <tr><th>Livraison prévue</th><td><?= $bc['date_livraison_prevue'] ? date('d/m/Y', strtotime($bc['date_livraison_prevue'])) : '—' ?></td></tr>
                                    <?php if ($bc['date_livraison_effective']): ?>
                                    <tr><th>Livrée le</th><td><?= date('d/m/Y', strtotime($bc['date_livraison_effective'])) ?></td></tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <i class="bi bi-building"></i> Fournisseur
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><th>Nom</th><td><?= htmlspecialchars($bc['fournisseur_nom'] ?? '—') ?></td></tr>
                                    <tr><th>Email</th><td><?= htmlspecialchars($bc['fournisseur_email'] ?? '—') ?></td></tr>
                                    <tr><th>Téléphone</th><td><?= htmlspecialchars($bc['fournisseur_telephone'] ?? '—') ?></td></tr>
                                    <tr><th>Conditions paiement</th><td><?= htmlspecialchars($bc['conditions_paiement'] ?? '—') ?></td></tr>
                                    <tr><th>Conditions livraison</th><td><?= htmlspecialchars($bc['conditions_livraison'] ?? '—') ?></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lignes -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <i class="bi bi-list-check"></i> Lignes du bon de commande
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>PU HT</th>
                                        <th>Remise</th>
                                        <th>TVA</th>
                                        <th>Total HT</th>
                                        <th>Total TTC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($lignes as $l): ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td><?= htmlspecialchars($l['produit_code'] ?? '') ?> - <?= htmlspecialchars($l['produit_nom'] ?? '') ?></td>
                                        <td><?= $l['quantite'] . ' ' . $l['unite_mesure'] ?></td>
                                        <td><?= number_format($l['prix_unitaire_ht'], 0, ',', ' ') ?></td>
                                        <td><?= $l['remise'] ?>%</td>
                                        <td><?= $l['taux_tva'] ?>%</td>
                                        <td><?= number_format($l['total_ht'], 0, ',', ' ') ?></td>
                                        <td><strong><?= number_format($l['total_ttc'], 0, ',', ' ') ?></strong></td>
                                    </tr>
                                    <?php $i++; endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <th colspan="6" class="text-end">Totaux</th>
                                        <th><?= number_format($bc['total_ht'], 0, ',', ' ') ?> FCFA</th>
                                        <th><strong><?= number_format($bc['total_ttc'], 0, ',', ' ') ?> FCFA</strong></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($bc['notes']): ?>
                <div class="card">
                    <div class="card-header">📝 Notes</div>
                    <div class="card-body"><?= nl2br(htmlspecialchars($bc['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>