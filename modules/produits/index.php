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
    ORDER BY p.nom
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits</title>
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
                    <h2>📦 Produits</h2>
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouveau produit
                    </a>
                </div>

                <?php if (empty($produits)): ?>
                    <div class="alert alert-info">Aucun produit enregistré.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Unité</th>
                                    <th>Prix réf.</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produits as $p): ?>
                                <tr <?= ($p['stock_actuel'] <= $p['seuil_alerte'] && $p['seuil_alerte'] > 0) ? 'class="table-danger"' : '' ?>>
                                    <td><strong><?= htmlspecialchars($p['code']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['nom']) ?></td>
                                    <td><?= htmlspecialchars($p['categorie_nom'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($p['unite_mesure']) ?></td>
                                    <td><?= number_format($p['prix_reference_ht'], 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <?= $p['stock_actuel'] ?>
                                        <?php if ($p['stock_actuel'] <= $p['seuil_alerte'] && $p['seuil_alerte'] > 0): ?>
                                            <span class="badge bg-danger">Alerte</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce produit ?')">
                                            <i class="bi bi-trash"></i>
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