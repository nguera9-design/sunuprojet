<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

// Récupérer les mouvements
$stmt = $pdo->query("
    SELECT ms.*, p.code as produit_code, p.nom as produit_nom, u.nom, u.prenom
    FROM mouvements_stock ms
    LEFT JOIN produits p ON ms.produit_id = p.id
    LEFT JOIN utilisateurs u ON ms.created_by = u.id
    ORDER BY ms.created_at DESC
    LIMIT 100
");
$mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mouvements de stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php">🏢 Gestion Achats</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link text-danger" href="../../logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar" style="min-height: 100vh; padding: 20px 0;">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="../../dashboard.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-clipboard-data"></i> Stock</a></li>
                </ul>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>📜 Historique des mouvements</h2>
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
                </div>

                <?php if (empty($mouvements)): ?>
                    <div class="alert alert-info">Aucun mouvement enregistré.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Produit</th>
                                    <th>Type</th>
                                    <th>Quantité</th>
                                    <th>Stock avant</th>
                                    <th>Stock après</th>
                                    <th>Référence</th>
                                    <th>Utilisateur</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mouvements as $m): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($m['produit_code'] ?? '') ?> - <?= htmlspecialchars($m['produit_nom'] ?? '') ?></td>
                                    <td>
                                        <?php 
                                        $couleur = 'secondary';
                                        if ($m['type_mouvement'] == 'entree') $couleur = 'success';
                                        elseif ($m['type_mouvement'] == 'sortie') $couleur = 'danger';
                                        elseif ($m['type_mouvement'] == 'ajustement_plus') $couleur = 'info';
                                        elseif ($m['type_mouvement'] == 'ajustement_moins') $couleur = 'warning';
                                        ?>
                                        <span class="badge bg-<?= $couleur ?>">
                                            <?= str_replace('_', ' ', $m['type_mouvement']) ?>
                                        </span>
                                    </td>
                                    <td><strong><?= $m['quantite'] ?></strong></td>
                                    <td><?= $m['stock_avant'] ?></td>
                                    <td><?= $m['stock_apres'] ?></td>
                                    <td><?= htmlspecialchars($m['reference_type']) ?>: <?= substr($m['reference_id'], 0, 8) ?></td>
                                    <td><?= htmlspecialchars($m['prenom'] ?? '') ?> <?= htmlspecialchars($m['nom'] ?? '') ?></td>
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