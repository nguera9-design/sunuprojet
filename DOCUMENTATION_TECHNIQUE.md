# DOCUMENTATION TECHNIQUE – SUNUPROJET

## 1. Présentation

**SUNUPROJET** est un site web pour gérer les achats d'une entreprise.

**Groupe :** Groupe 14 – DESCAF 1  
**Année :** 2025-2026

---

## 2. Ce que fait le site

| Ce qu'on peut faire | Qui le fait |
|---------------------|-------------|
| Demander des produits | Le demandeur |
| Valider les demandes | Le validateur |
| Acheter les produits | L'acheteur |
| Recevoir les marchandises | Le responsable stock |
| Gérer tout | L'administrateur |

---

## 3. Quels outils on a utilisés

| Outil | À quoi ça sert |
|-------|----------------|
| PHP | Pour faire fonctionner le site |
| MySQL | Pour stocker les données |
| Apache | Pour afficher le site |
| Bootstrap | Pour faire un beau design |
| Chart.js | Pour faire des graphiques |
| Git | Pour sauvegarder le code sur GitHub |

---

## 4. Comment est rangé le projet
sunuprojet/
├── modules/ # Les pages du site
│ ├── fournisseurs/ # Gestion des fournisseurs
│ ├── produits/ # Gestion des produits
│ ├── demandes/ # Demandes d'achat
│ └── bons_commande/ # Bons de commande
├── includes/ # Le menu et la barre de navigation
├── config/ # La connexion à la base de données
├── sql/ # Le script de la base de données
├── login.php # La page de connexion
└── dashboard.php # Le tableau de bord

---

## 5. La base de données

### 5.1 Les tables importantes

| Table | Dans cette table on stocke |
|-------|----------------------------|
| `utilisateurs` | Les comptes des utilisateurs |
| `fournisseurs` | La liste des fournisseurs |
| `produits` | La liste des produits |
| `demandes_achat` | Les demandes d'achat |
| `bons_commande` | Les bons de commande |
| `livraisons` | Les livraisons |
| `factures` | Les factures |

### 5.2 Comment les tables sont reliées
utilisateurs → demandes_achat → bons_commande → livraisons → factures
fournisseurs → bons_commande
produits → bons_commande_lignes

---
## 6. Les rôles (qui peut faire quoi)

| Rôle | Ce qu'il peut faire |
|------|---------------------|
| Admin | Tout |
| Acheteur | Gérer les fournisseurs, produits, commandes, factures |
| Demandeur | Faire des demandes d'achat |
| Validateur | Valider ou refuser les demandes |
| Responsable stock | Gérer les livraisons et le stock |

---

## 7. Comment le site est sécurisé

| Problème | Solution |
|----------|----------|
| Quelqu'un peut voler les mots de passe | Les mots de passe sont cachés (hashés) |
| Quelqu'un peut faire des injections SQL | On utilise des requêtes préparées |
| Quelqu'un peut faire des attaques XSS | On utilise htmlspecialchars() |
| Quelqu'un peut accéder à une page sans se connecter | On vérifie les sessions |

---

## 8. Comment installer le site

### Étape 1 : Installer XAMPP
Télécharger et installer XAMPP

### Étape 2 : Copier le projet
Copier le dossier `sunuprojet` dans `C:\xampp\htdocs\`

### Étape 3 : Démarrer XAMPP
Lancer Apache et MySQL

### Étape 4 : Créer la base de données
Aller sur `http://localhost/phpmyadmin`  
Créer une base appelée `sunuprojet`

### Étape 5 : Importer les données
Importer le fichier `sunuprojet.sql`

### Étape 6 : Ouvrir le site
Aller sur `http://localhost/sunuprojet/login.php`

---

## 9. Les comptes pour tester

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@sunuprojet.sn | password123 | Admin |
| demandeur@sunuprojet.sn | password123 | Demandeur |
| validateur@sunuprojet.sn | password123 | Validateur |
| acheteur@sunuprojet.sn | password123 | Acheteur |
| stock@sunuprojet.sn | password123 | Responsable stock |

---

## 10. Comment ça marche (les étapes)

### Une demande d'achat
1. Le **demandeur** crée une demande
2. Il la soumet
3. Le **validateur** l'approuve ou la refuse

### Un bon de commande
1. L'**acheteur** crée le bon de commande
2. Il l'envoie au fournisseur
3. Le fournisseur confirme

### Une livraison
1. Le **responsable stock** sélectionne le bon de commande
2. Il saisit ce qu'il a reçu
3. Le stock est automatiquement mis à jour

### Une facture
1. L'**acheteur** crée la facture
2. Il l'approuve
3. Il enregistre le paiement

---

## 11. Exporter en PDF

1. Ouvrir le bon de commande
2. Cliquer sur "Télécharger PDF"
3. Choisir "Enregistrer au format PDF"

---

## 12. Le code sur GitHub

**Lien :** https://github.com/nguera9-design/sunuprojet

### Les commandes Git

| Ce qu'on veut faire | La commande |
|---------------------|-------------|
| Ajouter des fichiers | `git add .` |
| Sauvegarder | `git commit -m "message"` |
| Envoyer sur GitHub | `git push` |
| Récupérer les modifs | `git pull` |

---

## 13. Les auteurs

**Groupe 14 – DESCAF 1**
- Abdou NGUER
- [Prénom2] [NOM2]
- [Prénom3] [NOM3]

**Année :** 2025-2026