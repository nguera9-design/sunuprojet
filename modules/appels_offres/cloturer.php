<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    $pdo->prepare("UPDATE appels_offres SET statut = 'clos' WHERE id = ?")->execute([$id]);
}

header('Location: index.php');
exit();
?>