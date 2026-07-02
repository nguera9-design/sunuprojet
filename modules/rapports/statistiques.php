<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

// Statistiques globales
$stats = [];

// Total des commandes
$stmt = $pdo->query("SELECT COUNT(*) FROM bons_commande WHERE statut NOT IN ('annule', 'brouillon')");
$stats['total_commandes'] = $stmt->fetchColumn();

// Total des achats
$stmt = $pdo->query("SELECT COALESCE(SUM(total_ht), 0) FROM bons_commande WHERE statut NOT IN ('annule', 'brouillon')");
$stats['total_achats'] = $stmt->fetchColumn();

// Total des fournisseurs
$stmt = $pdo->query("SELECT COUNT(*) FROM fournisseurs WHERE actif = 1");
$stats['total_fournisseurs'] = $stmt->fetchColumn();

// Nombre de produits
$stmt = $pdo->query("SELECT COUNT(*) FROM produits WHERE actif = 1");
$stats['total_produits'] = $stmt->fetchColumn();

// Commandes par mois (12 derniers mois)
$commandes_par_mois = $pdo->query("
    SELECT 
        DATE_FORMAT(date_emission, '%Y-%m') as mois,
        COUNT(*) as nb_commandes,
        COALESCE(SUM(total_ht), 0) as total
    FROM bons_commande
    WHERE date_emission >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        AND statut NOT IN ('annule', 'brouillon')
    GROUP BY DATE_FORMAT(date_emission, '%Y-%m')
    ORDER BY mois ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Top fournisseurs
$top_fournisseurs = $pdo->query("
    SELECT 
        f.nom,
        COALESCE(SUM(bc.total_ht), 0) as total
    FROM fournisseurs f
    LEFT JOIN bons_commande bc ON f.id = bc.fournisseur_id
        AND bc.statut NOT IN ('annule', 'brouillon')
    WHERE f.actif = 1
    GROUP BY f.id
    ORDER BY total DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Dépenses par catégorie
$depenses_categorie = $pdo->query("
    SELECT 
        c.nom as categorie_nom,
        COALESCE(SUM(bcl.total_ht), 0) as total
    FROM categories c
    LEFT JOIN produits p ON p.categorie_id = c.id
    LEFT JOIN bons_commande_lignes bcl ON bcl.produit_id = p.id
    LEFT JOIN bons_commande bc ON bc.id = bcl.bon_commande_id
        AND bc.statut NOT IN ('annule', 'brouillon')
    WHERE c.actif = 1
    GROUP BY c.id
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport statistique - Achats</title>
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
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <h2>📊 Rapport statistique des achats</h2>
                    <div>
                        <button onclick="window.print()" class="btn btn-danger">
                            <i class="bi bi-file-pdf"></i> Exporter en PDF
                        </button>
                        <a href="../../dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <!-- Résumé -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white p-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" style="font-size:28px;font-weight:700;"><?= number_format($stats['total_commandes'], 0, ',', ' ') ?></div>
                                    <div class="stat-label">Commandes</div>
                                </div>
                                <i class="bi bi-receipt fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white p-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" style="font-size:28px;font-weight:700;"><?= number_format($stats['total_achats'], 0, ',', ' ') ?> FCFA</div>
                                    <div class="stat-label">Total achats</div>
                                </div>
                                <i class="bi bi-cash fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark p-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" style="font-size:28px;font-weight:700;"><?= number_format($stats['total_fournisseurs'], 0, ',', ' ') ?></div>
                                    <div class="stat-label">Fournisseurs actifs</div>
                                </div>
                                <i class="bi bi-building fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white p-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" style="font-size:28px;font-weight:700;"><?= number_format($stats['total_produits'], 0, ',', ' ') ?></div>
                                    <div class="stat-label">Produits</div>
                                </div>
                                <i class="bi bi-box fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commandes par mois -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <i class="bi bi-calendar3"></i> Évolution des commandes (12 mois)
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mois</th>
                                        <th>Nombre de commandes</th>
                                        <th>Total HT</th>
                                        <th>Moyenne par commande</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commandes_par_mois as $c): ?>
                                    <tr>
                                        <td><?= date('F Y', strtotime($c['mois'] . '-01')) ?></td>
                                        <td><?= $c['nb_commandes'] ?></td>
                                        <td><?= number_format($c['total'], 0, ',', ' ') ?> FCFA</td>
                                        <td><?= number_format($c['nb_commandes'] > 0 ? $c['total'] / $c['nb_commandes'] : 0, 0, ',', ' ') ?> FCFA</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top fournisseurs -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-trophy"></i> Top 10 fournisseurs
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fournisseur</th>
                                        <th>Total achats</th>
                                        <th>% du total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($top_fournisseurs as $f): ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td><?= htmlspecialchars($f['nom']) ?></td>
                                        <td><?= number_format($f['total'], 0, ',', ' ') ?> FCFA</td>
                                        <td>
                                            <?= $stats['total_achats'] > 0 ? round(($f['total'] / $stats['total_achats']) * 100, 1) : 0 ?>%
                                        </td>
                                    </tr>
                                    <?php $i++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Dépenses par catégorie -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-pie-chart"></i> Dépenses par catégorie
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Catégorie</th>
                                        <th>Total</th>
                                        <th>% du total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($depenses_categorie as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['categorie_nom'] ?? 'Non catégorisé') ?></td>
                                        <td><?= number_format($c['total'], 0, ',', ' ') ?> FCFA</td>
                                        <td>
                                            <?= $stats['total_achats'] > 0 ? round(($c['total'] / $stats['total_achats']) * 100, 1) : 0 ?>%
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-muted text-center mt-4">
                    <small>Rapport généré le <?= date('d/m/Y H:i') ?></small>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>