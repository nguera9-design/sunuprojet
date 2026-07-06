-- ============================================================
--  BASE DE DONNÉES : SUNUPROJET
--  Projet 14 : Gestion des Achats et Approvisionnements
--  Groupe 14 - DESCAF 1
--  Version MySQL (100% compatible XAMPP)
-- ============================================================

-- ============================================================
-- 1. SUPPRESSION DES TABLES (si elles existent)
-- ============================================================

DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS mouvements_stock;
DROP TABLE IF EXISTS evaluations_fournisseurs;
DROP TABLE IF EXISTS paiements;
DROP TABLE IF EXISTS factures;
DROP TABLE IF EXISTS livraisons_lignes;
DROP TABLE IF EXISTS livraisons;
DROP TABLE IF EXISTS bons_commande_lignes;
DROP TABLE IF EXISTS bons_commande;
DROP TABLE IF EXISTS offres_fournisseurs;
DROP TABLE IF EXISTS appels_offres;
DROP TABLE IF EXISTS demandes_achat_lignes;
DROP TABLE IF EXISTS demandes_achat;
DROP TABLE IF EXISTS fournisseurs_produits;
DROP TABLE IF EXISTS produits;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS fournisseurs;
DROP TABLE IF EXISTS utilisateurs;

-- ============================================================
-- 2. TABLES PRINCIPALES
-- ============================================================

