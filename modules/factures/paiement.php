<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';
$erreur = '';
$succes = '';

if (!empty($id)) {
    $stmt = $pdo->prepare("SELECT * FROM factures WHERE id = ?");
    $stmt->execute([$id]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$facture || $facture['statut'] == 'effectue') {
        header('Location: index.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $date_paiement = $_POST['date_paiement'] ?? date('Y-m-d');
    $montant = $_POST['montant'] ?? 0;
    $methode = $_POST['methode'] ?? '';
    $reference = $_POST['reference'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($id) || $montant <= 0 || empty($methode)) {
        $erreur = 'Veuillez remplir tous les champs obligatoires';
    } else {
        try {
            // Générer un UUID pour le paiement
            $paiement_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff) | 0x4000,
                mt_rand(0, 0xffff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            $stmt = $pdo->prepare("
                INSERT INTO paiements (
                    id, facture_id, montant, date_paiement, methode, reference, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $paiement_id, $id, $montant, $date_paiement, $methode, $reference, $notes, $_SESSION['user_id']
            ]);
            
            // Mettre à jour le statut de la facture
            $stmt = $pdo->prepare("UPDATE factures SET statut = 'effectue', date_paiement = ? WHERE id = ?");
            $stmt->execute([$date_paiement, $id]);
            
            // Mettre à jour le statut du BC
            $stmt = $pdo->prepare("SELECT bon_commande_id FROM factures WHERE id = ?");
            $stmt->execute([$id]);
            $bon_id = $stmt->fetchColumn();
            
            if ($bon_id) {
                $stmt = $pdo->prepare("UPDATE bons_commande SET statut = 'paye' WHERE id = ?");
                $stmt->execute([$bon_id]);
            }
            
            $succes = 'Paiement enregistré avec succès !';
            
        } catch(PDOException $e) {
            $erreur = 'Erreur : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrer un paiement</title>
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

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>💰 Enregistrer un paiement</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($erreur): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                        <?php endif; ?>
                        <?php if ($succes): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                            <a href="index.php" class="btn btn-primary">Retour aux factures</a>
                        <?php endif; ?>

                        <?php if (!$succes && $facture): ?>
                            <div class="alert alert-info">
                                <strong>Facture :</strong> <?= htmlspecialchars($facture['numero']) ?><br>
                                <strong>Total TTC :</strong> <?= number_format($facture['total_ttc'], 0, ',', ' ') ?> FCFA
                            </div>

                            <form method="POST">
                                <input type="hidden" name="id" value="<?= $facture['id'] ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Date de paiement</label>
                                        <input type="date" name="date_paiement" class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Montant *</label>
                                        <input type="number" name="montant" class="form-control" step="1" value="<?= $facture['total_ttc'] ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Méthode de paiement *</label>
                                        <select name="methode" class="form-select" required>
                                            <option value="">Sélectionner</option>
                                            <option value="virement_bancaire">Virement bancaire</option>
                                            <option value="cheque">Chèque</option>
                                            <option value="especes">Espèces</option>
                                            <option value="wave">Wave</option>
                                            <option value="orange_money">Orange Money</option>
                                            <option value="free_money">Free Money</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Référence</label>
                                        <input type="text" name="reference" class="form-control" placeholder="N° de virement, chèque...">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success btn-lg">💾 Enregistrer le paiement</button>
                                        <a href="index.php" class="btn btn-secondary btn-lg">Annuler</a>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>