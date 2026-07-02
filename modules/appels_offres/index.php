<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

// Récupérer les appels d'offres
$appels = $pdo->query("
    SELECT ao.*, da.titre as demande_titre, da.numero as demande_numero
    FROM appels_offres ao
    LEFT JOIN demandes_achat da ON ao.demande_achat_id = da.id
    ORDER BY ao.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$statut_couleur = [
    'en_cours' => 'primary',
    'clos' => 'secondary',
    'annule' => 'danger'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appels d'offres</title>
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
                    <h2>📢 Appels d'offres</h2>
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouvel appel d'offres
                    </a>
                </div>

                <?php if (empty($appels)): ?>
                    <div class="alert alert-info">Aucun appel d'offres enregistré.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>N°</th>
                                    <th>Titre</th>
                                    <th>Demande associée</th>
                                    <th>Date limite</th>
                                    <th>Montant estimé</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appels as $a): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($a['numero']) ?></strong></td>
                                    <td><?= htmlspecialchars($a['titre']) ?></td>
                                    <td><?= htmlspecialchars($a['demande_numero'] ?? '—') ?></td>
                                    <td><?= date('d/m/Y', strtotime($a['date_limite_reponse'])) ?></td>
                                    <td><?= number_format($a['montant_estime_ht'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <span class="badge bg-<?= $statut_couleur[$a['statut']] ?? 'secondary' ?>">
                                            <?= str_replace('_', ' ', $a['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($a['statut'] == 'en_cours'): ?>
                                            <a href="offres.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-success">
                                                <i class="bi bi-file-earmark-text"></i> Offres
                                            </a>
                                            <a href="cloturer.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-warning" onclick="return confirm('Clôturer cet appel d\'offres ?')">
                                                <i class="bi bi-check-circle"></i>
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