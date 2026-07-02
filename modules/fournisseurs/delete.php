<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    $stmt = $pdo->prepare("UPDATE fournisseurs SET actif = 0 WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit();
?>