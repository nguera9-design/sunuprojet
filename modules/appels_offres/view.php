<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    header('Location: index.php');
    exit();
}

// Récupérer l'appel d'offres
$stmt = $pdo->prepare("
    SELECT ao.*, da.titre as demande_titre, da.numero as demande_numero
    FROM appels_offres ao
    LEFT JOIN demandes_achat da ON ao.demande_achat_id = da.id
    WHERE ao.id = ?
");
$stmt->execute([$id]);
$appel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appel) {
    header('Location: index.php');
    exit();
}

// Récupérer les offres reçues
$offres = $pdo->prepare("
    SELECT of.*, f.nom as fournisseur_nom, f.code as fournisseur_code,
           f.email as fournisseur_email, f.telephone as fournisseur_telephone
    FROM offres_fournisseurs of
    LEFT JOIN fournisseurs f ON of.fournisseur_id = f.id
    WHERE of.appel_offre_id = ?
    ORDER BY of.montant_ht ASC
");
$offres->execute([$id]);
$offres = $offres->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Détail appel d'offres - <?= htmlspecialchars($appel['numero']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .offre-selectionnee { background: #d4edda; }
        @media print {
            .no-print { display: none !important; }
            .sidebar { display: none !important; }
            main { width: 100% !important; margin: 0 !important; padding: 20px !important; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <h2>📋 Détail de l'appel d'offres</h2>
                    <div>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                        <?php if ($appel['statut'] == 'en_cours'): ?>
                            <a href="offres.php?id=<?= $appel['id'] ?>" class="btn btn-success">
                                <i class="bi bi-file-earmark-text"></i> Gérer les offres
                            </a>
                            <a href="cloturer.php?id=<?= $appel['id'] ?>" class="btn btn-warning" onclick="return confirm('Clôturer cet appel d\'offres ?')">
                                <i class="bi bi-check-circle"></i> Clôturer
                            </a>
                        <?php endif; ?>
                        <button onclick="window.print()" class="btn btn-danger">
                            <i class="bi bi-file-pdf"></i> Imprimer PDF
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <i class="bi bi-info-circle"></i> Informations
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>N°</th>
                                        <td><strong><?= htmlspecialchars($appel['numero']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Titre</th>
                                        <td><?= htmlspecialchars($appel['titre']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Statut</th>
                                        <td>
                                            <span class="badge bg-<?= $statut_couleur[$appel['statut']] ?? 'secondary' ?>">
                                                <?= str_replace('_', ' ', $appel['statut']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Demande associée</th>
                                        <td><?= htmlspecialchars($appel['demande_numero'] ?? 'Aucune') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date de publication</th>
                                        <td><?= date('d/m/Y', strtotime($appel['date_publication'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date limite de réponse</th>
                                        <td>
                                            <?= date('d/m/Y', strtotime($appel['date_limite_reponse'])) ?>
                                            <?php if ($appel['statut'] == 'en_cours' && strtotime($appel['date_limite_reponse']) < time()): ?>
                                                <span class="badge bg-danger">Dépassée</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Montant estimé HT</th>
                                        <td><?= number_format($appel['montant_estime_ht'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if ($appel['description']): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                <i class="bi bi-file-text"></i> Description
                            </div>
                            <div class="card-body">
                                <?= nl2br(htmlspecialchars($appel['description'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Offres reçues -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <i class="bi bi-list-check"></i> Offres reçues (<?= count($offres) ?>)
                    </div>
                    <div class="card-body">
                        <?php if (empty($offres)): ?>
                            <p class="text-muted">Aucune offre reçue pour le moment.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fournisseur</th>
                                            <th>Montant HT</th>
                                            <th>Délai (jours)</th>
                                            <th>Conditions paiement</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($offres as $o): ?>
                                        <tr class="<?= $o['est_selectionnee'] ? 'offre-selectionnee' : '' ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($o['fournisseur_code'] ?? '') ?></strong><br>
                                                <small><?= htmlspecialchars($o['fournisseur_nom'] ?? '') ?></small>
                                            </td>
                                            <td><strong><?= number_format($o['montant_ht'], 0, ',', ' ') ?> FCFA</strong></td>
                                            <td><?= $o['delai_livraison_jours'] ?></td>
                                            <td><?= htmlspecialchars($o['conditions_paiement'] ?? '—') ?></td>
                                            <td>
                                                <?php if ($o['est_selectionnee']): ?>
                                                    <span class="badge bg-success">✅ Sélectionnée</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">En attente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>