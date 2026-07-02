<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    $stmt = $pdo->prepare("
        UPDATE bons_commande 
        SET statut = 'envoye', date_envoi = NOW(), envoye_par = ? 
        WHERE id = ? AND statut = 'brouillon'
    ");
    $stmt->execute([$_SESSION['user_id'], $id]);
}

header('Location: index.php');
exit();
?>