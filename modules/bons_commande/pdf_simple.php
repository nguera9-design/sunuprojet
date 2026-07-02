<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    die('ID manquant');
}

// Récupérer le BC
$stmt = $pdo->prepare("
    SELECT bc.*, f.nom as fournisseur_nom, f.adresse_ligne1, f.ville, f.telephone, f.email
    FROM bons_commande bc
    LEFT JOIN fournisseurs f ON bc.fournisseur_id = f.id
    WHERE bc.id = ?
");
$stmt->execute([$id]);
$bc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bc) {
    die('BC non trouvé');
}

// Récupérer les lignes
$stmt = $pdo->prepare("
    SELECT bcl.*, p.code as produit_code, p.nom as produit_nom, p.unite_mesure
    FROM bons_commande_lignes bcl
    LEFT JOIN produits p ON bcl.produit_id = p.id
    WHERE bcl.bon_commande_id = ?
");
$stmt->execute([$id]);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer les totaux
$total_ht = 0;
$total_tva = 0;
$total_ttc = 0;
foreach ($lignes as $l) {
    $total_ht += $l['total_ht'];
    $total_tva += $l['total_tva'];
    $total_ttc += $l['total_ttc'];
}

// Envoyer l'en-tête PDF
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>BC <?= $bc['numero'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            color: #333; 
            padding: 20px;
            background: white;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; }
        }
        .header { text-align: center; margin-bottom: 20px; }
        h1 { font-size: 20px; text-transform: uppercase; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 5px 8px; border-bottom: 1px solid #ddd; }
        .label { font-weight: bold; width: 150px; }
        .fournisseur { 
            background: #f5f5f5; 
            padding: 10px; 
            margin-bottom: 15px; 
            border: 1px solid #ddd;
        }
        .fournisseur .titre { font-weight: bold; font-size: 13px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { 
            background: #2c3e50; 
            color: white; 
            padding: 6px; 
            text-align: center; 
            font-size: 10px;
        }
        td { padding: 5px; border: 1px solid #ddd; text-align: center; }
        .td-left { text-align: left; }
        .td-right { text-align: right; }
        .total { font-weight: bold; background: #f0f0f0; }
        .grand-total { font-weight: bold; background: #2c3e50; color: white; }
        .signatures { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature { width: 45%; padding-top: 30px; border-top: 1px solid #999; text-align: center; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #999; }
        .btn-print { 
            display: block; 
            margin: 20px auto; 
            padding: 12px 40px; 
            font-size: 16px; 
            background: #2c3e50; 
            color: white; 
            border: none; 
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-print:hover { background: #1a252f; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom:20px;">
        <button onclick="window.print()" class="btn-print">
            📄 Télécharger en PDF (Cliquez ici)
        </button>
        <br>
        <span style="font-size:12px; color:#999;">Puis choisissez "Enregistrer au format PDF" dans l'imprimante</span>
        <hr style="margin:20px 0;">
    </div>

    <div class="header">
        <h1>Bon de Commande</h1>
        <p>SUNUPROJET - Gestion des Achats</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">N° :</td>
            <td><strong><?= $bc['numero'] ?></strong></td>
            <td class="label">Date :</td>
            <td><?= date('d/m/Y', strtotime($bc['date_emission'])) ?></td>
        </tr>
        <tr>
            <td class="label">Statut :</td>
            <td><strong><?= strtoupper(str_replace('_', ' ', $bc['statut'])) ?></strong></td>
            <td class="label">Livraison :</td>
            <td><?= $bc['date_livraison_prevue'] ? date('d/m/Y', strtotime($bc['date_livraison_prevue'])) : 'Non précisée' ?></td>
        </tr>
    </table>

    <div class="fournisseur">
        <div class="titre">📦 FOURNISSEUR</div>
        <div><strong>Nom :</strong> <?= $bc['fournisseur_nom'] ?></div>
        <?php if ($bc['adresse_ligne1']): ?>
        <div><strong>Adresse :</strong> <?= $bc['adresse_ligne1'] . ', ' . $bc['ville'] ?></div>
        <?php endif; ?>
        <?php if ($bc['telephone']): ?>
        <div><strong>Tél :</strong> <?= $bc['telephone'] ?></div>
        <?php endif; ?>
        <?php if ($bc['email']): ?>
        <div><strong>Email :</strong> <?= $bc['email'] ?></div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="40%">Produit</th>
                <th width="10%">Qté</th>
                <th width="15%">PU HT</th>
                <th width="10%">Remise</th>
                <th width="10%">TVA</th>
                <th width="10%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($lignes as $l): ?>
            <tr>
                <td><?= $i ?></td>
                <td class="td-left"><?= ($l['produit_code'] ? $l['produit_code'] . ' - ' : '') . $l['produit_nom'] ?></td>
                <td><?= $l['quantite'] . ' ' . $l['unite_mesure'] ?></td>
                <td class="td-right"><?= number_format($l['prix_unitaire_ht'], 0, ',', ' ') ?> FCFA</td>
                <td><?= $l['remise'] ?>%</td>
                <td><?= $l['taux_tva'] ?>%</td>
                <td class="td-right"><?= number_format($l['total_ht'], 0, ',', ' ') ?></td>
            </tr>
            <?php $i++; endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="td-right total">TOTAL HT</td>
                <td class="td-right total"><?= number_format($total_ht, 0, ',', ' ') ?> FCFA</td>
            </tr>
            <tr>
                <td colspan="6" class="td-right total">TOTAL TVA (<?= round(($total_tva / max($total_ht, 1)) * 100, 1) ?>%)</td>
                <td class="td-right total"><?= number_format($total_tva, 0, ',', ' ') ?> FCFA</td>
            </tr>
            <tr>
                <td colspan="6" class="td-right grand-total">TOTAL TTC</td>
                <td class="td-right grand-total"><strong><?= number_format($total_ttc, 0, ',', ' ') ?> FCFA</strong></td>
            </tr>
        </tfoot>
    </table>

    <?php if ($bc['conditions_paiement'] || $bc['conditions_livraison'] || $bc['notes']): ?>
    <div style="background:#f9f9f9; padding:10px; border:1px solid #ddd; margin-bottom:15px;">
        <strong>CONDITIONS PARTICULIERES</strong><br>
        <?php if ($bc['conditions_paiement']): ?>
        <div><strong>Paiement :</strong> <?= $bc['conditions_paiement'] ?></div>
        <?php endif; ?>
        <?php if ($bc['conditions_livraison']): ?>
        <div><strong>Livraison :</strong> <?= $bc['conditions_livraison'] ?></div>
        <?php endif; ?>
        <?php if ($bc['notes']): ?>
        <div><strong>Notes :</strong> <?= nl2br($bc['notes']) ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="signatures">
        <div class="signature">
            <strong>Le fournisseur</strong><br>
            <span style="font-size:9px;color:#999;">Cachet et signature</span>
        </div>
        <div class="signature">
            <strong>Le client</strong><br>
            <span style="font-size:9px;color:#999;">Cachet et signature</span>
        </div>
    </div>

    <div class="footer">
        Document généré automatiquement le <?= date('d/m/Y H:i') ?> - SUNUPROJET
    </div>

    <div class="no-print" style="text-align:center; margin-top:20px;">
        <button onclick="window.print()" class="btn-print">
            📄 Télécharger en PDF
        </button>
        <br>
        <span style="font-size:12px; color:#999;">Puis choisissez "Enregistrer au format PDF" dans l'imprimante</span>
    </div>
</body>
</html>
<?php exit(); ?>