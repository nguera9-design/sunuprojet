<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'acheteur'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$erreur = '';
$succes = '';
$demande_id = $_GET['demande_id'] ?? '';

// Récupérer les fournisseurs
$fournisseurs = $pdo->query("SELECT id, code, nom FROM fournisseurs WHERE actif = 1 ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Si une demande est sélectionnée, récupérer ses lignes
$demande = null;
$lignes_demande = [];
if (!empty($demande_id)) {
    $stmt = $pdo->prepare("SELECT * FROM demandes_achat WHERE id = ? AND statut = 'validee'");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($demande) {
        $stmt = $pdo->prepare("
            SELECT dal.*, p.code as produit_code, p.nom as produit_nom 
            FROM demandes_achat_lignes dal
            LEFT JOIN produits p ON dal.produit_id = p.id
            WHERE dal.demande_achat_id = ?
        ");
        $stmt->execute([$demande_id]);
        $lignes_demande = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fournisseur_id = $_POST['fournisseur_id'] ?? '';
    $date_livraison_prevue = $_POST['date_livraison_prevue'] ?? null;
    $conditions_paiement = $_POST['conditions_paiement'] ?? '';
    $conditions_livraison = $_POST['conditions_livraison'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Lignes
    $produit_ids = $_POST['produit_id'] ?? [];
    $quantites = $_POST['quantite'] ?? [];
    $prix_ht = $_POST['prix_unitaire_ht'] ?? [];
    $remises = $_POST['remise'] ?? [];
    $taux_tva = $_POST['taux_tva'] ?? [];
    
    if (empty($fournisseur_id)) {
        $erreur = 'Veuillez sélectionner un fournisseur';
    } elseif (empty($produit_ids) || empty($produit_ids[0])) {
        $erreur = 'Ajoutez au moins un produit';
    } else {
        try {
            // Générer un UUID pour le bon de commande
            $bon_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff) | 0x4000,
                mt_rand(0, 0xffff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            // Générer le numéro de BC
            $annee = date('Y');
            $stmt = $pdo->query("SELECT COUNT(*) FROM bons_commande WHERE YEAR(created_at) = $annee");
            $count = $stmt->fetchColumn() + 1;
            $numero = 'BC-' . $annee . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            $pdo->beginTransaction();
            
            // Insérer le bon de commande avec l'UUID généré
            $stmt = $pdo->prepare("
                INSERT INTO bons_commande (
                    id, numero, fournisseur_id, demande_achat_id, date_emission,
                    date_livraison_prevue, conditions_paiement, conditions_livraison,
                    notes, statut, created_by
                ) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, 'brouillon', ?)
            ");
            $stmt->execute([
                $bon_id, $numero, $fournisseur_id, $demande_id ?: null,
                $date_livraison_prevue, $conditions_paiement, $conditions_livraison,
                $notes, $_SESSION['user_id']
            ]);
            
            // Insérer les lignes
            $total_ht = 0;
            $total_tva = 0;
            $total_ttc = 0;
            
            foreach ($produit_ids as $index => $produit_id) {
                if (empty($produit_id)) continue;
                
                $quantite = intval($quantites[$index] ?? 1);
                $prix = floatval($prix_ht[$index] ?? 0);
                $remise = floatval($remises[$index] ?? 0);
                $tva = floatval($taux_tva[$index] ?? 18);
                
                $prix_remise = $prix * (1 - $remise / 100);
                $total_ligne_ht = $quantite * $prix_remise;
                $total_ligne_tva = $total_ligne_ht * $tva / 100;
                $total_ligne_ttc = $total_ligne_ht + $total_ligne_tva;
                
                $total_ht += $total_ligne_ht;
                $total_tva += $total_ligne_tva;
                $total_ttc += $total_ligne_ttc;
                
                // Générer un UUID pour la ligne
                $ligne_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff) | 0x4000,
                    mt_rand(0, 0xffff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
                
                $stmt = $pdo->prepare("
                    INSERT INTO bons_commande_lignes (
                        id, bon_commande_id, produit_id, quantite, 
                        prix_unitaire_ht, remise, taux_tva,
                        total_ht, total_tva, total_ttc
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $ligne_id, $bon_id, $produit_id, $quantite,
                    $prix, $remise, $tva,
                    $total_ligne_ht, $total_ligne_tva, $total_ligne_ttc
                ]);
            }
            
            // Mettre à jour les totaux du BC
            $stmt = $pdo->prepare("
                UPDATE bons_commande 
                SET total_ht = ?, total_tva = ?, total_ttc = ?
                WHERE id = ?
            ");
            $stmt->execute([$total_ht, $total_tva, $total_ttc, $bon_id]);
            
            // Mettre à jour le statut de la demande
            if (!empty($demande_id)) {
                $stmt = $pdo->prepare("UPDATE demandes_achat SET statut = 'transformee_en_bc' WHERE id = ?");
                $stmt->execute([$demande_id]);
            }
            
            $pdo->commit();
            $succes = 'Bon de commande créé avec succès ! N° ' . $numero;
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            $erreur = 'Erreur : ' . $e->getMessage();
        }
    }
}

// Récupérer les produits
$produits = $pdo->query("SELECT id, code, nom, unite_mesure, prix_reference_ht, taux_tva FROM produits WHERE actif = 1 ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau bon de commande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #f8f9fa; padding: 20px 0; }
        .sidebar .nav-link { color: #2c3e50; padding: 10px 20px; }
        .sidebar .nav-link:hover { background: #e9ecef; border-radius: 5px; }
        .sidebar .nav-link.active { background: #667eea; color: white; border-radius: 5px; }
        .ligne-item { background: #f8f9fa; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php">🏢 Gestion Achats</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link text-danger" href="../../logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="../../dashboard.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="../demandes/index.php"><i class="bi bi-file-earmark-plus"></i> Demandes d'achat</a></li>
                    <li class="nav-item"><a class="nav-link" href="../fournisseurs/index.php"><i class="bi bi-building"></i> Fournisseurs</a></li>
                    <li class="nav-item"><a class="nav-link" href="../produits/index.php"><i class="bi bi-box"></i> Produits</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-receipt"></i> Bons de commande</a></li>
                </ul>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>➕ Nouveau bon de commande</h2>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                    <a href="index.php" class="btn btn-primary">Voir la liste des BC</a>
                <?php endif; ?>

                <?php if (!$succes): ?>
                <form method="POST" id="bcForm">
                    <!-- Informations générales -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-info-circle"></i> Informations générales
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Fournisseur *</label>
                                    <select name="fournisseur_id" class="form-select" required>
                                        <option value="">Sélectionner un fournisseur</option>
                                        <?php foreach ($fournisseurs as $f): ?>
                                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['code']) ?> - <?= htmlspecialchars($f['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($demande): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Demande d'achat</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($demande['numero']) ?> - <?= htmlspecialchars($demande['titre']) ?>" readonly>
                                    <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                                </div>
                                <?php endif; ?>
                                <div class="col-md-4">
                                    <label class="form-label">Date livraison prévue</label>
                                    <input type="date" name="date_livraison_prevue" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Conditions de paiement</label>
                                    <input type="text" name="conditions_paiement" class="form-control" placeholder="Ex: 30 jours fin de mois">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Conditions de livraison</label>
                                    <input type="text" name="conditions_livraison" class="form-control" placeholder="Ex: FOB Dakar">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lignes du BC -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white d-flex justify-content-between">
                            <span><i class="bi bi-list-check"></i> Lignes du bon de commande</span>
                            <button type="button" class="btn btn-sm btn-light" onclick="ajouterLigne()">
                                <i class="bi bi-plus-circle"></i> Ajouter un produit
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="lignesContainer"></div>
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-md-4 offset-md-8">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Total HT</th>
                                                <td id="totalHt" class="text-end">0 FCFA</td>
                                            </tr>
                                            <tr>
                                                <th>Total TVA</th>
                                                <td id="totalTva" class="text-end">0 FCFA</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-success text-white">Total TTC</th>
                                                <td id="totalTtc" class="text-end fw-bold fs-5">0 FCFA</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">💾 Enregistrer le BC</button>
                    <a href="index.php" class="btn btn-secondary btn-lg">Annuler</a>
                </form>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        let ligneCount = 0;
        const produits = <?= json_encode($produits) ?>;
        const lignesDemande = <?= json_encode($lignes_demande) ?>;

        function ajouterLigne(produit_id, quantite, prix, tva) {
            const container = document.getElementById('lignesContainer');
            const row = document.createElement('div');
            row.className = 'row g-2 ligne-item';
            row.id = 'ligne-' + ligneCount;
            row.innerHTML = `
                <div class="col-md-3">
                    <select name="produit_id[]" class="form-select" onchange="majPrix(this, ${ligneCount})">
                        <option value="">Sélectionner</option>
                        ${produits.map(p => `<option value="${p.id}" data-prix="${p.prix_reference_ht}" data-tva="${p.taux_tva}" ${p.id == produit_id ? 'selected' : ''}>${p.code} - ${p.nom}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="quantite[]" class="form-control quantite" value="${quantite || 1}" min="1" onchange="calculerTotaux()">
                </div>
                <div class="col-md-2">
                    <input type="number" name="prix_unitaire_ht[]" class="form-control prix" step="1" value="${prix || 0}" onchange="calculerTotaux()">
                </div>
                <div class="col-md-1">
                    <input type="number" name="remise[]" class="form-control remise" value="0" step="0.1" onchange="calculerTotaux()">
                </div>
                <div class="col-md-1">
                    <input type="number" name="taux_tva[]" class="form-control tva" value="${tva || 18}" step="0.1" onchange="calculerTotaux()">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control total-ligne" readonly>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(${ligneCount})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            
            if (produit_id) {
                const select = row.querySelector('select');
                const option = select.querySelector(`option[value="${produit_id}"]`);
                if (option) {
                    select.value = produit_id;
                    majPrix(select, ligneCount);
                }
            }
            
            ligneCount++;
            calculerTotaux();
        }

        function majPrix(select, index) {
            const row = document.getElementById('ligne-' + index);
            const prixInput = row.querySelector('.prix');
            const tvaInput = row.querySelector('.tva');
            const option = select.options[select.selectedIndex];
            if (option.value) {
                prixInput.value = option.dataset.prix || 0;
                tvaInput.value = option.dataset.tva || 18;
            } else {
                prixInput.value = 0;
                tvaInput.value = 18;
            }
            calculerTotaux();
        }

        function calculerTotaux() {
            const lignes = document.querySelectorAll('.ligne-item');
            let totalHt = 0, totalTva = 0, totalTtc = 0;
            
            lignes.forEach(row => {
                const quantite = parseFloat(row.querySelector('.quantite').value) || 0;
                const prix = parseFloat(row.querySelector('.prix').value) || 0;
                const remise = parseFloat(row.querySelector('.remise').value) || 0;
                const tva = parseFloat(row.querySelector('.tva').value) || 0;
                
                const prixRemise = prix * (1 - remise / 100);
                const totalLigneHt = quantite * prixRemise;
                const totalLigneTva = totalLigneHt * tva / 100;
                const totalLigneTtc = totalLigneHt + totalLigneTva;
                
                row.querySelector('.total-ligne').value = totalLigneTtc.toLocaleString() + ' FCFA';
                
                totalHt += totalLigneHt;
                totalTva += totalLigneTva;
                totalTtc += totalLigneTtc;
            });
            
            document.getElementById('totalHt').textContent = totalHt.toLocaleString() + ' FCFA';
            document.getElementById('totalTva').textContent = totalTva.toLocaleString() + ' FCFA';
            document.getElementById('totalTtc').textContent = totalTtc.toLocaleString() + ' FCFA';
        }

        function supprimerLigne(index) {
            const row = document.getElementById('ligne-' + index);
            if (document.querySelectorAll('.ligne-item').length > 1) {
                row.remove();
                calculerTotaux();
            } else {
                alert('Vous devez avoir au moins un produit');
            }
        }

        // Chargement initial
        window.onload = function() {
            if (lignesDemande.length > 0) {
                lignesDemande.forEach(l => {
                    ajouterLigne(l.produit_id, l.quantite, l.prix_unitaire_estime_ht || 0, 18);
                });
            } else {
                ajouterLigne(null, 1, 0, 18);
            }
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>