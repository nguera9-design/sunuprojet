<?php
// includes/auth_check.php
// À inclure en haut de chaque page protégée

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérification du rôle (à utiliser sur les pages spécifiques)
function verifier_role($role_requis) {
    if ($_SESSION['role'] !== $role_requis && $_SESSION['role'] !== 'admin') {
        header('Location: dashboard.php?erreur=Accès non autorisé');
        exit();
    }
}
?>