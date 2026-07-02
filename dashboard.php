<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sunuprojet;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// === STATISTIQUES GÉNÉRALES ===
$stats = [];

// Nombre de demandes
$stmt = $pdo->query("SELECT COUNT(*) FROM demandes_achat");
$stats['demandes'] = $stmt->fetchColumn();

// Nombre de bons de commande
$stmt = $pdo->query("SELECT COUNT(*) FROM bons_commande");
$stats['commandes'] = $stmt->fetchColumn();

// Nombre de fournisseurs
$stmt = $pdo->query("SELECT COUNT(*) FROM fournisseurs WHERE actif = 1");
$stats['fournisseurs'] = $stmt->fetchColumn();

// Nombre de produits
$stmt = $pdo->query("SELECT COUNT(*) FROM produits WHERE actif = 1");
$stats['produits'] = $stmt->fetchColumn();

// Alertes stock
$stmt = $pdo->query("SELECT COUNT(*) FROM produits WHERE stock_actuel <= seuil_alerte AND actif = 1");
$stats['alertes_stock'] = $stmt->fetchColumn();

// === COMMANDES EN COURS ===
$commandes_en_cours = $pdo->query("
    SELECT COUNT(*) FROM bons_commande 
    WHERE statut IN ('envoye', 'confirme', 'expedie', 'recu_partiel')
")->fetchColumn();

// === DÉPENSES PAR CATÉGORIE (12 mois) ===
$depenses_categorie = $pdo->query("
    SELECT 
        c.nom as categorie_nom,
        COALESCE(SUM(bc.total_ht), 0) as total
    FROM categories c
    LEFT JOIN produits p ON p.categorie_id = c.id
    LEFT JOIN bons_commande_lignes bcl ON bcl.produit_id = p.id
    LEFT JOIN bons_commande bc ON bc.id = bcl.bon_commande_id
        AND bc.date_emission >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        AND bc.statut NOT IN ('annule', 'brouillon')
    WHERE c.actif = 1
    GROUP BY c.id
    ORDER BY total DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// === TOP FOURNISSEURS (12 mois) ===
$top_fournisseurs = $pdo->query("
    SELECT 
        f.nom,
        f.code,
        COALESCE(SUM(bc.total_ht), 0) as total_achats,
        COUNT(bc.id) as nb_commandes
    FROM fournisseurs f
    LEFT JOIN bons_commande bc ON f.id = bc.fournisseur_id
        AND bc.date_emission >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        AND bc.statut NOT IN ('annule', 'brouillon')
    WHERE f.actif = 1
    GROUP BY f.id
    ORDER BY total_achats DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// === DERNIÈRES ACTIVITÉS ===
$activites = $pdo->query("
    SELECT 'demande' as type, numero, titre, created_at 
    FROM demandes_achat 
    UNION ALL
    SELECT 'commande' as type, numero, CONCAT('BC ', numero) as titre, created_at 
    FROM bons_commande
    ORDER BY created_at DESC LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Gestion des Achats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .card:hover { transform: translateY(-3px); transition: transform 0.2s; }
        .stat-number { font-size: 32px; font-weight: 700; }
        .stat-label { font-size: 14px; opacity: 0.8; }
        .chart-container { height: 250px; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold">📊 Tableau de bord</h2>
                        <p class="text-muted">Bienvenue <?= htmlspecialchars($_SESSION['prenom']) ?> <?= htmlspecialchars($_SESSION['nom']) ?> !</p>
                    </div>
                    <span class="badge bg-success fs-6 p-2"><?= date('d/m/Y H:i') ?></span>
                </div>
                
                <!-- Cartes statistiques -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?= $stats['demandes'] ?></div>
                                    <div class="stat-label">Demandes d'achat</div>
                                </div>
                                <i class="bi bi-file-earmark-plus fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?= $stats['commandes'] ?></div>
                                    <div class="stat-label">Bons de commande</div>
                                </div>
                                <i class="bi bi-receipt fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?= $stats['fournisseurs'] ?></div>
                                    <div class="stat-label">Fournisseurs actifs</div>
                                </div>
                                <i class="bi bi-building fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?= $stats['alertes_stock'] ?></div>
                                    <div class="stat-label">Alertes stock</div>
                                </div>
                                <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deuxième ligne : Commandes en cours -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-info text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?= $commandes_en_cours ?></div>
                                    <div class="stat-label">🚚 Commandes en cours</div>
                                </div>
                                <i class="bi bi-truck fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?= $stats['produits'] ?></div>
                                    <div class="stat-label">📦 Produits en catalogue</div>
                                </div>
                                <i class="bi bi-box fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-dark text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?= number_format($stats['commandes'] > 0 ? round($stats['commandes'] / max($stats['fournisseurs'], 1), 1) : 0, 1) ?></div>
                                    <div class="stat-label">📊 Commandes / Fournisseur</div>
                                </div>
                                <i class="bi bi-bar-chart fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertes -->
                <?php if ($stats['alertes_stock'] > 0): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Attention !</strong> <?= $stats['alertes_stock'] ?> produit(s) sont en dessous du seuil d'alerte.
                    <a href="modules/stock/index.php" class="alert-link">Voir les stocks</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Graphiques -->
                <div class="row g-3 mb-4">
                    <!-- Dépenses par catégorie -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white fw-bold">
                                <i class="bi bi-pie-chart me-2"></i> Dépenses par catégorie (12 mois)
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartCategories"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top fournisseurs -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white fw-bold">
                                <i class="bi bi-trophy me-2"></i> Top fournisseurs (12 mois)
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartFournisseurs"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dernières activités -->
                <div class="card mt-4">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-clock-history me-2"></i> Dernières activités
                    </div>
                    <div class="card-body">
                        <?php if (empty($activites)): ?>
                            <p class="text-muted text-center my-3">Aucune activité récente</p>
                        <?php else: ?>
                            <div class="list-group">
                            <?php foreach ($activites as $act): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if ($act['type'] == 'demande'): ?>
                                            <i class="bi bi-file-earmark me-2 text-primary"></i>
                                        <?php else: ?>
                                            <i class="bi bi-receipt me-2 text-success"></i>
                                        <?php endif; ?>
                                        <strong><?= htmlspecialchars($act['numero']) ?></strong>
                                        <span class="ms-2"><?= htmlspecialchars($act['titre']) ?></span>
                                    </div>
                                    <span class="badge bg-secondary"><?= date('d/m/Y H:i', strtotime($act['created_at'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer -->
                <footer class="text-center text-muted mt-5 py-3 border-top">
                    <small>© <?= date('Y') ?> - Gestion des Achats et Approvisionnements | Groupe 14</small>
                </footer>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Graphique des dépenses par catégorie
    const ctx1 = document.getElementById('chartCategories').getContext('2d');
    const categoriesData = <?= json_encode(array_column($depenses_categorie, 'categorie_nom')) ?>;
    const categoriesValues = <?= json_encode(array_column($depenses_categorie, 'total')) ?>;

    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: categoriesData.length ? categoriesData : ['Aucune donnée'],
            datasets: [{
                data: categoriesValues.length ? categoriesValues : [1],
                backgroundColor: ['#dc3545', '#ffc107', '#198754', '#0dcaf0', '#6c757d', '#6610f2'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Graphique des top fournisseurs
    const ctx2 = document.getElementById('chartFournisseurs').getContext('2d');
    const fournisseursLabels = <?= json_encode(array_column($top_fournisseurs, 'nom')) ?>;
    const fournisseursValues = <?= json_encode(array_column($top_fournisseurs, 'total_achats')) ?>;

    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: fournisseursLabels.length ? fournisseursLabels : ['Aucun'],
            datasets: [{
                label: 'Achats (FCFA)',
                data: fournisseursValues.length ? fournisseursValues : [0],
                backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b'],
                borderColor: '#333',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' FCFA';
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>