<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $nom_commercial = $_POST['nom_commercial'] ?? '';
    $adresse_ligne1 = $_POST['adresse_ligne1'] ?? '';
    $adresse_ligne2 = $_POST['adresse_ligne2'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $pays = $_POST['pays'] ?? 'Sénégal';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';
    $site_web = $_POST['site_web'] ?? '';
    $contact_nom = $_POST['contact_nom'] ?? '';
    $contact_fonction = $_POST['contact_fonction'] ?? '';
    $contact_telephone = $_POST['contact_telephone'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';
    $conditions_paiement = $_POST['conditions_paiement'] ?? '';
    $delai_livraison_jours = intval($_POST['delai_livraison_jours'] ?? 0);
    $notes = $_POST['notes'] ?? '';
    
    // Notation sur 4 critères
    $notation_qualite = intval($_POST['notation_qualite'] ?? 0);
    $notation_delai = intval($_POST['notation_delai'] ?? 0);
    $notation_prix = intval($_POST['notation_prix'] ?? 0);
    $notation_service = intval($_POST['notation_service'] ?? 0);
    
    if (empty($code) || empty($nom)) {
        $erreur = 'Le code et le nom sont obligatoires';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO fournisseurs (
                    id, code, nom, nom_commercial, adresse_ligne1, adresse_ligne2,
                    code_postal, ville, pays, telephone, email, site_web,
                    contact_nom, contact_fonction, contact_telephone, contact_email,
                    conditions_paiement, delai_livraison_jours, notes,
                    notation_qualite, notation_delai, notation_prix, notation_service,
                    actif, created_by
                ) VALUES (
                    UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?
                )
            ");
            $stmt->execute([
                $code, $nom, $nom_commercial, $adresse_ligne1, $adresse_ligne2,
                $code_postal, $ville, $pays, $telephone, $email, $site_web,
                $contact_nom, $contact_fonction, $contact_telephone, $contact_email,
                $conditions_paiement, $delai_livraison_jours, $notes,
                $notation_qualite, $notation_delai, $notation_prix, $notation_service,
                $_SESSION['user_id']
            ]);
            
            $succes = 'Fournisseur ajouté avec succès !';
            
        } catch(PDOException $e) {
            $erreur = 'Erreur : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un fournisseur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>➕ Ajouter un fournisseur</h2>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                    <a href="index.php" class="btn btn-primary">Voir la liste des fournisseurs</a>
                <?php endif; ?>

                <?php if (!$succes): ?>
                <form method="POST" class="row g-3">
                    <!-- Informations principales -->
                    <div class="col-md-6">
                        <label class="form-label">Code *</label>
                        <input type="text" name="code" class="form-control" required placeholder="Ex: F001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom commercial</label>
                        <input type="text" name="nom_commercial" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    
                    <!-- Adresse -->
                    <div class="col-12">
                        <label class="form-label">Adresse ligne 1</label>
                        <input type="text" name="adresse_ligne1" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse ligne 2</label>
                        <input type="text" name="adresse_ligne2" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Code postal</label>
                        <input type="text" name="code_postal" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pays</label>
                        <input type="text" name="pays" class="form-control" value="Sénégal">
                    </div>
                    
                    <!-- Contact -->
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site web</label>
                        <input type="text" name="site_web" class="form-control">
                    </div>
                    
                    <!-- Personne de contact -->
                    <div class="col-12">
                        <h5 class="mt-3">👤 Personne de contact</h5>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nom</label>
                        <input type="text" name="contact_nom" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fonction</label>
                        <input type="text" name="contact_fonction" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="contact_telephone" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="contact_email" class="form-control">
                    </div>
                    
                    <!-- Conditions -->
                    <div class="col-12">
                        <h5 class="mt-3">📋 Conditions</h5>
                        <hr>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Conditions de paiement</label>
                        <input type="text" name="conditions_paiement" class="form-control" placeholder="Ex: 30 jours fin de mois">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Délai de livraison (jours)</label>
                        <input type="number" name="delai_livraison_jours" class="form-control" value="0">
                    </div>
                    
                    <!-- Notation -->
                    <div class="col-12">
                        <h5 class="mt-3">📊 Notation (sur 5)</h5>
                        <hr>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Qualité</label>
                        <select name="notation_qualite" class="form-select">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Délai</label>
                        <select name="notation_delai" class="form-select">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix</label>
                        <select name="notation_prix" class="form-select">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Service</label>
                        <select name="notation_service" class="form-select">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">💾 Enregistrer</button>
                        <a href="index.php" class="btn btn-secondary btn-lg">Annuler</a>
                    </div>
                </form>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>