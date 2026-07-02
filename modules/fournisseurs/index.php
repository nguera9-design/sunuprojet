<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

// Récupérer les fournisseurs avec leur note moyenne sur 12 mois
$fournisseurs = $pdo->query("
    SELECT 
        f.*,
        AVG(
            (f.notation_qualite + f.notation_delai + f.notation_prix + f.notation_service) / 4
        ) as note_moyenne,
        COUNT(bc.id) as nb_commandes,
        COALESCE(SUM(bc.total_ht), 0) as total_achats
    FROM fournisseurs f
    LEFT JOIN bons_commande bc ON f.id = bc.fournisseur_id 
        AND bc.date_emission >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        AND bc.statut NOT IN ('annule', 'brouillon')
    WHERE f.actif = 1
    GROUP BY f.id
    ORDER BY f.nom
")->fetchAll(PDO::FETCH_ASSOC);

// Mise à jour des notes moyennes en base
foreach ($fournisseurs as $f) {
    if ($f['note_moyenne'] !== null) {
        // On ne met pas à jour automatiquement pour garder l'historique
        // La note affichée est calculée à partir des 4 critères
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fournisseurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>🏢 Fournisseurs</h2>
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouveau fournisseur
                    </a>
                </div>

                <?php if (empty($fournisseurs)): ?>
                    <div class="alert alert-info">Aucun fournisseur enregistré.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Note moyenne</th>
                                    <th>Nb commandes (12 mois)</th>
                                    <th>Total achats (12 mois)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fournisseurs as $f): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($f['code']) ?></strong></td>
                                    <td><?= htmlspecialchars($f['nom']) ?></td>
                                    <td><?= htmlspecialchars($f['email'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($f['telephone'] ?? '—') ?></td>
                                    <td>
                                        <?php 
                                        $note = round($f['note_moyenne'] ?? 0, 1);
                                        echo str_repeat('⭐', round($note));
                                        if ($note == 0) echo '—';
                                        ?>
                                        <span class="badge bg-secondary"><?= $note ?>/5</span>
                                    </td>
                                    <td><span class="badge bg-info"><?= $f['nb_commandes'] ?></span></td>
                                    <td><?= number_format($f['total_achats'], 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <a href="edit.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce fournisseur ?')">
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