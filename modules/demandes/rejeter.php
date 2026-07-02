<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'validateur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';
$motif = $_POST['motif'] ?? 'Non conforme';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($id)) {
    $stmt = $pdo->prepare("
        UPDATE demandes_achat 
        SET statut = 'rejetee', motif_rejet = ? 
        WHERE id = ? AND statut = 'en_validation'
    ");
    $stmt->execute([$motif, $id]);
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejeter la demande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card" style="max-width: 500px; margin: auto;">
            <div class="card-header bg-danger text-white">
                <h4>Rejeter la demande</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Motif du rejet</label>
                        <textarea name="motif" class="form-control" rows="3" required>Non conforme aux besoins</textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                    <a href="index.php" class="btn btn-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>