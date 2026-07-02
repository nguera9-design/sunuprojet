<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manuel Utilisateur - SUNUPROJET</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
            background: white;
            max-width: 900px;
            margin: auto;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; }
        }
        h1 { font-size: 24px; color: #2c3e50; text-align: center; margin: 30px 0 10px 0; }
        h2 { font-size: 18px; color: #2c3e50; margin: 30px 0 15px 0; border-bottom: 2px solid #2c3e50; padding-bottom: 5px; }
        h3 { font-size: 15px; color: #34495e; margin: 20px 0 10px 0; }
        h4 { font-size: 13px; color: #555; margin: 15px 0 8px 0; }
        p { margin: 8px 0; line-height: 1.6; }
        ul, ol { margin: 10px 0 10px 25px; }
        li { margin: 5px 0; line-height: 1.5; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0;
            font-size: 11px;
        }
        th { 
            background: #2c3e50; 
            color: white; 
            padding: 8px 10px; 
            text-align: left;
            border: 1px solid #2c3e50;
        }
        td { 
            padding: 6px 10px; 
            border: 1px solid #ddd;
        }
        tr:nth-child(even) { background: #f8f9fa; }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }
        .header h1 { margin: 0; font-size: 28px; }
        .header .subtitle { font-size: 14px; color: #666; margin-top: 5px; }
        .toc { 
            background: #f8f9fa; 
            padding: 15px 25px; 
            margin: 20px 0;
            border-radius: 8px;
        }
        .toc ul { list-style: none; padding: 0; }
        .toc li { padding: 4px 0; border-bottom: 1px dashed #ddd; }
        .toc a { color: #2c3e50; text-decoration: none; }
        .toc a:hover { text-decoration: underline; }
        .badge { 
            background: #2c3e50; 
            color: white; 
            padding: 2px 10px; 
            border-radius: 12px;
            font-size: 10px;
        }
        .btn-print { 
            display: block;
            margin: 20px auto;
            padding: 14px 50px;
            font-size: 16px;
            font-weight: bold;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-print:hover { background: #1a252f; }
        .footer { 
            text-align: center; 
            margin-top: 40px; 
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #999;
        }
        .page-break { page-break-after: always; }
        .code-block {
            background: #f4f4f4;
            padding: 10px 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 11px;
            margin: 10px 0;
            border-left: 3px solid #2c3e50;
        }
        .capture-placeholder {
            background: #f0f0f0;
            border: 2px dashed #ccc;
            padding: 30px 20px;
            text-align: center;
            margin: 10px 0;
            border-radius: 8px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align:center; margin-bottom:20px;">
    <button onclick="window.print()" class="btn-print">
        📄 TÉLÉCHARGER LE PDF
    </button>
    <br>
    <span style="font-size:13px; color:#999;">
        Cliquez sur le bouton, puis choisissez <strong>"Enregistrer au format PDF"</strong>
    </span>
    <hr style="margin:20px 0;">
</div>

<!-- ============================================================ -->
<!-- PAGE DE GARDE -->
<!-- ============================================================ -->
<div style="text-align:center; padding:60px 0; min-height:80vh; display:flex; flex-direction:column; justify-content:center;">
    <h1 style="font-size:42px; color:#2c3e50;">SUNUPROJET</h1>
    <h2 style="font-size:24px; color:#34495e; border: none;">Gestion des Achats</h2>
    <h2 style="font-size:20px; color:#555; border: none; margin-top:5px;">Manuel Utilisateur</h2>
    <div style="margin:40px 0; font-size:16px; color:#666;">
        <p>Version 1.0</p>
        <p>Juin 2026</p>
    </div>
    <div style="margin-top:60px; font-size:14px; color:#888; border-top:2px solid #eee; padding-top:20px; width:60%; margin-left:auto; margin-right:auto;">
        <p><strong>Étudiant :</strong> [Votre Nom]</p>
        <p><strong>Promotion :</strong> DESCAF 1</p>
        <p><strong>Année universitaire :</strong> 2025 - 2026</p>
    </div>
</div>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- TABLE DES MATIÈRES -->
<!-- ============================================================ -->
<h2>📋 Table des matières</h2>
<div class="toc">
    <ul>
        <li><a href="#introduction">1. Introduction</a></li>
        <li><a href="#installation">2. Installation et configuration</a></li>
        <li><a href="#connexion">3. Connexion à l'application</a></li>
        <li><a href="#dashboard">4. Tableau de bord</a></li>
        <li><a href="#fournisseurs">5. Gestion des fournisseurs</a></li>
        <li><a href="#produits">6. Gestion des produits</a></li>
        <li><a href="#demandes">7. Demandes d'achat</a></li>
        <li><a href="#bc">8. Bons de commande</a></li>
        <li><a href="#livraisons">9. Livraisons</a></li>
        <li><a href="#factures">10. Factures et paiements</a></li>
        <li><a href="#stock">11. Gestion de stock</a></li>
        <li><a href="#pdf">12. Export PDF</a></li>
        <li><a href="#deconnexion">13. Déconnexion</a></li>
        <li><a href="#depannage">14. Dépannage</a></li>
        <li><a href="#annexes">Annexes</a></li>
    </ul>
</div>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 1. INTRODUCTION -->
<!-- ============================================================ -->
<h2 id="introduction">1. Introduction</h2>

<h3>1.1 Présentation du projet</h3>
<p>
    <strong>SUNUPROJET</strong> est une plateforme web de gestion des achats et approvisionnements 
    destinée aux PME sénégalaises. Elle permet de centraliser et d'automatiser l'ensemble du 
    processus achats, de la demande à la facturation.
</p>

<h3>1.2 Objectifs</h3>
<ul>
    <li>Centraliser le processus achats</li>
    <li>Automatiser le workflow de validation</li>
    <li>Suivre les commandes et livraisons</li>
    <li>Gérer le stock en temps réel</li>
    <li>Produire des documents professionnels (PDF)</li>
</ul>

<h3>1.3 Public cible</h3>
<ul>
    <li>Responsables achats</li>
    <li>Demandeurs</li>
    <li>Validateurs</li>
    <li>Acheteurs</li>
    <li>Responsables de stock</li>
</ul>

<h3>1.4 Technologies utilisées</h3>
<ul>
    <li>PHP 8</li>
    <li>MySQL</li>
    <li>HTML5 / CSS3 / JavaScript</li>
    <li>Bootstrap 5</li>
    <li>FPDF (pour les PDF)</li>
</ul>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 2. INSTALLATION -->
<!-- ============================================================ -->
<h2 id="installation">2. Installation et configuration</h2>

<h3>2.1 Prérequis</h3>
<p>Pour installer et exécuter l'application, vous devez disposer de :</p>
<ul>
    <li>XAMPP (Apache, MySQL, PHP 8+)</li>
    <li>Un navigateur web (Chrome, Firefox, Edge)</li>
    <li>Au moins 100 Mo d'espace disque</li>
</ul>

<h3>2.2 Installation</h3>

<h4>Étape 1 : Copier le projet</h4>
<p>Copiez le dossier <code>sunuprojet</code> dans le répertoire :</p>
<div class="code-block">C:\xampp\htdocs\</div>

<h4>Étape 2 : Démarrer XAMPP</h4>
<p>Lancez XAMPP et démarrez les services :</p>
<ul>
    <li>Apache (bouton Start)</li>
    <li>MySQL (bouton Start)</li>
</ul>
<div class="capture-placeholder">📸 [Capture d'écran : XAMPP Control Panel]</div>

<h4>Étape 3 : Créer la base de données</h4>
<ol>
    <li>Ouvrez votre navigateur</li>
    <li>Allez sur : <code>http://localhost/phpmyadmin</code></li>
    <li>Cliquez sur <strong>"Nouvelle base de données"</strong></li>
    <li>Nommez-la : <code>sunuprojet</code></li>
    <li>Choisissez : <code>utf8_general_ci</code></li>
    <li>Cliquez sur <strong>"Créer"</strong></li>
</ol>
<div class="capture-placeholder">📸 [Capture d'écran : Création base phpMyAdmin]</div>

<h4>Étape 4 : Importer les données</h4>
<ol>
    <li>Dans phpMyAdmin, cliquez sur la base <code>sunuprojet</code></li>
    <li>Cliquez sur l'onglet <strong>"Importer"</strong></li>
    <li>Choisissez le fichier <code>sunuprojet.sql</code></li>
    <li>Cliquez sur <strong>"Exécuter"</strong></li>
</ol>

<h4>Étape 5 : Accéder à l'application</h4>
<p>Dans votre navigateur :</p>
<div class="code-block">http://localhost/sunuprojet/</div>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 3. CONNEXION -->
<!-- ============================================================ -->
<h2 id="connexion">3. Connexion à l'application</h2>

<h3>3.1 Page de connexion</h3>
<p>Pour accéder à l'application, ouvrez votre navigateur et tapez :</p>
<div class="code-block">http://localhost/sunuprojet/login.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Page de connexion]</div>

<h3>3.2 Identifiants de démonstration</h3>
<table>
    <thead>
        <tr><th>Rôle</th><th>Email</th><th>Mot de passe</th></tr>
    </thead>
    <tbody>
        <tr><td>Administrateur</td><td>admin@sunuprojet.sn</td><td>password123</td></tr>
        <tr><td>Demandeur</td><td>demandeur@sunuprojet.sn</td><td>password123</td></tr>
        <tr><td>Validateur</td><td>validateur@sunuprojet.sn</td><td>password123</td></tr>
        <tr><td>Acheteur</td><td>acheteur@sunuprojet.sn</td><td>password123</td></tr>
        <tr><td>Responsable stock</td><td>stock@sunuprojet.sn</td><td>password123</td></tr>
    </tbody>
</table>

<h3>3.3 Procédure de connexion</h3>
<ol>
    <li>Saisissez votre adresse email</li>
    <li>Saisissez votre mot de passe</li>
    <li>Cliquez sur le bouton <strong>"Se connecter"</strong></li>
</ol>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 4. TABLEAU DE BORD -->
<!-- ============================================================ -->
<h2 id="dashboard">4. Tableau de bord</h2>

<h3>4.1 Présentation</h3>
<p>Après la connexion, vous arrivez sur le tableau de bord.</p>
<div class="code-block">http://localhost/sunuprojet/dashboard.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Tableau de bord]</div>

<h3>4.2 Statistiques</h3>
<p>Le tableau de bord affiche 4 indicateurs clés :</p>
<table>
    <thead>
        <tr><th>Indicateur</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td>Demandes</td><td>Nombre total de demandes d'achat</td></tr>
        <tr><td>Commandes</td><td>Nombre total de bons de commande</td></tr>
        <tr><td>Fournisseurs</td><td>Nombre de fournisseurs actifs</td></tr>
        <tr><td>Alertes stock</td><td>Produits en dessous du seuil d'alerte</td></tr>
    </tbody>
</table>

<h3>4.3 Menu de navigation</h3>
<p>Le menu de gauche permet d'accéder à tous les modules :</p>
<ul>
    <li>🏠 Tableau de bord</li>
    <li>📄 Demandes d'achat</li>
    <li>🏢 Fournisseurs</li>
    <li>📦 Produits</li>
    <li>📄 Bons de commande</li>
    <li>🚚 Livraisons</li>
    <li>📊 Stock</li>
</ul>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 5. FOURNISSEURS -->
<!-- ============================================================ -->
<h2 id="fournisseurs">5. Gestion des fournisseurs</h2>

<h3>5.1 Liste des fournisseurs</h3>
<div class="code-block">http://localhost/sunuprojet/modules/fournisseurs/index.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Liste des fournisseurs]</div>

<p><strong>Fonctionnalités :</strong></p>
<ul>
    <li>Affichage de tous les fournisseurs actifs</li>
    <li>Notation par étoiles (moyenne des 4 critères)</li>
    <li>Actions : Voir (👁️), Modifier (✏️), Supprimer (🗑️)</li>
</ul>

<h3>5.2 Ajouter un fournisseur</h3>
<div class="code-block">http://localhost/sunuprojet/modules/fournisseurs/create.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Ajout fournisseur]</div>

<p><strong>Champs obligatoires :</strong></p>
<ul>
    <li>Code (ex: F001)</li>
    <li>Nom</li>
</ul>

<p><strong>Champs optionnels :</strong></p>
<ul>
    <li>Adresse</li>
    <li>Code postal / Ville / Pays</li>
    <li>Téléphone / Email</li>
    <li>Notation (qualité, délai, prix, service)</li>
</ul>

<h3>5.3 Modifier un fournisseur</h3>
<ol>
    <li>Cliquez sur l'icône ✏️ dans la liste</li>
    <li>Modifiez les informations</li>
    <li>Cliquez sur <strong>"Enregistrer"</strong></li>
</ol>

<h3>5.4 Supprimer un fournisseur</h3>
<ol>
    <li>Cliquez sur l'icône 🗑️ dans la liste</li>
    <li>Confirmez la suppression</li>
</ol>
<p><em>Note : La suppression est logique (actif = 0), les données historiques sont conservées.</em></p>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 6. PRODUITS -->
<!-- ============================================================ -->
<h2 id="produits">6. Gestion des produits</h2>

<h3>6.1 Liste des produits</h3>
<div class="code-block">http://localhost/sunuprojet/modules/produits/index.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Liste des produits]</div>

<p><strong>Fonctionnalités :</strong></p>
<ul>
    <li>Affichage du stock actuel</li>
    <li>Alerte visuelle si stock ≤ seuil</li>
    <li>Prix de référence</li>
    <li>Actions : Modifier (✏️), Supprimer (🗑️)</li>
</ul>

<h3>6.2 Ajouter un produit</h3>
<div class="code-block">http://localhost/sunuprojet/modules/produits/create.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Ajout produit]</div>

<p><strong>Champs obligatoires :</strong></p>
<ul>
    <li>Code (ex: PROD-001)</li>
    <li>Nom</li>
    <li>Unité de mesure (pièce, kg, litre...)</li>
</ul>

<p><strong>Champs optionnels :</strong></p>
<ul>
    <li>Catégorie</li>
    <li>Description</li>
    <li>Prix de référence HT</li>
    <li>Taux de TVA (défaut: 18%)</li>
    <li>Seuil d'alerte</li>
    <li>Stock minimum / maximum</li>
    <li>Stock actuel</li>
</ul>

<h3>6.3 Gestion du stock</h3>
<p><strong>Important :</strong> Le stock est automatiquement mis à jour lors des réceptions de livraison.</p>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 7. DEMANDES D'ACHAT -->
<!-- ============================================================ -->
<h2 id="demandes">7. Demandes d'achat</h2>

<h3>7.1 Présentation</h3>
<p>Le module Demandes d'achat est le point de départ du processus achats.</p>
<div class="code-block">http://localhost/sunuprojet/modules/demandes/index.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Liste des demandes]</div>

<h3>7.2 Statuts des demandes</h3>
<table>
    <thead>
        <tr><th>Statut</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td>Brouillon</td><td>En cours de rédaction</td></tr>
        <tr><td>En validation</td><td>Soumise, en attente d'approbation</td></tr>
        <tr><td>Validée</td><td>Approuvée, peut être transformée en BC</td></tr>
        <tr><td>Rejetée</td><td>Refusée par le validateur</td></tr>
        <tr><td>Transformée en BC</td><td>Un bon de commande a été créé</td></tr>
    </tbody>
</table>

<h3>7.3 Créer une demande</h3>
<div class="code-block">http://localhost/sunuprojet/modules/demandes/create.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Création demande]</div>

<p><strong>Étapes :</strong></p>
<ol>
    <li><strong>Informations générales</strong>
        <ul>
            <li>Titre (obligatoire)</li>
            <li>Description</li>
            <li>Date souhaitée</li>
            <li>Type d'approvisionnement</li>
            <li>Justification</li>
        </ul>
    </li>
    <li><strong>Lignes de la demande</strong>
        <ul>
            <li>Cliquez sur <strong>"Ajouter une ligne"</strong></li>
            <li>Sélectionnez un produit ou saisissez une description libre</li>
            <li>Indiquez la quantité, l'unité et le prix estimé</li>
        </ul>
    </li>
    <li><strong>Enregistrement</strong>
        <ul>
            <li>Cliquez sur <strong>"Enregistrer la demande"</strong></li>
        </ul>
    </li>
</ol>

<h3>7.4 Soumettre pour validation</h3>
<ol>
    <li>Ouvrir la demande dans la liste</li>
    <li>Cliquer sur l'icône <strong>📤</strong> (Soumettre)</li>
    <li>Le statut passe à <strong>"En validation"</strong></li>
</ol>

<h3>7.5 Valider ou rejeter</h3>
<p><strong>Pour le validateur :</strong></p>
<ol>
    <li>Ouvrir la demande en statut <strong>"En validation"</strong></li>
    <li>Cliquer sur <strong>✅</strong> pour valider ou <strong>❌</strong> pour rejeter</li>
    <li>Si rejet, saisir un motif</li>
</ol>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 8. BONS DE COMMANDE -->
<!-- ============================================================ -->
<h2 id="bc">8. Bons de commande</h2>

<h3>8.1 Présentation</h3>
<p>Le module Bons de commande est le cœur du processus achats.</p>
<div class="code-block">http://localhost/sunuprojet/modules/bons_commande/index.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Liste des BC]</div>

<h3>8.2 Statuts des bons de commande</h3>
<table>
    <thead>
        <tr><th>Statut</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td>Brouillon</td><td>En cours de création</td></tr>
        <tr><td>Envoyé</td><td>Transmis au fournisseur</td></tr>
        <tr><td>Confirmé</td><td>Accepté par le fournisseur</td></tr>
        <tr><td>Expédié</td><td>En cours de transport</td></tr>
        <tr><td>Partiellement reçu</td><td>Livraison partielle</td></tr>
        <tr><td>Totalement reçu</td><td>Intégralement livré</td></tr>
        <tr><td>Facturé</td><td>Facture reçue</td></tr>
        <tr><td>Payé</td><td>Paiement effectué</td></tr>
        <tr><td>Annulé</td><td>Annulé</td></tr>
    </tbody>
</table>

<h3>8.3 Créer un bon de commande</h3>
<div class="code-block">http://localhost/sunuprojet/modules/bons_commande/create.php</div>

<p><strong>Deux méthodes :</strong></p>
<p><strong>Méthode 1 : Depuis une demande validée</strong></p>
<ul>
    <li>Dans la liste des demandes, cliquer sur <strong>"BC"</strong></li>
</ul>
<p><strong>Méthode 2 : Création manuelle</strong></p>
<ul>
    <li>Cliquer sur <strong>"Nouveau bon de commande"</strong></li>
</ul>
<div class="capture-placeholder">📸 [Capture d'écran : Création BC]</div>

<h3>8.4 Envoyer le bon de commande</h3>
<ol>
    <li>Ouvrir le BC en statut <strong>"Brouillon"</strong></li>
    <li>Cliquer sur <strong>"Envoyer"</strong></li>
    <li>Le statut passe à <strong>"Envoyé"</strong></li>
</ol>

<h3>8.5 Visualiser un BC</h3>
<div class="code-block">http://localhost/sunuprojet/modules/bons_commande/view.php?id=...</div>
<div class="capture-placeholder">📸 [Capture d'écran : Détail BC]</div>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 9. LIVRAISONS -->
<!-- ============================================================ -->
<h2 id="livraisons">9. Livraisons</h2>

<h3>9.1 Présentation</h3>
<p>Le module Livraisons permet de réceptionner les commandes.</p>
<div class="code-block">http://localhost/sunuprojet/modules/livraisons/index.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Liste des livraisons]</div>

<h3>9.2 Réceptionner une commande</h3>
<div class="code-block">http://localhost/sunuprojet/modules/livraisons/create.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Réception livraison]</div>

<p><strong>Étapes :</strong></p>
<ol>
    <li><strong>Sélectionner le BC</strong>
        <ul>
            <li>Choisir dans la liste des BC en statut "Envoyé", "Confirmé" ou "Expédié"</li>
        </ul>
    </li>
    <li><strong>Saisir les informations de livraison</strong>
        <ul>
            <li>Date de livraison</li>
            <li>Transporteur</li>
            <li>Numéro de suivi</li>
            <li>Notes</li>
        </ul>
    </li>
    <li><strong>Indiquer les quantités reçues</strong>
        <ul>
            <li>Pour chaque ligne, saisir la quantité reçue</li>
            <li>Cochez "Conforme" si la qualité est bonne</li>
        </ul>
    </li>
    <li><strong>Valider</strong>
        <ul>
            <li>Cliquer sur <strong>"Valider la réception"</strong></li>
        </ul>
    </li>
</ol>

<h3>9.3 Mise à jour automatique</h3>
<p>Lors de la réception :</p>
<ul>
    <li>Le stock est automatiquement mis à jour</li>
    <li>Le statut du BC passe à "Partiellement reçu" ou "Totalement reçu"</li>
</ul>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 10. FACTURES -->
<!-- ============================================================ -->
<h2 id="factures">10. Factures et paiements</h2>

<h3>10.1 Liste des factures</h3>
<div class="code-block">http://localhost/sunuprojet/modules/factures/index.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Liste des factures]</div>

<h3>10.2 Statuts des factures</h3>
<table>
    <thead>
        <tr><th>Statut</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td>En attente</td><td>Facture reçue, en attente d'approbation</td></tr>
        <tr><td>Approuvée</td><td>Validée pour paiement</td></tr>
        <tr><td>Payée</td><td>Paiement effectué</td></tr>
        <tr><td>Échoué</td><td>Problème de paiement</td></tr>
    </tbody>
</table>

<h3>10.3 Créer une facture</h3>
<div class="code-block">http://localhost/sunuprojet/modules/factures/create.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Création facture]</div>

<p><strong>Étapes :</strong></p>
<ol>
    <li>Sélectionner le fournisseur</li>
    <li>Sélectionner le bon de commande associé (optionnel)</li>
    <li>Saisir les dates (émission, échéance)</li>
    <li>Renseigner les montants (HT, TVA, TTC)</li>
    <li>Enregistrer</li>
</ol>

<h3>10.4 Approuver une facture</h3>
<ol>
    <li>Ouvrir la facture en statut <strong>"En attente"</strong></li>
    <li>Cliquer sur <strong>"Approuver"</strong></li>
    <li>Le statut passe à <strong>"Approuvée"</strong></li>
</ol>

<h3>10.5 Enregistrer un paiement</h3>
<div class="code-block">http://localhost/sunuprojet/modules/factures/paiement.php</div>

<p><strong>Méthodes de paiement :</strong></p>
<ul>
    <li>Virement bancaire</li>
    <li>Chèque</li>
    <li>Espèces</li>
    <li>Wave</li>
    <li>Orange Money</li>
    <li>Free Money</li>
</ul>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 11. STOCK -->
<!-- ============================================================ -->
<h2 id="stock">11. Gestion de stock</h2>

<h3>11.1 Liste des stocks</h3>
<div class="code-block">http://localhost/sunuprojet/modules/stock/index.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Liste des stocks]</div>

<h3>11.2 Fonctionnalités</h3>
<ul>
    <li>Affichage du stock actuel pour chaque produit</li>
    <li>Valeur totale du stock</li>
    <li>Alertes visuelles :
        <ul>
            <li><strong style="color:red;">Rouge</strong> : Stock ≤ seuil d'alerte</li>
            <li><strong style="color:orange;">Orange</strong> : Stock ≤ stock minimum</li>
        </ul>
    </li>
</ul>

<h3>11.3 Mouvements de stock</h3>
<div class="code-block">http://localhost/sunuprojet/modules/stock/mouvements.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Mouvements de stock]</div>

<p><strong>Types de mouvements :</strong></p>
<ul>
    <li>Entrée (livraison)</li>
    <li>Sortie (vente)</li>
    <li>Ajustement + (correction à la hausse)</li>
    <li>Ajustement - (correction à la baisse)</li>
</ul>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 12. EXPORT PDF -->
<!-- ============================================================ -->
<h2 id="pdf">12. Export PDF</h2>

<h3>12.1 Exporter un bon de commande</h3>
<div class="capture-placeholder">📸 [Capture d'écran : Export PDF]</div>

<p><strong>Étapes :</strong></p>
<ol>
    <li>Ouvrir le détail du BC</li>
    <li>Cliquer sur <strong>"Télécharger PDF"</strong></li>
    <li>Dans la nouvelle fenêtre, cliquer sur <strong>"Télécharger en PDF"</strong></li>
    <li>Dans la boîte de dialogue d'impression, choisir <strong>"Enregistrer au format PDF"</strong></li>
    <li>Cliquer sur <strong>"Enregistrer"</strong></li>
</ol>
<p><em>Astuce : Vous pouvez aussi utiliser le raccourci <code>Ctrl + P</code> puis choisir "Enregistrer au format PDF".</em></p>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 13. DECONNEXION -->
<!-- ============================================================ -->
<h2 id="deconnexion">13. Déconnexion</h2>

<h3>13.1 Se déconnecter</h3>
<ol>
    <li>Cliquer sur le nom de l'utilisateur en haut à droite</li>
    <li>Cliquer sur <strong>"Déconnexion"</strong></li>
</ol>
<div class="code-block">http://localhost/sunuprojet/logout.php</div>
<div class="capture-placeholder">📸 [Capture d'écran : Menu déconnexion]</div>

<h3>13.2 Sécurité</h3>
<ul>
    <li>La déconnexion détruit la session</li>
    <li>Toute tentative d'accès à une page protégée redirige vers <code>login.php</code></li>
</ul>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- 14. DEPANNAGE -->
<!-- ============================================================ -->
<h2 id="depannage">14. Dépannage</h2>

<h3>14.1 Erreur de connexion</h3>
<p><strong>Problème :</strong> "Email ou mot de passe incorrect"</p>
<p><strong>Solutions :</strong></p>
<ul>
    <li>Vérifier l'orthographe de l'email</li>
    <li>Vérifier la casse du mot de passe</li>
    <li>Utiliser les comptes de démonstration</li>
</ul>

<h3>14.2 Erreur 404 (Not Found)</h3>
<p><strong>Problème :</strong> La page demandée n'existe pas</p>
<p><strong>Solutions :</strong></p>
<ul>
    <li>Vérifier l'URL</li>
    <li>S'assurer que les fichiers sont au bon endroit (<code>C:\xampp\htdocs\sunuprojet\</code>)</li>
    <li>Vérifier que XAMPP (Apache) est démarré</li>
</ul>

<h3>14.3 Erreur de base de données</h3>
<p><strong>Problème :</strong> "Connection refused"</p>
<p><strong>Solutions :</strong></p>
<ul>
    <li>Vérifier que MySQL est démarré dans XAMPP</li>
    <li>Vérifier les identifiants dans <code>config/database.php</code></li>
</ul>

<h3>14.4 Le PDF ne s'affiche pas</h3>
<p><strong>Problème :</strong> Le PDF ne s'ouvre pas</p>
<p><strong>Solutions :</strong></p>
<ul>
    <li>Utiliser la fonction "Imprimer" du navigateur (<code>Ctrl + P</code>)</li>
    <li>Choisir "Enregistrer au format PDF"</li>
    <li>Vérifier que le fichier <code>pdf_simple.php</code> existe</li>
</ul>

<h3>14.5 Les boutons ne répondent pas</h3>
<p><strong>Problème :</strong> Les actions ne fonctionnent pas</p>
<p><strong>Solutions :</strong></p>
<ul>
    <li>Vérifier que votre rôle a les permissions nécessaires</li>
    <li>Rafraîchir la page (F5)</li>
    <li>Vider le cache du navigateur</li>
</ul>

<div class="page-break"></div>

<!-- ============================================================ -->
<!-- ANNEXES -->
<!-- ============================================================ -->
<h2 id="annexes">Annexes</h2>

<h3>A. Structure des rôles</h3>
<table>
    <thead>
        <tr><th>Rôle</th><th>Permissions</th></tr>
    </thead>
    <tbody>
        <tr><td>Admin</td><td>Accès complet à tous les modules</td></tr>
        <tr><td>Demandeur</td><td>Créer et soumettre des demandes</td></tr>
        <tr><td>Validateur</td><td>Valider / Rejeter les demandes</td></tr>
        <tr><td>Acheteur</td><td>Créer BC, factures, paiements</td></tr>
        <tr><td>Responsable stock</td><td>Gérer les livraisons</td></tr>
    </tbody>
</table>

<h3>B. URLs principales</h3>
<table>
    <thead>
        <tr><th>Page</th><th>URL</th></tr>
    </thead>
    <tbody>
        <tr><td>Connexion</td><td>http://localhost/sunuprojet/login.php</td></tr>
        <tr><td>Tableau de bord</td><td>http://localhost/sunuprojet/dashboard.php</td></tr>
        <tr><td>Fournisseurs</td><td>http://localhost/sunuprojet/modules/fournisseurs/index.php</td></tr>
        <tr><td>Produits</td><td>http://localhost/sunuprojet/modules/produits/index.php</td></tr>
        <tr><td>Demandes</td><td>http://localhost/sunuprojet/modules/demandes/index.php</td></tr>
        <tr><td>Bons de commande</td><td>http://localhost/sunuprojet/modules/bons_commande/index.php</td></tr>
        <tr><td>Livraisons</td><td>http://localhost/sunuprojet/modules/livraisons/index.php</td></tr>
        <tr><td>Factures</td><td>http://localhost/sunuprojet/modules/factures/index.php</td></tr>
        <tr><td>Stock</td><td>http://localhost/sunuprojet/modules/stock/index.php</td></tr>
    </tbody>
</table>

<h3>C. Structure de la base de données</h3>
<table>
    <thead>
        <tr><th>Table</th><th>Rôle</th></tr>
    </thead>
    <tbody>
        <tr><td>utilisateurs</td><td>Gestion des comptes</td></tr>
        <tr><td>fournisseurs</td><td>Référentiel fournisseurs</td></tr>
        <tr><td>produits</td><td>Catalogue produits</td></tr>
        <tr><td>demandes_achat</td><td>Demandes d'achat</td></tr>
        <tr><td>bons_commande</td><td>Bons de commande</td></tr>
        <tr><td>livraisons</td><td>Suivi des livraisons</td></tr>
        <tr><td>factures</td><td>Factures fournisseurs</td></tr>
        <tr><td>paiements</td><td>Paiements effectués</td></tr>
    </tbody>
</table>

<!-- ============================================================ -->
<!-- FOOTER -->
<!-- ============================================================ -->
<div class="footer">
    <p>SUNUPROJET - Gestion des Achats et Approvisionnements</p>
    <p>Document généré le <?= date('d/m/Y') ?> - Version 1.0</p>
</div>

<script>
    // Script pour l'impression automatique
    document.addEventListener('DOMContentLoaded', function() {
        // Pas d'impression automatique pour éviter les fenêtres pop-up
    });
</script>

</body>
</html>