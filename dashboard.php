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

// Statistiques
$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) FROM demandes_achat");
$stats['demandes'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM bons_commande");
$stats['commandes'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM fournisseurs WHERE actif = 1");
$stats['fournisseurs'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM produits WHERE actif = 1");
$stats['produits'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM produits WHERE stock_actuel <= seuil_alerte AND actif = 1");
$stats['alertes_stock'] = $stmt->fetchColumn();

// Dernières activités
$activites = $pdo->query("
    SELECT numero, titre, created_at 
    FROM demandes_achat 
    ORDER BY created_at DESC LIMIT 5
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
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .card:hover { transform: translateY(-3px); transition: transform 0.2s; }
        .stat-number { font-size: 32px; font-weight: 700; }
        .stat-label { font-size: 14px; opacity: 0.8; }
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

                <!-- Alerte stock -->
                <?php if ($stats['alertes_stock'] > 0): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Attention !</strong> <?= $stats['alertes_stock'] ?> produit(s) sont en dessous du seuil d'alerte.
                    <a href="modules/stock/index.php" class="alert-link">Voir les stocks</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Dernières demandes -->
                <div class="card mt-4">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-clock-history me-2"></i> Dernières demandes d'achat
                    </div>
                    <div class="card-body">
                        <?php if (empty($activites)): ?>
                            <p class="text-muted text-center my-3">Aucune demande d'achat enregistrée</p>
                        <?php else: ?>
                            <div class="list-group">
                            <?php foreach ($activites as $act): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark me-2 text-primary"></i>
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

                <footer class="text-center text-muted mt-5 py-3 border-top">
                    <small>© <?= date('Y') ?> - Gestion des Achats et Approvisionnements | Groupe 14</small>
                </footer>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>