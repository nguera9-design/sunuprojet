<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$erreur = '';
$succes = '';

// Récupérer les produits pour la liste déroulante
$produits = $pdo->query("SELECT id, code, nom, unite_mesure, prix_reference_ht FROM produits WHERE actif = 1 ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $date_souhaitee = $_POST['date_souhaitee'] ?? null;
    $type_approvisionnement = $_POST['type_approvisionnement'] ?? 'stock';
    $justification = $_POST['justification'] ?? '';
    
    // Lignes
    $produit_ids = $_POST['produit_id'] ?? [];
    $quantites = $_POST['quantite'] ?? [];
    $unites = $_POST['unite_mesure'] ?? [];
    $prix_estimes = $_POST['prix_unitaire_estime_ht'] ?? [];
    $descriptions_libres = $_POST['description_libre'] ?? [];
    
    if (empty($titre)) {
        $erreur = 'Le titre est obligatoire';
    } elseif (empty($produit_ids) || empty($produit_ids[0])) {
        $erreur = 'Ajoutez au moins une ligne';
    } else {
        try {
            // Générer le numéro
            $annee = date('Y');
            $stmt = $pdo->query("SELECT COUNT(*) FROM demandes_achat WHERE YEAR(created_at) = $annee");
            $count = $stmt->fetchColumn() + 1;
            $numero = 'DA-' . $annee . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            // Insérer la demande
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO demandes_achat (
                    id, numero, titre, description, demandeur_id, date_souhaitee, 
                    type_approvisionnement, justification, statut, created_by
                ) VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, 'brouillon', ?)
            ");
            $stmt->execute([$numero, $titre, $description, $_SESSION['user_id'], $date_souhaitee, $type_approvisionnement, $justification, $_SESSION['user_id']]);
            
            $demande_id = $pdo->lastInsertId();
            
            // Insérer les lignes
            foreach ($produit_ids as $index => $produit_id) {
                if (empty($produit_id) && empty($descriptions_libres[$index])) continue;
                
                $quantite = $quantites[$index] ?? 1;
                $unite = $unites[$index] ?? '';
                $prix = $prix_estimes[$index] ?? 0;
                $desc_libre = $descriptions_libres[$index] ?? '';
                $total = $quantite * $prix;
                
                $stmt = $pdo->prepare("
                    INSERT INTO demandes_achat_lignes (
                        demande_achat_id, produit_id, description_libre, quantite, 
                        unite_mesure, prix_unitaire_estime_ht, total_estime_ht
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$demande_id, $produit_id ?: null, $desc_libre, $quantite, $unite, $prix, $total]);
            }
            
            // Mettre à jour le budget estimé
            $stmt = $pdo->prepare("
                UPDATE demandes_achat 
                SET budget_estime_ht = (
                    SELECT COALESCE(SUM(total_estime_ht), 0) 
                    FROM demandes_achat_lignes 
                    WHERE demande_achat_id = ?
                )
                WHERE id = ?
            ");
            $stmt->execute([$demande_id, $demande_id]);
            
            $pdo->commit();
            $succes = 'Demande créée avec succès ! N° ' . $numero;
            
        } catch(PDOException $e) {
            $pdo->rollBack();
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
    <title>Nouvelle demande d'achat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar" style="min-height: 100vh; padding: 20px 0;">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="../../dashboard.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-file-earmark-plus"></i> Demandes d'achat</a></li>
                    <li class="nav-item"><a class="nav-link" href="../fournisseurs/index.php"><i class="bi bi-building"></i> Fournisseurs</a></li>
                    <li class="nav-item"><a class="nav-link" href="../produits/index.php"><i class="bi bi-box"></i> Produits</a></li>
                </ul>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>➕ Nouvelle demande d'achat</h2>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                <?php endif; ?>

                <form method="POST" id="demandeForm">
                    <!-- Informations générales -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-info-circle"></i> Informations générales
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Titre *</label>
                                    <input type="text" name="titre" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date souhaitée</label>
                                    <input type="date" name="date_souhaitee" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Type d'approvisionnement</label>
                                    <select name="type_approvisionnement" class="form-select">
                                        <option value="stock">Stock</option>
                                        <option value="juste_a_temps">Juste à temps</option>
                                        <option value="projet">Projet</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Justification</label>
                                    <textarea name="justification" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lignes de la demande -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white d-flex justify-content-between">
                            <span><i class="bi bi-list-check"></i> Lignes de la demande</span>
                            <button type="button" class="btn btn-sm btn-light" onclick="ajouterLigne()">
                                <i class="bi bi-plus-circle"></i> Ajouter une ligne
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="lignesContainer">
                                <!-- La ligne 0 sera générée par JavaScript -->
                            </div>
                            <div class="mt-3 text-end">
                                <strong>Total estimé : </strong>
                                <span id="totalGeneral" class="fs-5 text-primary">0 FCFA</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">💾 Enregistrer la demande</button>
                    <a href="index.php" class="btn btn-secondary btn-lg">Annuler</a>
                </form>
            </main>
        </div>
    </div>

    <script>
        let ligneCount = 0;
        const produits = <?= json_encode($produits) ?>;

        function ajouterLigne() {
            const container = document.getElementById('lignesContainer');
            const row = document.createElement('div');
            row.className = 'row g-3 ligne-item mb-3 p-3 border rounded';
            row.id = 'ligne-' + ligneCount;
            row.innerHTML = `
                <div class="col-md-3">
                    <label class="form-label">Produit</label>
                    <select name="produit_id[]" class="form-select" onchange="majInfos(this, ${ligneCount})">
                        <option value="">Sélectionner ou libre</option>
                        ${produits.map(p => `<option value="${p.id}" data-unite="${p.unite_mesure}" data-prix="${p.prix_reference_ht}">${p.code} - ${p.nom}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description libre</label>
                    <input type="text" name="description_libre[]" class="form-control" placeholder="Si produit non listé">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantité</label>
                    <input type="number" name="quantite[]" class="form-control quantite" value="1" min="1" onchange="calculerTotal()">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unité</label>
                    <input type="text" name="unite_mesure[]" class="form-control unite" placeholder="pièce, kg...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prix unitaire HT</label>
                    <input type="number" name="prix_unitaire_estime_ht[]" class="form-control prix" step="1" onchange="calculerTotal()">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total</label>
                    <input type="text" class="form-control total-ligne" readonly>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(${ligneCount})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            ligneCount++;
        }

        function majInfos(select, index) {
            const row = document.getElementById('ligne-' + index);
            const unite = row.querySelector('.unite');
            const prix = row.querySelector('.prix');
            const option = select.options[select.selectedIndex];
            if (option.value) {
                unite.value = option.dataset.unite || '';
                prix.value = option.dataset.prix || 0;
            } else {
                unite.value = '';
                prix.value = 0;
            }
            calculerTotal();
        }

        function calculerTotal() {
            const lignes = document.querySelectorAll('.ligne-item');
            let totalGeneral = 0;
            lignes.forEach(row => {
                const quantite = parseFloat(row.querySelector('.quantite').value) || 0;
                const prix = parseFloat(row.querySelector('.prix').value) || 0;
                const total = quantite * prix;
                row.querySelector('.total-ligne').value = total.toLocaleString() + ' FCFA';
                totalGeneral += total;
            });
            document.getElementById('totalGeneral').textContent = totalGeneral.toLocaleString() + ' FCFA';
        }

        function supprimerLigne(index) {
            const row = document.getElementById('ligne-' + index);
            if (document.querySelectorAll('.ligne-item').length > 1) {
                row.remove();
                calculerTotal();
            } else {
                alert('Vous devez avoir au moins une ligne');
            }
        }

        // Ajouter une première ligne au chargement
        window.onload = function() {
            ajouterLigne();
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>