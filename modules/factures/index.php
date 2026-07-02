<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$factures = $pdo->query("
    SELECT f.*, fo.nom as fournisseur_nom, bc.numero as bc_numero
    FROM factures f
    LEFT JOIN fournisseurs fo ON f.fournisseur_id = fo.id
    LEFT JOIN bons_commande bc ON f.bon_commande_id = bc.id
    ORDER BY f.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Factures</title>
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
                    <h2>💳 Factures</h2>
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouvelle facture
                    </a>
                </div>

                <?php if (empty($factures)): ?>
                    <div class="alert alert-info">Aucune facture enregistrée.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>N°</th>
                                    <th>Fournisseur</th>
                                    <th>Échéance</th>
                                    <th>Total TTC</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($factures as $f): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($f['numero']) ?></strong></td>
                                    <td><?= htmlspecialchars($f['fournisseur_nom'] ?? '—') ?></td>
                                    <td><?= date('d/m/Y', strtotime($f['date_echeance'])) ?></td>
                                    <td><?= number_format($f['total_ttc'], 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <span class="badge bg-<?= $statut_couleur[$f['statut']] ?? 'secondary' ?>">
                                            <?= str_replace('_', ' ', $f['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
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