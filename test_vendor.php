<?php
echo "<h1>🔍 Vérification du dossier vendor</h1>";

// Vérifier si le dossier vendor existe
if (is_dir('vendor')) {
    echo "<p style='color:green'>✅ Le dossier 'vendor' existe</p>";
} else {
    echo "<p style='color:red'>❌ Le dossier 'vendor' n'existe pas</p>";
}

// Vérifier si le dossier vendor/fpdf existe
if (is_dir('vendor/fpdf')) {
    echo "<p style='color:green'>✅ Le dossier 'vendor/fpdf' existe</p>";
} else {
    echo "<p style='color:red'>❌ Le dossier 'vendor/fpdf' n'existe pas</p>";
}

// Vérifier si le fichier vendor/fpdf/fpdf.php existe
if (file_exists('vendor/fpdf/fpdf.php')) {
    echo "<p style='color:green'>✅ Le fichier 'vendor/fpdf/fpdf.php' existe</p>";
} else {
    echo "<p style='color:red'>❌ Le fichier 'vendor/fpdf/fpdf.php' n'existe pas</p>";
}

// Afficher le chemin complet
echo "<h2>📁 Chemin complet attendu :</h2>";
echo "<p><code>C:\\xampp\\htdocs\\sunuprojet\\vendor\\fpdf\\fpdf.php</code></p>";
?>