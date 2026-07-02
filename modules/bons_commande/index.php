<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$bons = $pdo->query("
    SELECT bc.*, f.nom as fournisseur_nom
    FROM bons_commande bc
    LEFT JOIN fournisseurs f ON bc.fournisseur_id = f.id
    ORDER BY bc.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Bons de commande</title>
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
                    <h2>📄 Bons de commande</h2>
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouveau bon de commande
                    </a>
                </div>

                <?php if (empty($bons)): ?>
                    <div class="alert alert-info">Aucun bon de commande enregistré.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>N°</th>
                                    <th>Fournisseur</th>
                                    <th>Date</th>
                                    <th>Total TTC</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bons as $b): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($b['numero']) ?></strong></td>
                                    <td><?= htmlspecialchars($b['fournisseur_nom'] ?? '—') ?></td>
                                    <td><?= date('d/m/Y', strtotime($b['date_emission'])) ?></td>
                                    <td><?= number_format($b['total_ttc'], 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <span class="badge bg-<?= $statut_couleur[$b['statut']] ?? 'secondary' ?>">
                                            <?= str_replace('_', ' ', $b['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($b['statut'] == 'brouillon'): ?>
                                            <a href="envoyer.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Envoyer ce BC ?')">
                                                <i class="bi bi-send"></i>
                                            </a>
                                        <?php endif; ?>
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