<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$livraisons = $pdo->query("
    SELECT l.*, bc.numero as bc_numero, f.nom as fournisseur_nom
    FROM livraisons l
    LEFT JOIN bons_commande bc ON l.bon_commande_id = bc.id
    LEFT JOIN fournisseurs f ON bc.fournisseur_id = f.id
    ORDER BY l.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraisons</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>🚚 Livraisons</h2>
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouvelle réception
                    </a>
                </div>

                <?php if (empty($livraisons)): ?>
                    <div class="alert alert-info">Aucune livraison enregistrée.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>N°</th>
                                    <th>BC</th>
                                    <th>Fournisseur</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($livraisons as $l): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($l['numero']) ?></strong></td>
                                    <td><?= htmlspecialchars($l['bc_numero'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($l['fournisseur_nom'] ?? '—') ?></td>
                                    <td><?= date('d/m/Y', strtotime($l['date_livraison'])) ?></td>
                                    <td><span class="badge bg-success"><?= $l['statut'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>