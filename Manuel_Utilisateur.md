# MANUEL UTILISATEUR – SUNUPROJET

## 1. Introduction

SUNUPROJET est une plateforme web de gestion des achats et approvisionnements destinée aux PME sénégalaises.

L'application permet de :
- Gérer les fournisseurs
- Gérer les produits
- Créer et valider des demandes d'achat
- Créer des bons de commande
- Réceptionner des livraisons
- Gérer les factures et paiements
- Exporter des documents en PDF

---

## 2. Connexion

### 2.1 Page de connexion

Ouvrez votre navigateur et allez à l'adresse :http://localhost/sunuprojet/login.php

### 2.2 Identifiants de démonstration

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | admin@sunuprojet.sn | password123 |
| Demandeur | demandeur@sunuprojet.sn | password123 |
| Validateur | validateur@sunuprojet.sn | password123 |
| Acheteur | acheteur@sunuprojet.sn | password123 |
| Responsable stock | stock@sunuprojet.sn | password123 |

### 2.3 Procédure

1. Saisissez votre adresse email
2. Saisissez votre mot de passe
3. Cliquez sur **"Se connecter"**

---

## 3. Tableau de bord

Après la connexion, vous arrivez sur le tableau de bord.

### 3.1 Statistiques

Le tableau de bord affiche 4 indicateurs :
- Nombre de demandes d'achat
- Nombre de bons de commande
- Nombre de fournisseurs actifs
- Alertes stock

### 3.2 Menu de navigation

Le menu de gauche permet d'accéder à tous les modules :
- Tableau de bord
- Demandes d'achat
- Fournisseurs
- Produits
- Bons de commande
- Livraisons
- Factures
- Stock

---

## 4. Gestion des fournisseurs

### 4.1 Liste des fournisseurs

**Accès :** Menu → Fournisseurs

La liste affiche tous les fournisseurs avec :
- Leur code
- Leur nom
- Leur email
- Leur téléphone
- Leur notation (étoiles)

### 4.2 Ajouter un fournisseur

1. Cliquez sur **"Nouveau fournisseur"**
2. Remplissez le formulaire
3. Cliquez sur **"Enregistrer"**

**Champs obligatoires :**
- Code
- Nom

### 4.3 Modifier un fournisseur

1. Cliquez sur l'icône ✏️
2. Modifiez les informations
3. Cliquez sur **"Enregistrer"**

### 4.4 Supprimer un fournisseur

1. Cliquez sur l'icône 🗑️
2. Confirmez la suppression

---

## 5. Gestion des produits

### 5.1 Liste des produits

**Accès :** Menu → Produits

La liste affiche tous les produits avec :
- Leur code
- Leur nom
- Leur catégorie
- Leur unité de mesure
- Leur prix de référence
- Le stock actuel

### 5.2 Ajouter un produit

1. Cliquez sur **"Nouveau produit"**
2. Remplissez le formulaire
3. Cliquez sur **"Enregistrer"**

**Champs obligatoires :**
- Code
- Nom
- Unité de mesure

### 5.3 Modifier un produit

1. Cliquez sur l'icône ✏️
2. Modifiez les informations
3. Cliquez sur **"Enregistrer"**

### 5.4 Supprimer un produit

1. Cliquez sur l'icône 🗑️
2. Confirmez la suppression

---

## 6. Demandes d'achat

### 6.1 Liste des demandes

**Accès :** Menu → Demandes d'achat

### 6.2 Créer une demande

1. Cliquez sur **"Nouvelle demande"**
2. Remplissez les informations
3. Ajoutez des lignes (produits)
4. Cliquez sur **"Enregistrer"**

### 6.3 Soumettre une demande

1. Ouvrez la demande
2. Cliquez sur l'icône 📤 (Soumettre)
3. Le statut passe à "En validation"

### 6.4 Valider une demande (Validateur)

1. Ouvrez la demande en statut "En validation"
2. Cliquez sur ✅ pour valider
3. Ou sur ❌ pour rejeter

---

## 7. Bons de commande

### 7.1 Liste des bons de commande

**Accès :** Menu → Bons de commande

### 7.2 Créer un bon de commande

**Méthode 1 :** Depuis une demande validée
1. Allez sur la demande
2. Cliquez sur **"BC"**

