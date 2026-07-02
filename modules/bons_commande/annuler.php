<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    $stmt = $pdo->prepare("UPDATE bons_commande SET statut = 'annule' WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit();
?>