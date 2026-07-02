<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id) && $id != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit();
?>