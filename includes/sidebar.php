<?php
$current_path = $_SERVER['REQUEST_URI'];
?>
<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar" style="min-height: 100vh; padding: 20px 0;">
    <ul class="nav flex-column">
        
        <!-- TABLEAU DE BORD -->
        <li class="nav-item">
            <a class="nav-link <?= (strpos($current_path, 'dashboard.php') !== false || $current_path == '/sunuprojet/') ? 'active' : '' ?>" href="/sunuprojet/dashboard.php">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>

        <!-- DEMANDES D'ACHAT -->
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/demandes') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/demandes/index.php">
                <i class="bi bi-file-earmark-plus"></i> Demandes d'achat
            </a>
        </li>

        <!-- FOURNISSEURS -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/fournisseurs') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/fournisseurs/index.php">
                <i class="bi bi-building"></i> Fournisseurs
            </a>
        </li>
        <?php endif; ?>

        <!-- PRODUITS -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/produits') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/produits/index.php">
                <i class="bi bi-box"></i> Produits
            </a>
        </li>
        <?php endif; ?>

        <!-- APPELS D'OFFRES -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/appels_offres') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/appels_offres/index.php">
                <i class="bi bi-megaphone"></i> Appels d'offres
            </a>
        </li>
        <?php endif; ?>

        <!-- BONS DE COMMANDE -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/bons_commande') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/bons_commande/index.php">
                <i class="bi bi-receipt"></i> Bons de commande
            </a>
        </li>
        <?php endif; ?>

        <!-- LIVRAISONS -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur' || $_SESSION['role'] == 'responsable_stock'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/livraisons') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/livraisons/index.php">
                <i class="bi bi-truck"></i> Livraisons
            </a>
        </li>
        <?php endif; ?>

        <!-- FACTURES -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/factures') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/factures/index.php">
                <i class="bi bi-credit-card"></i> Factures
            </a>
        </li>
        <?php endif; ?>

        <!-- STOCK -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur' || $_SESSION['role'] == 'responsable_stock'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/stock') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/stock/index.php">
                <i class="bi bi-clipboard-data"></i> Stock
            </a>
        </li>
        <?php endif; ?>

        <!-- RAPPORTS STATISTIQUES -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/rapports/statistiques.php') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/rapports/statistiques.php">
                <i class="bi bi-file-earmark-bar-graph"></i> Rapports statistiques
            </a>
        </li>
        <?php endif; ?>

        <!-- CLASSEMENT ABC -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'acheteur'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/rapports/abc.php') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/rapports/abc.php">
                <i class="bi bi-bar-chart-fill"></i> Classement ABC
            </a>
        </li>
        <?php endif; ?>

        <!-- UTILISATEURS -->
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_path, 'modules/utilisateurs') !== false ? 'active' : '' ?>" href="/sunuprojet/modules/utilisateurs/index.php">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
        </li>
        <?php endif; ?>

    </ul>
</div>