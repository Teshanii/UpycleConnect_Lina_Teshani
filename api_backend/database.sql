
-- 1. POLE SECURITE & ACTEURS
CREATE TABLE roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    libelle_role VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE utilisateurs (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL, -- Sera haché avec bcrypt en Go
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    score_upcycling INT DEFAULT 0, -- Pour les particuliers
    onesignal_player_id VARCHAR(255), -- ID technique pour les notifications push
    est_actif TINYINT DEFAULT 1,
    id_role INT NOT NULL,
    FOREIGN KEY (id_role) REFERENCES roles(id_role)
) ENGINE=InnoDB;

-- 2. POLE INTERNATIONALISATION
CREATE TABLE langues (
    id_langue INT AUTO_INCREMENT PRIMARY KEY,
    code_iso VARCHAR(5) NOT NULL UNIQUE, 
    nom_langue VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE categories (
    id_cat INT AUTO_INCREMENT PRIMARY KEY,
    code_ref_cat VARCHAR(50) NOT NULL UNIQUE 
) ENGINE=InnoDB;

CREATE TABLE traductions_categories (
    id_cat INT NOT NULL,
    id_langue INT NOT NULL,
    libelle_traduit VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_cat, id_langue),
    FOREIGN KEY (id_cat) REFERENCES categories(id_cat) ON DELETE CASCADE,
    FOREIGN KEY (id_langue) REFERENCES langues(id_langue) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. POLE LOGISTIQUE (OBJETS & DEPOTS)
CREATE TABLE objets (
    id_objet INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT,
    etat VARCHAR(50),
    poids_estime DECIMAL(10,2),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_cat INT,
    FOREIGN KEY (id_cat) REFERENCES categories(id_cat)
) ENGINE=InnoDB;

CREATE TABLE annonces (
    id_annonce INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    statut_validation TINYINT DEFAULT 0,
    id_user_auteur INT NOT NULL,
    description TEXT,
    categorie VARCHAR(50),
    type_annonce VARCHAR(10) DEFAULT 'don',
    prix DECIMAL(10,2) DEFAULT 0.00,
    statut_annonce VARCHAR(20) DEFAULT 'disponible',
    photo VARCHAR(255),
    FOREIGN KEY (id_user_auteur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

CREATE TABLE box (
    id_box INT AUTO_INCREMENT PRIMARY KEY,
    adresse VARCHAR(255) NOT NULL,
    capacite_max INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE demandes_depot (
    id_demande INT AUTO_INCREMENT PRIMARY KEY,
    statut_check VARCHAR(50) DEFAULT 'en_attente',
    code_ouverture VARCHAR(10), 
    code_barre_scan VARCHAR(100),
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_user INT NOT NULL,
    id_objet INT NOT NULL,
    id_box INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user),
    FOREIGN KEY (id_objet) REFERENCES objets(id_objet),
    FOREIGN KEY (id_box) REFERENCES box(id_box)
) ENGINE=InnoDB;

-- 4. POLE COMMUNAUTE & PROJETS
CREATE TABLE projets (
    id_projet INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description_generale TEXT,
    est_sponsorise TINYINT DEFAULT 0, -- [cite: 473]
    id_createur INT NOT NULL,
    FOREIGN KEY (id_createur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

-- Ajout des étapes pour corriger le MCD
CREATE TABLE etapes_projet (
    id_etape INT AUTO_INCREMENT PRIMARY KEY,
    titre_etape VARCHAR(150),
    description_etape TEXT,
    image_etape VARCHAR(255),
    ordre INT,
    id_projet INT NOT NULL,
    FOREIGN KEY (id_projet) REFERENCES projets(id_projet) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE evenements (
    id_event INT AUTO_INCREMENT PRIMARY KEY,
    type_event VARCHAR(50), 
    prix_actuel DECIMAL(10,2) NOT NULL,
    places_max INT NOT NULL,
    statut_validation TINYINT DEFAULT 0,
    id_animateur INT NOT NULL,
    FOREIGN KEY (id_animateur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

-- 5. POLE FINANCE
CREATE TABLE types_abonnements (
    id_type_abo INT AUTO_INCREMENT PRIMARY KEY,
    nom_offre VARCHAR(100) NOT NULL,
    prix_mensuel_actuel DECIMAL(10, 2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE transactions (
    id_transac INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(10, 2) NOT NULL,
    reference_stripe VARCHAR(255), -- [cite: 277]
    statut_paiement VARCHAR(50),
    date_transac DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_user INT,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

-- 6. NOTIFICATIONS 
CREATE TABLE notifications (
    id_notif INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150),
    message TEXT,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE recoit_notif (
    id_user INT,
    id_notif INT,
    est_lue TINYINT DEFAULT 0,
    PRIMARY KEY (id_user, id_notif),
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user),
    FOREIGN KEY (id_notif) REFERENCES notifications(id_notif)
) ENGINE=InnoDB;


CREATE TABLE article_conseil (
    id_article INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    type VARCHAR(50),
    id_auteur INT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE inscriptions (
    id_inscription INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_event INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- INSERTIONS INITIALES
INSERT INTO roles (libelle_role) VALUES ('Admin'), ('Salarie'), ('Professionnel et Artisan'), ('Particulier');
INSERT INTO langues (code_iso, nom_langue) VALUES ('fr', 'Français'), ('en', 'English');
INSERT INTO types_abonnements (nom_offre, prix_mensuel_actuel) VALUES ('Gratuit', 0.00), ('Premium Artisan', 29.99);
INSERT INTO evenements (titre, date_debut, prix_actuel, places_max, statut_validation, id_animateur) 
VALUES ('Atelier Palette', '2026-04-10 14:00:00', 15.00, 10, 1, 2);