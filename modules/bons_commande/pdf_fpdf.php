<?php
// pdf_fpdf.php - Export PDF avec FPDF
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

include '../../config/database.php';

// Inclure FPDF
require_once '../../vendor/fpdf/fpdf.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    die('ID manquant');
}

// Récupérer le BC
$stmt = $pdo->prepare("
    SELECT bc.*, f.nom as fournisseur_nom, f.adresse_ligne1, f.code_postal, f.ville, f.pays, f.telephone, f.email
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

// Créer le PDF
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'BON DE COMMANDE', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'SUNUPROJET - Gestion des Achats', 0, 1, 'C');
        $this->Ln(5);
        $this->Line(10, 30, 200, 30);
        $this->Ln(8);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | Généré le ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(15, 35, 15);

// Informations
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 7, 'N° :', 0, 0, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(60, 7, $bc['numero'], 0, 0, 'L');

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 7, 'Date :', 0, 0, 'R');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, date('d/m/Y', strtotime($bc['date_emission'])), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 7, 'Statut :', 0, 0, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(60, 7, strtoupper(str_replace('_', ' ', $bc['statut'])), 0, 0, 'L');

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 7, 'Livraison :', 0, 0, 'R');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, $bc['date_livraison_prevue'] ? date('d/m/Y', strtotime($bc['date_livraison_prevue'])) : 'Non précisée', 0, 1, 'L');

$pdf->Ln(5);

// Fournisseur
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(180, 8, 'FOURNISSEUR', 1, 1, 'C', 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(180, 6, 'Nom : ' . $bc['fournisseur_nom'], 'LR', 1, 'L');
if ($bc['adresse_ligne1']) {
    $pdf->Cell(180, 6, 'Adresse : ' . $bc['adresse_ligne1'] . ', ' . $bc['ville'], 'LR', 1, 'L');
}
if ($bc['telephone']) {
    $pdf->Cell(180, 6, 'Tél : ' . $bc['telephone'], 'LR', 1, 'L');
}
if ($bc['email']) {
    $pdf->Cell(180, 6, 'Email : ' . $bc['email'], 'LRB', 1, 'L');
}
$pdf->Ln(5);

// Lignes
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(60, 60, 60);
$pdf->SetTextColor(255);
$pdf->Cell(15, 8, '#', 1, 0, 'C', 1);
$pdf->Cell(65, 8, 'PRODUIT', 1, 0, 'C', 1);
$pdf->Cell(15, 8, 'Qté', 1, 0, 'C', 1);
$pdf->Cell(25, 8, 'PU HT', 1, 0, 'C', 1);
$pdf->Cell(15, 8, 'Remise', 1, 0, 'C', 1);
$pdf->Cell(18, 8, 'TVA %', 1, 0, 'C', 1);
$pdf->Cell(27, 8, 'Total HT', 1, 1, 'C', 1);

$pdf->SetTextColor(0);
$pdf->SetFillColor(245, 245, 245);
$pdf->SetFont('Arial', '', 9);

$total_ht = 0;
$total_tva = 0;
$i = 1;
foreach ($lignes as $l) {
    $fill = ($i % 2 == 0) ? 1 : 0;
    $pdf->Cell(15, 7, $i, 1, 0, 'C', $fill);
    $pdf->Cell(65, 7, ($l['produit_code'] ? $l['produit_code'] . ' - ' : '') . $l['produit_nom'], 1, 0, 'L', $fill);
    $pdf->Cell(15, 7, $l['quantite'] . ' ' . $l['unite_mesure'], 1, 0, 'C', $fill);
    $pdf->Cell(25, 7, number_format($l['prix_unitaire_ht'], 0, ',', ' '), 1, 0, 'R', $fill);
    $pdf->Cell(15, 7, $l['remise'] . '%', 1, 0, 'C', $fill);
    $pdf->Cell(18, 7, $l['taux_tva'] . '%', 1, 0, 'C', $fill);
    $pdf->Cell(27, 7, number_format($l['total_ht'], 0, ',', ' '), 1, 1, 'R', $fill);
    
    $total_ht += $l['total_ht'];
    $total_tva += $l['total_tva'];
    $i++;
}

// Totaux
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(153, 8, 'TOTAL HT', 1, 0, 'R', 1);
$pdf->Cell(27, 8, number_format($total_ht, 0, ',', ' ') . ' FCFA', 1, 1, 'R', 1);

$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(153, 8, 'TOTAL TVA', 1, 0, 'R', 1);
$pdf->Cell(27, 8, number_format($total_tva, 0, ',', ' ') . ' FCFA', 1, 1, 'R', 1);

$pdf->SetFillColor(60, 60, 60);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(153, 10, 'TOTAL TTC', 1, 0, 'R', 1);
$pdf->Cell(27, 10, number_format($bc['total_ttc'], 0, ',', ' ') . ' FCFA', 1, 1, 'R', 1);
$pdf->SetTextColor(0);

$pdf->Ln(5);

// Conditions
if ($bc['conditions_paiement'] || $bc['conditions_livraison'] || $bc['notes']) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(180, 8, 'CONDITIONS PARTICULIERES', 1, 1, 'C', 1);
    $pdf->SetFont('Arial', '', 9);
    
    if ($bc['conditions_paiement']) {
        $pdf->Cell(180, 5, 'Paiement : ' . $bc['conditions_paiement'], 'LR', 1, 'L');
    }
    if ($bc['conditions_livraison']) {
        $pdf->Cell(180, 5, 'Livraison : ' . $bc['conditions_livraison'], 'LR', 1, 'L');
    }
    if ($bc['notes']) {
        $pdf->MultiCell(180, 5, 'Notes : ' . $bc['notes'], 'LRB', 'L');
    }
}

// Signatures
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(90, 5, 'Cachet et signature du fournisseur', 0, 0, 'L');
$pdf->Cell(90, 5, 'Cachet et signature du client', 0, 1, 'R');

// Générer le PDF
$pdf->Output('BC_' . $bc['numero'] . '.pdf', 'I');
exit();
?>