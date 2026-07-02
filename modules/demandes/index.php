<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

// Récupérer les demandes
$demandes = $pdo->query("
    SELECT da.*, u.nom, u.prenom 
    FROM demandes_achat da
    LEFT JOIN utilisateurs u ON da.demandeur_id = u.id
    ORDER BY da.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Couleurs des statuts
$statut_couleur = [
    'brouillon' => 'secondary',
    'en_validation' => 'warning',
    'validee' => 'success',
    'rejetee' => 'danger',
    'transformee_en_bc' => 'info'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes d'achat</title>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>📄 Demandes d'achat</h2>
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouvelle demande
                    </a>
                </div>

                <?php if (empty($demandes)): ?>
                    <div class="alert alert-info">Aucune demande d'achat enregistrée.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>N°</th>
                                    <th>Titre</th>
                                    <th>Demandeur</th>
                                    <th>Date</th>
                                    <th>Budget</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($demandes as $d): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($d['numero']) ?></strong></td>
                                    <td><?= htmlspecialchars($d['titre']) ?></td>
                                    <td><?= htmlspecialchars($d['prenom'] ?? '') ?> <?= htmlspecialchars($d['nom'] ?? '') ?></td>
                                    <td><?= date('d/m/Y', strtotime($d['date_demande'])) ?></td>
                                    <td><?= number_format($d['budget_estime_ht'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <span class="badge bg-<?= $statut_couleur[$d['statut']] ?? 'secondary' ?>">
                                            <?= str_replace('_', ' ', $d['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($d['statut'] == 'brouillon'): ?>
                                            <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="soumettre.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Soumettre cette demande ?')">
                                                <i class="bi bi-send"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($d['statut'] == 'en_validation' && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'validateur')): ?>
                                            <a href="valider.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Valider cette demande ?')">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                            <a href="rejeter.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Rejeter cette demande ?')">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($d['statut'] == 'validee' && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur')): ?>
                                            <a href="../bons_commande/create.php?demande_id=<?= $d['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-receipt"></i> BC
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