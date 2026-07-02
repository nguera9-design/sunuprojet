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

// Récupérer la facture
$stmt = $pdo->prepare("
    SELECT f.*, 
           fo.nom as fournisseur_nom, 
           fo.email as fournisseur_email,
           fo.telephone as fournisseur_telephone,
           bc.numero as bc_numero
    FROM factures f
    LEFT JOIN fournisseurs fo ON f.fournisseur_id = fo.id
    LEFT JOIN bons_commande bc ON f.bon_commande_id = bc.id
    WHERE f.id = ?
");
$stmt->execute([$id]);
$facture = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    header('Location: index.php');
    exit();
}

// Récupérer les paiements
$paiements = $pdo->prepare("
    SELECT * FROM paiements WHERE facture_id = ? ORDER BY date_paiement DESC
");
$paiements->execute([$id]);
$paiements = $paiements->fetchAll(PDO::FETCH_ASSOC);

$statut_couleur = [
    'en_attente' => 'warning',
    'approuve' => 'info',
    'effectue' => 'success',
    'echoue' => 'danger'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail facture - <?= htmlspecialchars($facture['numero']) ?></title>
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
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <h2>💳 Détail de la facture</h2>
                    <div>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                        <?php if ($facture['statut'] == 'en_attente'): ?>
                            <a href="approuver.php?id=<?= $facture['id'] ?>" class="btn btn-success" onclick="return confirm('Approuver cette facture ?')">
                                <i class="bi bi-check-circle"></i> Approuver
                            </a>
                            <a href="paiement.php?id=<?= $facture['id'] ?>" class="btn btn-primary">
                                <i class="bi bi-cash"></i> Payer
                            </a>
                        <?php endif; ?>
                        <?php if ($facture['statut'] == 'approuve'): ?>
                            <a href="paiement.php?id=<?= $facture['id'] ?>" class="btn btn-primary">
                                <i class="bi bi-cash"></i> Payer
                            </a>
                        <?php endif; ?>
                        <button onclick="window.print()" class="btn btn-danger">
                            <i class="bi bi-file-pdf"></i> Imprimer PDF
                        </button>
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
                                    <tr>
                                        <th>N° facture</th>
                                        <td><strong><?= htmlspecialchars($facture['numero']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>N° facture fournisseur</th>
                                        <td><?= htmlspecialchars($facture['numero_facture_fournisseur'] ?? '—') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Statut</th>
                                        <td>
                                            <span class="badge bg-<?= $statut_couleur[$facture['statut']] ?? 'secondary' ?>">
                                                <?= str_replace('_', ' ', $facture['statut']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Date d'émission</th>
                                        <td><?= date('d/m/Y', strtotime($facture['date_emission'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date d'échéance</th>
                                        <td>
                                            <?= date('d/m/Y', strtotime($facture['date_echeance'])) ?>
                                            <?php if ($facture['statut'] != 'effectue' && strtotime($facture['date_echeance']) < time()): ?>
                                                <span class="badge bg-danger">En retard</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if ($facture['date_paiement']): ?>
                                    <tr>
                                        <th>Date de paiement</th>
                                        <td><?= date('d/m/Y', strtotime($facture['date_paiement'])) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th>BC associé</th>
                                        <td><?= htmlspecialchars($facture['bc_numero'] ?? '—') ?></td>
                                    </tr>
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
                                    <tr>
                                        <th>Nom</th>
                                        <td><?= htmlspecialchars($facture['fournisseur_nom'] ?? '—') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?= htmlspecialchars($facture['fournisseur_email'] ?? '—') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Téléphone</th>
                                        <td><?= htmlspecialchars($facture['fournisseur_telephone'] ?? '—') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <i class="bi bi-cash"></i> Montants
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Total HT</th>
                                        <td class="text-end"><?= number_format($facture['total_ht'], 0, ',', ' ') ?> FCFA</td>
                                    </tr>
                                    <tr>
                                        <th>Total TVA</th>
                                        <td class="text-end"><?= number_format($facture['total_tva'], 0, ',', ' ') ?> FCFA</td>
                                    </tr>
                                    <tr class="table-success">
                                        <th><strong>TOTAL TTC</strong></th>
                                        <td class="text-end"><strong><?= number_format($facture['total_ttc'], 0, ',', ' ') ?> FCFA</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paiements -->
                <?php if (!empty($paiements)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-credit-card"></i> Historique des paiements
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Référence</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paiements as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['numero']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                                    <td><?= number_format($p['montant'], 0, ',', ' ') ?> FCFA</td>
                                    <td><?= str_replace('_', ' ', $p['methode']) ?></td>
                                    <td><?= htmlspecialchars($p['reference'] ?? '—') ?></td>
                                    <td><span class="badge bg-success">Effectué</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($facture['notes']): ?>
                <div class="card">
                    <div class="card-header">Notes</div>
                    <div class="card-body"><?= nl2br(htmlspecialchars($facture['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>