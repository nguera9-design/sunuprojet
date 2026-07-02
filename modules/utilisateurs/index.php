<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Seul l'admin peut accéder à ce module
if ($_SESSION['role'] != 'admin') {
    header('Location: ../../dashboard.php');
    exit();
}

include '../../config/database.php';

// Récupérer tous les utilisateurs
$utilisateurs = $pdo->query("
    SELECT id, nom, prenom, email, role, service, telephone, actif, created_at
    FROM utilisateurs 
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>👥 Gestion des utilisateurs</h2>
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouvel utilisateur
                    </a>
                </div>

                <?php if (empty($utilisateurs)): ?>
                    <div class="alert alert-info">Aucun utilisateur enregistré.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Service</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($utilisateurs as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['nom']) ?></td>
                                    <td><?= htmlspecialchars($u['prenom']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $u['role'] == 'admin' ? 'danger' : 
                                            ($u['role'] == 'acheteur' ? 'primary' : 
                                            ($u['role'] == 'validateur' ? 'success' : 
                                            ($u['role'] == 'responsable_stock' ? 'info' : 'secondary'))) 
                                        ?>">
                                            <?= str_replace('_', ' ', $u['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($u['service'] ?? '—') ?></td>
                                    <td>
                                        <?php if ($u['actif'] == 1): ?>
                                            <span class="badge bg-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($_SESSION['user_id'] != $u['id']): ?>
                                            <a href="toggle.php?id=<?= $u['id'] ?>" class="btn btn-sm <?= $u['actif'] == 1 ? 'btn-danger' : 'btn-success' ?>" onclick="return confirm('Changer le statut de cet utilisateur ?')">
                                                <i class="bi <?= $u['actif'] == 1 ? 'bi-person-x' : 'bi-person-check' ?>"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer définitivement cet utilisateur ?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>