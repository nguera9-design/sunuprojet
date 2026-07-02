<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'validateur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    $stmt = $pdo->prepare("
        UPDATE demandes_achat 
        SET statut = 'validee', date_validation = NOW(), validateur_id = ? 
        WHERE id = ? AND statut = 'en_validation'
    ");
    $stmt->execute([$_SESSION['user_id'], $id]);
}

header('Location: index.php');
exit();
?>