**Méthode 2 :** Création manuelle
1. Cliquez sur **"Nouveau bon de commande"**
2. Sélectionnez le fournisseur
3. Ajoutez les produits
4. Cliquez sur **"Enregistrer"**

### 7.3 Envoyer un bon de commande

1. Ouvrez le BC en statut "Brouillon"
2. Cliquez sur **"Envoyer"**

### 7.4 Exporter en PDF

1. Ouvrez le BC
2. Cliquez sur **"Télécharger PDF"**
3. Choisissez **"Enregistrer au format PDF"**

---

## 8. Livraisons

### 8.1 Réceptionner une commande

**Accès :** Menu → Livraisons → "Nouvelle réception"

1. Sélectionnez le bon de commande
2. Saisissez les informations de livraison
3. Indiquez les quantités reçues
4. Cliquez sur **"Valider"**

Le stock est automatiquement mis à jour.

---

## 9. Factures

### 9.1 Créer une facture

**Accès :** Menu → Factures → "Nouvelle facture"

1. Sélectionnez le fournisseur
2. Sélectionnez le bon de commande
3. Remplissez les montants
4. Cliquez sur **"Enregistrer"**

### 9.2 Approuver une facture

1. Ouvrez la facture en statut "En attente"
2. Cliquez sur **"Approuver"**

### 9.3 Enregistrer un paiement

1. Ouvrez la facture
2. Cliquez sur **"Payer"**
3. Remplissez les informations
4. Cliquez sur **"Enregistrer"**

---

## 10. Gestion de stock

**Accès :** Menu → Stock

### 10.1 Liste des stocks

Affiche tous les produits avec leur quantité actuelle.
- Les produits en alerte sont surlignés en rouge.

### 10.2 Mouvements de stock

**Accès :** Menu → Stock → "Voir les mouvements"

Affiche l'historique de tous les mouvements de stock.

---

## 11. Appels d'offres

**Accès :** Menu → Appels d'offres

### 11.1 Créer un appel d'offres

1. Cliquez sur **"Nouvel appel d'offres"**
2. Remplissez les informations
3. Cliquez sur **"Enregistrer"**

### 11.2 Gérer les offres

1. Ouvrez l'appel d'offres
2. Cliquez sur **"Offres"**
3. Ajoutez les offres reçues
4. Sélectionnez la meilleure offre

---

## 12. Rapports

### 12.1 Classement ABC

**Accès :** Menu → Classement ABC

Affiche les fournisseurs classés par montant d'achat.

### 12.2 Rapports statistiques

**Accès :** Menu → Rapports statistiques

Affiche les statistiques des achats.

---

## 13. Déconnexion

1. Cliquez sur votre nom en haut à droite
2. Cliquez sur **"Déconnexion"**

---

## 14. Dépannage

### Erreur de connexion
- Vérifiez vos identifiants
- Utilisez les comptes de démonstration

### Erreur 404
- Vérifiez que XAMPP est lancé
- Vérifiez que le dossier est dans `C:\xampp\htdocs\`

### Le PDF ne s'affiche pas
- Utilisez la fonction "Imprimer" du navigateur
- Choisissez "Enregistrer au format PDF"
- ## 15. Structure des rôles

| Rôle | Permissions |
|------|-------------|
| Admin | Accès complet à tous les modules |
| Demandeur | Créer et soumettre des demandes |
| Validateur | Valider/Rejeter les demandes |
| Acheteur | Créer BC, factures, paiements |
| Responsable stock | Gérer les livraisons |

---

## 16. URLs principales

| Page | URL |
|------|-----|
| Connexion | http://localhost/sunuprojet/login.php |
| Tableau de bord | http://localhost/sunuprojet/dashboard.php |
| Fournisseurs | http://localhost/sunuprojet/modules/fournisseurs/index.php |
| Produits | http://localhost/sunuprojet/modules/produits/index.php |
| Demandes | http://localhost/sunuprojet/modules/demandes/index.php |
| Bons de commande | http://localhost/sunuprojet/modules/bons_commande/index.php |
| Livraisons | http://localhost/sunuprojet/modules/livraisons/index.php |
| Factures | http://localhost/sunuprojet/modules/factures/index.php |
| Stock | http://localhost/sunuprojet/modules/stock/index.php |

---

**Fin du document**

Groupe 14 - DESCAF 1
Année universitaire 2025-2026