-- 2.1 Utilisateurs
CREATE TABLE utilisateurs (
    id VARCHAR(36) PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    service VARCHAR(100),
    telephone VARCHAR(30),
    actif TINYINT(1) DEFAULT 1,
    derniere_connexion DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.2 Fournisseurs
CREATE TABLE fournisseurs (
    id VARCHAR(36) PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(200) NOT NULL,
    nom_commercial VARCHAR(200),
    siret VARCHAR(14),
    tva_intracommunautaire VARCHAR(20),
    adresse_ligne1 VARCHAR(255) NOT NULL,
    adresse_ligne2 VARCHAR(255),
    code_postal VARCHAR(10) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    pays VARCHAR(100) NOT NULL,
    telephone VARCHAR(30),
    email VARCHAR(255),
    site_web VARCHAR(255),
    contact_nom VARCHAR(100),
    contact_fonction VARCHAR(100),
    contact_telephone VARCHAR(30),
    contact_email VARCHAR(255),
    categorie VARCHAR(50),
    notation_qualite INT DEFAULT 0,
    notation_delai INT DEFAULT 0,
    notation_prix INT DEFAULT 0,
    notation_service INT DEFAULT 0,
    actif TINYINT(1) DEFAULT 1,
    conditions_paiement VARCHAR(100),
    delai_livraison_jours INT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    updated_by VARCHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.3 Catégories
CREATE TABLE categories (
    id VARCHAR(36) PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id VARCHAR(36),
    actif TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.4 Produits
CREATE TABLE produits (
    id VARCHAR(36) PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    categorie_id VARCHAR(36),
    unite_mesure VARCHAR(20) NOT NULL,
    prix_reference_ht DECIMAL(15,2) DEFAULT 0,
    devise VARCHAR(3) DEFAULT NULL,
    taux_tva DECIMAL(5,2) DEFAULT 18.00,
    seuil_alerte INT DEFAULT 0,
    stock_minimum INT DEFAULT 0,
    stock_maximum INT DEFAULT 0,
    stock_actuel INT DEFAULT 0,
    delai_approvisionnement_jours INT,
    actif TINYINT(1) DEFAULT 1,
    est_service TINYINT(1) DEFAULT 0,
    code_barre VARCHAR(50),
    poids_unitaire DECIMAL(10,3),
    dimensions VARCHAR(100),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    updated_by VARCHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.5 Fournisseurs-Produits
CREATE TABLE fournisseurs_produits (
    id VARCHAR(36) PRIMARY KEY,
    fournisseur_id VARCHAR(36) NOT NULL,
    produit_id VARCHAR(36) NOT NULL,
    prix_achat_ht DECIMAL(15,2) NOT NULL,
    devise VARCHAR(3) DEFAULT NULL,
    delai_livraison_jours INT,
    conditions_remise TEXT,
    remise DECIMAL(5,2) DEFAULT 0,
    est_prioritaire TINYINT(1) DEFAULT 0,
    reference_fournisseur VARCHAR(100),
    actif TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fournisseur_produit (fournisseur_id, produit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.6 Demandes d'achat
CREATE TABLE demandes_achat (
    id VARCHAR(36) PRIMARY KEY,
    numero VARCHAR(50) UNIQUE NOT NULL,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    demandeur_id VARCHAR(36) NOT NULL,
    service VARCHAR(100),
    date_demande DATE DEFAULT NULL,
    date_souhaitee DATE,
    date_limite DATE,
    statut VARCHAR(20) DEFAULT 'brouillon',
    type_approvisionnement VARCHAR(20) DEFAULT 'stock',
    budget_estime_ht DECIMAL(15,2),
    devise VARCHAR(3) DEFAULT NULL,
    justification TEXT,
    notes TEXT,
    date_soumission DATETIME,
    date_validation DATETIME,
    validateur_id VARCHAR(36),
    motif_rejet TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    updated_by VARCHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.7 Lignes de demande d'achat
CREATE TABLE demandes_achat_lignes (
    id VARCHAR(36) PRIMARY KEY,
    demande_achat_id VARCHAR(36) NOT NULL,
    produit_id VARCHAR(36),
    description_libre VARCHAR(500),
    quantite INT NOT NULL,
    unite_mesure VARCHAR(20) NOT NULL,
    prix_unitaire_estime_ht DECIMAL(15,2),
    total_estime_ht DECIMAL(15,2),
    notes VARCHAR(500),
    priorite INT DEFAULT 1,
    date_besoin DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.8 Appels d'offres
CREATE TABLE appels_offres (
    id VARCHAR(36) PRIMARY KEY,
    numero VARCHAR(50) UNIQUE NOT NULL,
    demande_achat_id VARCHAR(36),
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    date_publication DATE NOT NULL,
    date_limite_reponse DATE NOT NULL,
    montant_estime_ht DECIMAL(15,2),
    devise VARCHAR(3) DEFAULT NULL,
    statut VARCHAR(20) DEFAULT 'en_cours',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    updated_by VARCHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.9 Offres fournisseurs
CREATE TABLE offres_fournisseurs (
    id VARCHAR(36) PRIMARY KEY,
    appel_offre_id VARCHAR(36) NOT NULL,
    fournisseur_id VARCHAR(36) NOT NULL,
    montant_ht DECIMAL(15,2) NOT NULL,
    devise VARCHAR(3) DEFAULT NULL,
    delai_livraison_jours INT,
    conditions_paiement VARCHAR(100),
    notes TEXT,
    est_selectionnee TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_appel_fournisseur (appel_offre_id, fournisseur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.10 Bons de commande
CREATE TABLE bons_commande (
    id VARCHAR(36) PRIMARY KEY,
    numero VARCHAR(50) UNIQUE NOT NULL,
    fournisseur_id VARCHAR(36) NOT NULL,
    demande_achat_id VARCHAR(36),
    appel_offre_id VARCHAR(36),
    date_emission DATE DEFAULT NULL,
    date_livraison_prevue DATE,
    date_livraison_effective DATE,
    statut VARCHAR(20) DEFAULT 'brouillon',
    conditions_paiement VARCHAR(100),
    conditions_livraison VARCHAR(100),
    adresse_livraison_ligne1 VARCHAR(255),
    adresse_livraison_ligne2 VARCHAR(255),
    code_postal_livraison VARCHAR(10),
    ville_livraison VARCHAR(100),
    pays_livraison VARCHAR(100),
    total_ht DECIMAL(15,2) DEFAULT 0,
    total_tva DECIMAL(15,2) DEFAULT 0,
    total_ttc DECIMAL(15,2) DEFAULT 0,
    devise VARCHAR(3) DEFAULT NULL,
    notes TEXT,
    valide_par VARCHAR(36),
    date_validation DATETIME,
    envoye_par VARCHAR(36),
    date_envoi DATETIME,
    confirme_par VARCHAR(100),
    date_confirmation DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    updated_by VARCHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.11 Lignes de bon de commande
CREATE TABLE bons_commande_lignes (
    id VARCHAR(36) PRIMARY KEY,
    bon_commande_id VARCHAR(36) NOT NULL,
    produit_id VARCHAR(36),
    demande_ligne_id VARCHAR(36),
    description VARCHAR(500),
    quantite INT NOT NULL,
    quantite_recue INT DEFAULT 0,
    prix_unitaire_ht DECIMAL(15,2) NOT NULL,
    remise DECIMAL(5,2) DEFAULT 0,
    total_ht DECIMAL(15,2) NOT NULL,
    taux_tva DECIMAL(5,2) NOT NULL,
    total_tva DECIMAL(15,2),
    total_ttc DECIMAL(15,2),
    date_livraison_prevue DATE,
    notes VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.12 Livraisons
CREATE TABLE livraisons (
    id VARCHAR(36) PRIMARY KEY,
    numero VARCHAR(50) UNIQUE NOT NULL,
    bon_commande_id VARCHAR(36) NOT NULL,
    date_livraison DATE DEFAULT NULL,
    date_prevue DATE NOT NULL,
    date_expedition DATE,
    statut VARCHAR(20) DEFAULT 'prevue',
    transporteur VARCHAR(100),
    numero_suivi VARCHAR(100),
    poids_total DECIMAL(10,3),
    nb_colis INT,
    adresse_livraison TEXT,
    notes TEXT,
    recu_par VARCHAR(100),
    date_reception DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    updated_by VARCHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.13 Lignes de livraison
CREATE TABLE livraisons_lignes (
    id VARCHAR(36) PRIMARY KEY,
    livraison_id VARCHAR(36) NOT NULL,
    bon_commande_ligne_id VARCHAR(36) NOT NULL,
    quantite_livree INT NOT NULL,
    quantite_refusee INT DEFAULT 0,
    motif_refus TEXT,
    qualite_conforme TINYINT(1) DEFAULT 1,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.14 Factures
CREATE TABLE factures (
    id VARCHAR(36) PRIMARY KEY,
    numero VARCHAR(50) UNIQUE NOT NULL,
    bon_commande_id VARCHAR(36),
    fournisseur_id VARCHAR(36) NOT NULL,
    date_emission DATE NOT NULL,
    date_echeance DATE NOT NULL,
    date_paiement DATE,
    total_ht DECIMAL(15,2) NOT NULL,
    total_tva DECIMAL(15,2) NOT NULL,
    total_ttc DECIMAL(15,2) NOT NULL,
    devise VARCHAR(3) DEFAULT NULL,
    statut VARCHAR(20) DEFAULT 'en_attente',
    methode_paiement VARCHAR(50),
    numero_facture_fournisseur VARCHAR(100),
    notes TEXT,
    fichier_url VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    updated_by VARCHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.15 Paiements
CREATE TABLE paiements (
    id VARCHAR(36) PRIMARY KEY,
    numero VARCHAR(50) UNIQUE NOT NULL,
    facture_id VARCHAR(36) NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    devise VARCHAR(3) DEFAULT NULL,
    date_paiement DATE NOT NULL,
    methode VARCHAR(50) NOT NULL,
    reference VARCHAR(100),
    statut VARCHAR(20) DEFAULT 'effectue',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    updated_by VARCHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.16 Évaluations fournisseurs
CREATE TABLE evaluations_fournisseurs (
    id VARCHAR(36) PRIMARY KEY,
    fournisseur_id VARCHAR(36) NOT NULL,
    livraison_id VARCHAR(36),
    note_qualite INT NOT NULL,
    note_delai INT NOT NULL,
    note_conformite INT NOT NULL,
    note_communication INT NOT NULL,
    commentaire TEXT,
    evalue_par VARCHAR(36),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.17 Mouvements de stock
CREATE TABLE mouvements_stock (
    id VARCHAR(36) PRIMARY KEY,
    produit_id VARCHAR(36) NOT NULL,
    quantite INT NOT NULL,
    type_mouvement VARCHAR(20) NOT NULL,
    reference_type VARCHAR(50) NOT NULL,
    reference_id VARCHAR(36) NOT NULL,
    stock_avant INT NOT NULL,
    stock_apres INT NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2.18 Audit log
CREATE TABLE audit_log (
    id VARCHAR(36) PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id VARCHAR(36) NOT NULL,
    action VARCHAR(20) NOT NULL,
    old_data JSON,
    new_data JSON,
    user_id VARCHAR(36),
    user_ip VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================================
-- 3. CONTRAINTES FOREIGN KEY
-- ============================================================

ALTER TABLE categories ADD CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL;
ALTER TABLE produits ADD CONSTRAINT fk_produits_categorie FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL;
ALTER TABLE fournisseurs_produits ADD CONSTRAINT fk_fp_fournisseur FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE CASCADE;
ALTER TABLE fournisseurs_produits ADD CONSTRAINT fk_fp_produit FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE;
ALTER TABLE demandes_achat ADD CONSTRAINT fk_da_demandeur FOREIGN KEY (demandeur_id) REFERENCES utilisateurs(id);
ALTER TABLE demandes_achat ADD CONSTRAINT fk_da_validateur FOREIGN KEY (validateur_id) REFERENCES utilisateurs(id);
ALTER TABLE demandes_achat_lignes ADD CONSTRAINT fk_dal_demande FOREIGN KEY (demande_achat_id) REFERENCES demandes_achat(id) ON DELETE CASCADE;
ALTER TABLE demandes_achat_lignes ADD CONSTRAINT fk_dal_produit FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE SET NULL;
ALTER TABLE appels_offres ADD CONSTRAINT fk_ao_demande FOREIGN KEY (demande_achat_id) REFERENCES demandes_achat(id) ON DELETE SET NULL;
ALTER TABLE offres_fournisseurs ADD CONSTRAINT fk_of_appel FOREIGN KEY (appel_offre_id) REFERENCES appels_offres(id) ON DELETE CASCADE;
ALTER TABLE offres_fournisseurs ADD CONSTRAINT fk_of_fournisseur FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id);
ALTER TABLE bons_commande ADD CONSTRAINT fk_bc_fournisseur FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id);
ALTER TABLE bons_commande ADD CONSTRAINT fk_bc_demande FOREIGN KEY (demande_achat_id) REFERENCES demandes_achat(id) ON DELETE SET NULL;
ALTER TABLE bons_commande ADD CONSTRAINT fk_bc_appel FOREIGN KEY (appel_offre_id) REFERENCES appels_offres(id) ON DELETE SET NULL;
ALTER TABLE bons_commande_lignes ADD CONSTRAINT fk_bcl_bon FOREIGN KEY (bon_commande_id) REFERENCES bons_commande(id) ON DELETE CASCADE;
ALTER TABLE bons_commande_lignes ADD CONSTRAINT fk_bcl_produit FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE SET NULL;
ALTER TABLE bons_commande_lignes ADD CONSTRAINT fk_bcl_demande_ligne FOREIGN KEY (demande_ligne_id) REFERENCES demandes_achat_lignes(id) ON DELETE SET NULL;
ALTER TABLE livraisons ADD CONSTRAINT fk_liv_bon FOREIGN KEY (bon_commande_id) REFERENCES bons_commande(id) ON DELETE CASCADE;
ALTER TABLE livraisons_lignes ADD CONSTRAINT fk_ll_livraison FOREIGN KEY (livraison_id) REFERENCES livraisons(id) ON DELETE CASCADE;
ALTER TABLE livraisons_lignes ADD CONSTRAINT fk_ll_bcl FOREIGN KEY (bon_commande_ligne_id) REFERENCES bons_commande_lignes(id) ON DELETE CASCADE;
ALTER TABLE factures ADD CONSTRAINT fk_fact_bon FOREIGN KEY (bon_commande_id) REFERENCES bons_commande(id) ON DELETE SET NULL;
ALTER TABLE factures ADD CONSTRAINT fk_fact_fournisseur FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id);
ALTER TABLE paiements ADD CONSTRAINT fk_paiement_facture FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE;
ALTER TABLE evaluations_fournisseurs ADD CONSTRAINT fk_ev_fournisseur FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE CASCADE;
ALTER TABLE evaluations_fournisseurs ADD CONSTRAINT fk_ev_livraison FOREIGN KEY (livraison_id) REFERENCES livraisons(id) ON DELETE SET NULL;
ALTER TABLE mouvements_stock ADD CONSTRAINT fk_ms_produit FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE;

-- ============================================================
-- 4. INDEX
-- ============================================================

CREATE INDEX idx_utilisateurs_email ON utilisateurs(email);
CREATE INDEX idx_utilisateurs_role ON utilisateurs(role);
CREATE INDEX idx_fournisseurs_code ON fournisseurs(code);
CREATE INDEX idx_fournisseurs_nom ON fournisseurs(nom);
CREATE INDEX idx_fournisseurs_actif ON fournisseurs(actif);
CREATE INDEX idx_produits_code ON produits(code);
CREATE INDEX idx_produits_nom ON produits(nom);
CREATE INDEX idx_produits_categorie ON produits(categorie_id);
CREATE INDEX idx_produits_stock ON produits(stock_actuel);
CREATE INDEX idx_fournisseurs_produits_fournisseur ON fournisseurs_produits(fournisseur_id);
CREATE INDEX idx_fournisseurs_produits_produit ON fournisseurs_produits(produit_id);
CREATE INDEX idx_demandes_achat_numero ON demandes_achat(numero);
CREATE INDEX idx_demandes_achat_demandeur ON demandes_achat(demandeur_id);
CREATE INDEX idx_demandes_achat_statut ON demandes_achat(statut);
CREATE INDEX idx_bons_commande_numero ON bons_commande(numero);
CREATE INDEX idx_bons_commande_fournisseur ON bons_commande(fournisseur_id);
CREATE INDEX idx_bons_commande_statut ON bons_commande(statut);
CREATE INDEX idx_livraisons_bon_commande ON livraisons(bon_commande_id);
CREATE INDEX idx_livraisons_statut ON livraisons(statut);
CREATE INDEX idx_factures_numero ON factures(numero);
CREATE INDEX idx_factures_fournisseur ON factures(fournisseur_id);
CREATE INDEX idx_factures_statut ON factures(statut);
CREATE INDEX idx_paiements_facture ON paiements(facture_id);
CREATE INDEX idx_mouvements_stock_produit ON mouvements_stock(produit_id);
CREATE INDEX idx_mouvements_stock_date ON mouvements_stock(created_at);
CREATE INDEX idx_audit_log_table_record ON audit_log(table_name, record_id);

-- ============================================================
-- 5. DONNÉES DE DÉMONSTRATION (mots de passe: password123)
-- ============================================================

-- 5.1 Utilisateurs (avec hash correct pour password123)
INSERT INTO utilisateurs (id, nom, prenom, email, mot_de_passe_hash, role, service, telephone, actif) VALUES
('u1111111-1111-1111-1111-111111111111', 'DIOP', 'Aminata', 'admin@sunuprojet.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Direction', '778901234', 1),
('u2222222-2222-2222-2222-222222222222', 'FALL', 'Mamadou', 'demandeur@sunuprojet.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'demandeur', 'Production', '771234567', 1),
('u3333333-3333-3333-3333-333333333333', 'NDOYE', 'Fatou', 'validateur@sunuprojet.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'validateur', 'Direction', '772345678', 1),
('u4444444-4444-4444-4444-444444444444', 'SOW', 'Ibrahima', 'acheteur@sunuprojet.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'acheteur', 'Achats', '773456789', 1),
('u5555555-5555-5555-5555-555555555555', 'DIA', 'Mariama', 'stock@sunuprojet.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable_stock', 'Logistique', '774567890', 1);

-- 5.2 Catégories
INSERT INTO categories (id, code, nom, description) VALUES
('c1111111-1111-1111-1111-111111111111', 'CAT-ELEC', 'Électronique', 'Composants et équipements électroniques'),
('c2222222-2222-2222-2222-222222222222', 'CAT-MEC', 'Mécanique', 'Pièces mécaniques et outillage'),
('c3333333-3333-3333-3333-333333333333', 'CAT-BUREAU', 'Fournitures de bureau', 'Papeterie et fournitures de bureau'),
('c4444444-4444-4444-4444-444444444444', 'CAT-INFO', 'Informatique', 'Matériel et logiciels informatiques'),
('c5555555-5555-5555-5555-555555555555', 'CAT-MAT', 'Matières premières', 'Matières premières pour production');

-- 5.3 Fournisseurs
INSERT INTO fournisseurs (id, code, nom, adresse_ligne1, code_postal, ville, pays, telephone, email, notation_qualite, notation_delai, notation_prix, notation_service) VALUES
('f1111111-1111-1111-1111-111111111111', 'F001', 'TechnoPlus Sénégal', '15 rue des Technologies, Dakar', '10000', 'Dakar', 'Sénégal', '771234567', 'contact@technoplus.sn', 4, 3, 4, 4),
('f2222222-2222-2222-2222-222222222222', 'F002', 'Mecanique Générale SARL', '22 avenue de l''Industrie, Thiès', '20000', 'Thiès', 'Sénégal', '772345678', 'info@mecgen.sn', 5, 4, 3, 5),
('f3333333-3333-3333-3333-333333333333', 'F003', 'Bureau Plus SA', '35 boulevard de la République, Dakar', '10000', 'Dakar', 'Sénégal', '773456789', 'contact@bureauplus.sn', 3, 5, 4, 3),
('f4444444-4444-4444-4444-444444444444', 'F004', 'Informatique Solutions', '12 rue de l''Innovation, Dakar', '10000', 'Dakar', 'Sénégal', '774567890', 'contact@infosolutions.sn', 4, 4, 3, 4),
('f5555555-5555-5555-5555-555555555555', 'F005', 'Matières Premières SA', 'Zone industrielle, Rufisque', '30000', 'Rufisque', 'Sénégal', '775678901', 'info@matprem.sn', 3, 3, 5, 3);

-- 5.4 Produits
INSERT INTO produits (id, code, nom, description, categorie_id, unite_mesure, prix_reference_ht, seuil_alerte, stock_minimum, stock_maximum, stock_actuel) VALUES
('p1111111-1111-1111-1111-111111111111', 'PROD-001', 'Carte électronique XT-2000', 'Carte mère haute performance', 'c1111111-1111-1111-1111-111111111111', 'pièce', 150000, 10, 5, 100, 25),
('p2222222-2222-2222-2222-222222222222', 'PROD-002', 'Moteur électrique 2kW', 'Moteur AC triphasé', 'c2222222-2222-2222-2222-222222222222', 'unité', 320000, 5, 3, 20, 8),
('p3333333-3333-3333-3333-333333333333', 'PROD-003', 'Cartouche encre HP-405X', 'Cartouche d''encre haute capacité', 'c3333333-3333-3333-3333-333333333333', 'cartouche', 85000, 20, 10, 100, 35),
('p4444444-4444-4444-4444-444444444444', 'PROD-004', 'Ordinateur portable ProBook G9', 'Laptop professionnel 16Go RAM', 'c4444444-4444-4444-4444-444444444444', 'unité', 1200000, 3, 2, 15, 5),
('p5555555-5555-5555-5555-555555555555', 'PROD-005', 'Polymère ABS', 'Matière première pour impression 3D', 'c5555555-5555-5555-5555-555555555555', 'kg', 25000, 50, 20, 200, 80);

-- 5.5 Fournisseurs-Produits
INSERT INTO fournisseurs_produits (id, fournisseur_id, produit_id, prix_achat_ht, delai_livraison_jours, est_prioritaire, reference_fournisseur) VALUES
('fp111111-1111-1111-1111-111111111111', 'f1111111-1111-1111-1111-111111111111', 'p1111111-1111-1111-1111-111111111111', 135000, 5, 1, 'REF-XT2000'),
('fp222222-2222-2222-2222-222222222222', 'f2222222-2222-2222-2222-222222222222', 'p2222222-2222-2222-2222-222222222222', 295000, 10, 1, 'MOT-2K'),
('fp333333-3333-3333-3333-333333333333', 'f3333333-3333-3333-3333-333333333333', 'p3333333-3333-3333-3333-333333333333', 78000, 3, 1, 'HP405X'),
('fp444444-4444-4444-4444-444444444444', 'f4444444-4444-4444-4444-444444444444', 'p4444444-4444-4444-4444-444444444444', 1100000, 7, 1, 'PB-G9'),
('fp555555-5555-5555-5555-555555555555', 'f5555555-5555-5555-5555-555555555555', 'p5555555-5555-5555-5555-555555555555', 22000, 15, 1, 'ABS-BLK');

-- 5.6 Demandes d'achat
INSERT INTO demandes_achat (id, numero, titre, description, demandeur_id, service, date_demande, date_souhaitee, statut, type_approvisionnement, budget_estime_ht) VALUES
('d1111111-1111-1111-1111-111111111111', 'DA-2025-001', 'Commande cartouches imprimantes', 'Besoin de cartouches pour le service RH', 'u2222222-2222-2222-2222-222222222222', 'RH', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'validee', 'stock', 850000),
('d2222222-2222-2222-2222-222222222222', 'DA-2025-002', 'Moteur électrique atelier', 'Remplacement moteur ligne de production', 'u2222222-2222-2222-2222-222222222222', 'Production', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'en_validation', 'urgent', 320000);

-- 5.7 Lignes de demande
INSERT INTO demandes_achat_lignes (id, demande_achat_id, produit_id, quantite, unite_mesure, prix_unitaire_estime_ht, total_estime_ht) VALUES
('l1111111-1111-1111-1111-111111111111', 'd1111111-1111-1111-1111-111111111111', 'p3333333-3333-3333-3333-333333333333', 10, 'cartouche', 85000, 850000),
('l2222222-2222-2222-2222-222222222222', 'd2222222-2222-2222-2222-222222222222', 'p2222222-2222-2222-2222-222222222222', 1, 'unité', 320000, 320000);

-- 5.8 Bons de commande
INSERT INTO bons_commande (id, numero, fournisseur_id, demande_achat_id, date_emission, date_livraison_prevue, statut, total_ht, total_tva, total_ttc) VALUES
('b1111111-1111-1111-1111-111111111111', 'BC-2025-001', 'f3333333-3333-3333-3333-333333333333', 'd1111111-1111-1111-1111-111111111111', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'envoye', 780000, 140400, 920400);

-- 5.9 Lignes de bon de commande
INSERT INTO bons_commande_lignes (id, bon_commande_id, produit_id, quantite, quantite_recue, prix_unitaire_ht, total_ht, taux_tva, total_tva, total_ttc) VALUES
('bl111111-1111-1111-1111-111111111111', 'b1111111-1111-1111-1111-111111111111', 'p3333333-3333-3333-3333-333333333333', 10, 0, 78000, 780000, 18, 140400, 920400);

-- 5.10 Factures
INSERT INTO factures (id, numero, fournisseur_id, bon_commande_id, date_emission, date_echeance, total_ht, total_tva, total_ttc, statut) VALUES
('fact1-1111-1111-1111-111111111111', 'FACT-2025-001', 'f3333333-3333-3333-3333-333333333333', 'b1111111-1111-1111-1111-111111111111', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 780000, 140400, 920400, 'en_attente');

-- 5.11 Évaluations fournisseurs
INSERT INTO evaluations_fournisseurs (id, fournisseur_id, note_qualite, note_delai, note_conformite, note_communication) VALUES
('ev1-1111-1111-1111-111111111111', 'f1111111-1111-1111-1111-111111111111', 4, 3, 4, 4),
('ev2-2222-2222-2222-222222222222', 'f2222222-2222-2222-2222-222222222222', 5, 4, 3, 5);

-- ============================================================
-- FIN DU SCRIPT
-- ============================================================