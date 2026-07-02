<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    $stmt = $pdo->prepare("
        UPDATE demandes_achat 
        SET statut = 'en_validation', date_soumission = NOW() 
        WHERE id = ? AND statut = 'brouillon'
    ");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit();
?>