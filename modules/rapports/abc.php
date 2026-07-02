<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

// Classement ABC des fournisseurs
$sql = "
    SELECT 
        f.id,
        f.code,
        f.nom,
        f.notation_qualite,
        f.notation_delai,
        f.notation_prix,
        f.notation_service,
        COALESCE(SUM(bc.total_ht), 0) as total_achats,
        COUNT(bc.id) as nb_commandes,
        AVG(
            (f.notation_qualite + f.notation_delai + f.notation_prix + f.notation_service) / 4
        ) as note_moyenne
    FROM fournisseurs f
    LEFT JOIN bons_commande bc ON f.id = bc.fournisseur_id AND bc.statut NOT IN ('annule', 'brouillon')
    WHERE f.actif = 1
    GROUP BY f.id
    ORDER BY total_achats DESC
";

$fournisseurs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Calcul du total général pour le classement ABC
$total_general = array_sum(array_column($fournisseurs, 'total_achats'));

// Attribution des classes ABC
$cumul = 0;
foreach ($fournisseurs as &$f) {
    $cumul += $f['total_achats'];
    $pourcentage = $total_general > 0 ? ($cumul / $total_general) * 100 : 0;
    
    if ($pourcentage <= 80) {
        $f['classe'] = 'A';
        $f['couleur'] = 'danger';
        $f['libelle'] = 'Top fournisseurs (80% des achats)';
    } elseif ($pourcentage <= 95) {
        $f['classe'] = 'B';
        $f['couleur'] = 'warning';
        $f['libelle'] = 'Fournisseurs importants (15% des achats)';
    } else {
        $f['classe'] = 'C';
        $f['couleur'] = 'success';
        $f['libelle'] = 'Fournisseurs secondaires (5% des achats)';
    }
}
unset($f);

// Statistiques par classe
$stats = [
    'A' => ['count' => 0, 'total' => 0, 'pourcentage' => 0],
    'B' => ['count' => 0, 'total' => 0, 'pourcentage' => 0],
    'C' => ['count' => 0, 'total' => 0, 'pourcentage' => 0]
];

foreach ($fournisseurs as $f) {
    $stats[$f['classe']]['count']++;
    $stats[$f['classe']]['total'] += $f['total_achats'];
}
foreach ($stats as $classe => &$s) {
    $s['pourcentage'] = $total_general > 0 ? round(($s['total'] / $total_general) * 100, 1) : 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement ABC des fournisseurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .classe-a { background: #f8d7da; }
        .classe-b { background: #fff3cd; }
        .classe-c { background: #d1e7dd; }
        .badge-a { background: #dc3545; color: white; padding: 5px 12px; border-radius: 20px; }
        .badge-b { background: #ffc107; color: black; padding: 5px 12px; border-radius: 20px; }
        .badge-c { background: #198754; color: white; padding: 5px 12px; border-radius: 20px; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
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
                    <h2>📊 Classement ABC des fournisseurs</h2>
                    <div>
                        <button onclick="window.print()" class="btn btn-danger">
                            <i class="bi bi-file-pdf"></i> Imprimer PDF
                        </button>
                        <a href="../../dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <!-- Légende -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card classe-a p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">Classe A</h3>
                                    <small><?= $stats['A']['count'] ?> fournisseur(s)</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge-a fs-5"><?= $stats['A']['pourcentage'] ?>%</span>
                                    <div><small><?= number_format($stats['A']['total'], 0, ',', ' ') ?> FCFA</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card classe-b p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">Classe B</h3>
                                    <small><?= $stats['B']['count'] ?> fournisseur(s)</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge-b fs-5"><?= $stats['B']['pourcentage'] ?>%</span>
                                    <div><small><?= number_format($stats['B']['total'], 0, ',', ' ') ?> FCFA</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card classe-c p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">Classe C</h3>
                                    <small><?= $stats['C']['count'] ?> fournisseur(s)</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge-c fs-5"><?= $stats['C']['pourcentage'] ?>%</span>
                                    <div><small><?= number_format($stats['C']['total'], 0, ',', ' ') ?> FCFA</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <i class="bi bi-list"></i> Liste des fournisseurs par classe
                    </div>
                    <div class="card-body">
                        <?php if (empty($fournisseurs)): ?>
                            <div class="alert alert-info">Aucun fournisseur enregistré.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Classe</th>
                                            <th>Code</th>
                                            <th>Nom</th>
                                            <th>Note moyenne</th>
                                            <th>Nb commandes</th>
                                            <th>Total achats</th>
                                            <th>% cumulé</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $cumul = 0;
                                        foreach ($fournisseurs as $f): 
                                            $cumul += $f['total_achats'];
                                            $pourcentage_cumul = $total_general > 0 ? round(($cumul / $total_general) * 100, 1) : 0;
                                        ?>
                                        <tr class="classe-<?= strtolower($f['classe']) ?>">
                                            <td>
                                                <span class="badge badge-<?= strtolower($f['classe']) ?> fs-6">
                                                    <?= $f['classe'] ?>
                                                </span>
                                            </td>
                                            <td><strong><?= htmlspecialchars($f['code']) ?></strong></td>
                                            <td><?= htmlspecialchars($f['nom']) ?></td>
                                            <td>
                                                <?php 
                                                $note = round($f['note_moyenne'], 1);
                                                echo str_repeat('⭐', round($note));
                                                if ($note == 0) echo '—';
                                                ?>
                                            </td>
                                            <td><?= $f['nb_commandes'] ?></td>
                                            <td><strong><?= number_format($f['total_achats'], 0, ',', ' ') ?> FCFA</strong></td>
                                            <td><?= $pourcentage_cumul ?>%</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Interprétation -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-lightbulb"></i> Interprétation
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="border rounded p-3 classe-a">
                                    <h5>🔴 Classe A</h5>
                                    <p class="small">Fournisseurs stratégiques qui représentent <strong>80%</strong> des achats. Priorité absolue pour la gestion de la relation.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 classe-b">
                                    <h5>🟡 Classe B</h5>
                                    <p class="small">Fournisseurs importants qui représentent <strong>15%</strong> des achats. Suivi régulier recommandé.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 classe-c">
                                    <h5>🟢 Classe C</h5>
                                    <p class="small">Fournisseurs secondaires qui représentent <strong>5%</strong> des achats. Gestion simplifiée.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>