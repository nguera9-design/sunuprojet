<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    $stmt = $pdo->prepare("UPDATE factures SET statut = 'approuve' WHERE id = ? AND statut = 'en_attente'");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit();
?>