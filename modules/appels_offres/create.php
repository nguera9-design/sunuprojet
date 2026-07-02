<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$erreur = '';
$succes = '';

// Récupérer les demandes validées
$demandes = $pdo->query("
    SELECT id, numero, titre, budget_estime_ht 
    FROM demandes_achat 
    WHERE statut = 'validee' 
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $demande_achat_id = $_POST['demande_achat_id'] ?? null;
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $date_limite_reponse = $_POST['date_limite_reponse'] ?? '';
    $montant_estime_ht = $_POST['montant_estime_ht'] ?? 0;

    if (empty($titre) || empty($date_limite_reponse)) {
        $erreur = 'Le titre et la date limite sont obligatoires';
    } else {
        try {
            $annee = date('Y');
            $stmt = $pdo->query("SELECT COUNT(*) FROM appels_offres WHERE YEAR(created_at) = $annee");
            $count = $stmt->fetchColumn() + 1;
            $numero = 'AO-' . $annee . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $stmt = $pdo->prepare("
                INSERT INTO appels_offres (
                    id, numero, demande_achat_id, titre, description,
                    date_publication, date_limite_reponse, montant_estime_ht,
                    statut, created_by
                ) VALUES (UUID(), ?, ?, ?, ?, CURDATE(), ?, ?, 'en_cours', ?)
            ");
            $stmt->execute([
                $numero, $demande_achat_id, $titre, $description,
                $date_limite_reponse, $montant_estime_ht, $_SESSION['user_id']
            ]);

            $succes = 'Appel d\'offres créé avec succès ! N° ' . $numero;

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
    <title>Nouvel appel d'offres</title>
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
                <h2>➕ Nouvel appel d'offres</h2>

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                    <a href="index.php" class="btn btn-primary">Voir les appels d'offres</a>
                <?php endif; ?>

                <?php if (!$succes): ?>
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Demande d'achat associée</label>
                        <select name="demande_achat_id" class="form-select">
                            <option value="">Aucune</option>
                            <?php foreach ($demandes as $d): ?>
                                <option value="<?= $d['id'] ?>">
                                    <?= htmlspecialchars($d['numero']) ?> - <?= htmlspecialchars($d['titre']) ?>
                                    (<?= number_format($d['budget_estime_ht'] ?? 0, 0, ',', ' ') ?> FCFA)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Titre *</label>
                        <input type="text" name="titre" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date limite de réponse *</label>
                        <input type="date" name="date_limite_reponse" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Montant estimé HT (FCFA)</label>
                        <input type="number" name="montant_estime_ht" class="form-control" step="1" value="0">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                        <a href="index.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>