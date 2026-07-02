<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$produits = $pdo->query("
    SELECT p.*, c.nom as categorie_nom
    FROM produits p
    LEFT JOIN categories c ON p.categorie_id = c.id
    WHERE p.actif = 1
    ORDER BY 
        CASE 
            WHEN p.stock_actuel <= p.seuil_alerte THEN 0
            ELSE 1
        END,
        p.nom
")->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$total_produits = $pdo->query("SELECT COUNT(*) FROM produits WHERE actif = 1")->fetchColumn();
$alertes = $pdo->query("SELECT COUNT(*) FROM produits WHERE stock_actuel <= seuil_alerte AND seuil_alerte > 0 AND actif = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>📊 Gestion de stock</h2>

                <!-- Statistiques -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card text-white bg-primary p-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" style="font-size:32px;font-weight:700;"><?= $total_produits ?></div>
                                    <div class="stat-label">Total produits</div>
                                </div>
                                <i class="bi bi-box fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-danger p-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" style="font-size:32px;font-weight:700;"><?= $alertes ?></div>
                                    <div class="stat-label">⚠️ Alertes stock</div>
                                </div>
                                <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Unité</th>
                                <th>Stock</th>
                                <th>Seuil</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produits as $p): ?>
                            <?php 
                            $statut = '✅ OK';
                            $classe = '';
                            if ($p['stock_actuel'] <= $p['seuil_alerte'] && $p['seuil_alerte'] > 0) {
                                $statut = '⚠️ ALERTE';
                                $classe = 'table-danger';
                            }
                            ?>
                            <tr class="<?= $classe ?>">
                                <td><strong><?= htmlspecialchars($p['code']) ?></strong></td>
                                <td><?= htmlspecialchars($p['nom']) ?></td>
                                <td><?= htmlspecialchars($p['categorie_nom'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($p['unite_mesure']) ?></td>
                                <td><strong><?= $p['stock_actuel'] ?></strong></td>
                                <td><?= $p['seuil_alerte'] ?></td>
                                <td><?= $statut ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>