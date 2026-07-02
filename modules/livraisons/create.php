<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur', 'responsable_stock'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$erreur = '';
$succes = '';
$bon_id = $_GET['bon_id'] ?? '';

// Récupérer le bon de commande
$bc = null;
$lignes_bc = [];

if (!empty($bon_id)) {
    $stmt = $pdo->prepare("
        SELECT bc.*, f.nom as fournisseur_nom 
        FROM bons_commande bc
        LEFT JOIN fournisseurs f ON bc.fournisseur_id = f.id
        WHERE bc.id = ? AND bc.statut IN ('envoye', 'confirme', 'expedie')
    ");
    $stmt->execute([$bon_id]);
    $bc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($bc) {
        $stmt = $pdo->prepare("
            SELECT bcl.*, p.code as produit_code, p.nom as produit_nom, p.unite_mesure
            FROM bons_commande_lignes bcl
            LEFT JOIN produits p ON bcl.produit_id = p.id
            WHERE bcl.bon_commande_id = ? AND bcl.quantite_recue < bcl.quantite
        ");
        $stmt->execute([$bon_id]);
        $lignes_bc = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_livraison = $_POST['date_livraison'] ?? date('Y-m-d');
    $transporteur = $_POST['transporteur'] ?? '';
    $numero_suivi = $_POST['numero_suivi'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    $ligne_ids = $_POST['ligne_id'] ?? [];
    $quantites_recues = $_POST['quantite_recue'] ?? [];
    $qualites = $_POST['qualite_conforme'] ?? [];
    $quantites_manquantes = $_POST['quantite_manquante'] ?? [];
    $quantites_cassees = $_POST['quantite_cassee'] ?? [];
    $non_conformites = $_POST['non_conformite'] ?? [];
    
    if (empty($bon_id)) {
        $erreur = 'Aucun bon de commande sélectionné';
    } elseif (empty($ligne_ids) || empty($ligne_ids[0])) {
        $erreur = 'Aucune ligne à réceptionner';
    } else {
        try {
            $annee = date('Y');
            $stmt = $pdo->query("SELECT COUNT(*) FROM livraisons WHERE YEAR(created_at) = $annee");
            $count = $stmt->fetchColumn() + 1;
            $numero = 'LIV-' . $annee . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            $livraison_id = uniqid() . uniqid();
            
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO livraisons (
                    id, numero, bon_commande_id, date_livraison, date_prevue,
                    transporteur, numero_suivi, notes, statut, created_by
                ) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, 'recue', ?)
            ");
            $stmt->execute([
                $livraison_id, $numero, $bon_id, $date_livraison,
                $transporteur, $numero_suivi, $notes, $_SESSION['user_id']
            ]);
            
            $statut_bc = 'recu_total';
            $total_recu = 0;
            $total_quantite = 0;
            
            foreach ($ligne_ids as $index => $ligne_id) {
                if (empty($ligne_id)) continue;
                
                $quantite_recue = intval($quantites_recues[$index] ?? 0);
                $quantite_manquante = intval($quantites_manquantes[$index] ?? 0);
                $quantite_cassee = intval($quantites_cassees[$index] ?? 0);
                $qualite = isset($qualites[$index]) ? 1 : 0;
                $non_conformite = trim($non_conformites[$index] ?? '');
                
                if ($quantite_recue > 0) {
                    $stmt = $pdo->prepare("SELECT quantite, quantite_recue FROM bons_commande_lignes WHERE id = ?");
                    $stmt->execute([$ligne_id]);
                    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $nouvelle_quantite_recue = $ligne['quantite_recue'] + $quantite_recue;
                    
                    $stmt = $pdo->prepare("UPDATE bons_commande_lignes SET quantite_recue = ? WHERE id = ?");
                    $stmt->execute([$nouvelle_quantite_recue, $ligne_id]);
                    
                    $ligne_livraison_id = uniqid() . uniqid();
                    
                    $notes_ligne = "";
                    if ($quantite_manquante > 0) $notes_ligne .= "Manquant: $quantite_manquante ";
                    if ($quantite_cassee > 0) $notes_ligne .= "Cassé: $quantite_cassee ";
                    if ($non_conformite) $notes_ligne .= "Non-conformité: $non_conformite";
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO livraisons_lignes (
                            id, livraison_id, bon_commande_ligne_id, 
                            quantite_livree, qualite_conforme, notes
                        ) VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $ligne_livraison_id, $livraison_id, $ligne_id,
                        $quantite_recue, $qualite, $notes_ligne
                    ]);
                    
                    // Mettre à jour le stock
                    $stmt = $pdo->prepare("SELECT produit_id FROM bons_commande_lignes WHERE id = ?");
                    $stmt->execute([$ligne_id]);
                    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($produit && $produit['produit_id']) {
                        $quantite_conforme = $quantite_recue - $quantite_cassee;
                        if ($qualite == 0) {
                            $quantite_conforme = 0;
                        }
                        $stmt = $pdo->prepare("UPDATE produits SET stock_actuel = stock_actuel + ? WHERE id = ?");
                        $stmt->execute([$quantite_conforme, $produit['produit_id']]);
                    }
                    
                    $total_recu += $quantite_recue;
                    $total_quantite += $ligne['quantite'];
                }
            }
            
            if ($total_recu >= $total_quantite) {
                $statut_bc = 'recu_total';
            } elseif ($total_recu > 0) {
                $statut_bc = 'recu_partiel';
            }
            
            $stmt = $pdo->prepare("UPDATE bons_commande SET statut = ?, date_livraison_effective = ? WHERE id = ?");
            $stmt->execute([$statut_bc, $date_livraison, $bon_id]);
            
            $pdo->commit();
            $succes = 'Livraison enregistrée avec succès ! N° ' . $numero;
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            $erreur = 'Erreur : ' . $e->getMessage();
        }
    }
}

$bons_attente = $pdo->query("
    SELECT bc.id, bc.numero, f.nom as fournisseur_nom, bc.date_livraison_prevue
    FROM bons_commande bc
    LEFT JOIN fournisseurs f ON bc.fournisseur_id = f.id
    WHERE bc.statut IN ('envoye', 'confirme', 'expedie')
    ORDER BY bc.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réceptionner une livraison</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .ecart-row { border-left: 3px solid #ffc107; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2 class="mb-4">🚚 Réceptionner une livraison</h2>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                    <a href="../bons_commande/index.php" class="btn btn-primary">Voir les bons de commande</a>
                <?php endif; ?>

                <?php if (!$succes): ?>
                
                <?php if (empty($bon_id)): ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-list"></i> Sélectionner un bon de commande
                        </div>
                        <div class="card-body">
                            <?php if (empty($bons_attente)): ?>
                                <div class="alert alert-info">Aucun bon de commande en attente de réception.</div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($bons_attente as $b): ?>
                                    <a href="?bon_id=<?= $b['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($b['numero']) ?></strong>
                                            <span class="ms-3"><?= htmlspecialchars($b['fournisseur_nom']) ?></span>
                                        </div>
                                        <span class="badge bg-info">Livraison prévue le <?= date('d/m/Y', strtotime($b['date_livraison_prevue'])) ?></span>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($bc): ?>
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-receipt"></i> BC : <?= htmlspecialchars($bc['numero']) ?> - <?= htmlspecialchars($bc['fournisseur_nom']) ?>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">Date de livraison</label>
                                        <input type="date" name="date_livraison" class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Transporteur</label>
                                        <input type="text" name="transporteur" class="form-control" placeholder="Ex: Dakar Express">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Numéro de suivi</label>
                                        <input type="text" name="numero_suivi" class="form-control" placeholder="N° de suivi">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>

                                <h5>📦 Articles reçus</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Quantité commandée</th>
                                                <th>Déjà reçue</th>
                                                <th>Quantité reçue</th>
                                                <th>Conforme</th>
                                                <th class="text-warning">Manquant</th>
                                                <th class="text-danger">Cassé</th>
                                                <th>Non-conformité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lignes_bc as $l): ?>
                                            <tr class="ecart-row">
                                                <td><?= htmlspecialchars($l['produit_code'] ?? '') ?> - <?= htmlspecialchars($l['produit_nom'] ?? '') ?></td>
                                                <td><?= $l['quantite'] ?></td>
                                                <td><?= $l['quantite_recue'] ?></td>
                                                <td>
                                                    <input type="hidden" name="ligne_id[]" value="<?= $l['id'] ?>">
                                                    <input type="number" name="quantite_recue[]" class="form-control" value="<?= $l['quantite'] - $l['quantite_recue'] ?>" min="0" max="<?= $l['quantite'] - $l['quantite_recue'] ?>" required>
                                                </td>
                                                <td>
                                                    <input type="checkbox" name="qualite_conforme[]" value="1" checked>
                                                </td>
                                                <td>
                                                    <input type="number" name="quantite_manquante[]" class="form-control" value="0" min="0">
                                                </td>
                                                <td>
                                                    <input type="number" name="quantite_cassee[]" class="form-control" value="0" min="0">
                                                </td>
                                                <td>
                                                    <input type="text" name="non_conformite[]" class="form-control" placeholder="Décrire le problème">
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert alert-warning mt-3">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Attention :</strong> Les quantités "Manquant" et "Cassé" seront déduites du stock. Les articles non conformes ne seront pas pris en stock.
                                </div>

                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle"></i> Valider la réception
                                </button>
                                <a href="create.php" class="btn btn-secondary btn-lg">Changer de BC</a>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">Bon de commande non trouvé ou déjà réceptionné.</div>
                <?php endif; ?>
                
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>