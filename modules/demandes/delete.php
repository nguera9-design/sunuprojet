<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    // Vérifier que la demande est en brouillon
    $stmt = $pdo->prepare("SELECT statut FROM demandes_achat WHERE id = ?");
    $stmt->execute([$id]);
    $statut = $stmt->fetchColumn();
    
    if ($statut == 'brouillon' || $statut == 'rejetee') {
        // Supprimer les lignes puis la demande
        $pdo->prepare("DELETE FROM demandes_achat_lignes WHERE demande_achat_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM demandes_achat WHERE id = ?")->execute([$id]);
    }
}

header('Location: index.php');
exit();
?>