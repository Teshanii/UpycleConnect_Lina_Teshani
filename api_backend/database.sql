
CREATE TABLE roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    libelle_role VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE utilisateurs (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    est_actif TINYINT DEFAULT 1, -- 1=Actif, 0=Banni
    id_role INT NOT NULL,
    FOREIGN KEY (id_role) REFERENCES roles(id_role)
) ENGINE=InnoDB;


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

CREATE TABLE traductions_interface (
    cle_technique VARCHAR(100) NOT NULL, 
    id_langue INT NOT NULL,
    texte_traduit TEXT NOT NULL,
    PRIMARY KEY (cle_technique, id_langue),
    FOREIGN KEY (id_langue) REFERENCES langues(id_langue) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE prestations (
    id_prestation INT AUTO_INCREMENT PRIMARY KEY,
    nom_prestation VARCHAR(100) NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

CREATE TABLE evenements (
    id_event INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    date_debut DATETIME NOT NULL,
    prix_actuel DECIMAL(10, 2) NOT NULL,
    places_max INT NOT NULL,
    statut_validation TINYINT DEFAULT 0, -- 0=Attente, 1=Validé
    id_animateur INT NOT NULL,
    FOREIGN KEY (id_animateur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

CREATE TABLE annonces (
    id_annonce INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    statut_validation TINYINT DEFAULT 0, -- Validation administrative
    id_user_auteur INT NOT NULL,
    FOREIGN KEY (id_user_auteur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;



CREATE TABLE box (
    id_box INT AUTO_INCREMENT PRIMARY KEY,
    adresse VARCHAR(255) NOT NULL,
    capacite_max INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE types_abonnements (
    id_type_abo INT AUTO_INCREMENT PRIMARY KEY,
    nom_offre VARCHAR(100) NOT NULL,
    prix_mensuel_actuel DECIMAL(10, 2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE transactions (
    id_transac INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(10, 2) NOT NULL,
    type_transac ENUM('Revenu', 'Charge') NOT NULL,
    date_transac DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_user INT,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;


CREATE TABLE forum_threads (
    id_thread INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE message_forums (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    est_modere TINYINT DEFAULT 0, -- 1=Masqué
    id_user_auteur INT NOT NULL,
    id_thread INT NOT NULL,
    FOREIGN KEY (id_user_auteur) REFERENCES utilisateurs(id_user),
    FOREIGN KEY (id_thread) REFERENCES forum_threads(id_thread)
) ENGINE=InnoDB;


INSERT INTO roles (libelle_role) VALUES ('Admin'), ('Salarie'), ('Professionnel et Artisan'), ('Particulier');


INSERT INTO langues (code_iso, nom_langue) VALUES ('fr', 'Français'), ('en', 'English');


INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, id_role) VALUES 
('Fernando', 'Teshani', 't.fernando@myskolae.fr', 'admin123', 1), -- Admin
('Chellala', 'Lina', 'l.chellala@myskolae.fr', 'salarie123', 2), -- Salarié
('Jean', 'Gabrielle', 'gabrielle.jean@gmail.com', 'pro123', 3), -- Pro
('Bryant', 'Chris', 'chris.bryant@gmail.com', 'client123', 4); -- Particulier


INSERT INTO categories (code_ref_cat) VALUES ('MOBILIER');
INSERT INTO traductions_categories (id_cat, id_langue, libelle_traduit) VALUES 
(1, 1, 'Mobilier'), 
(1, 2, 'Furniture');


INSERT INTO evenements (titre, date_debut, prix_actuel, places_max, statut_validation, id_animateur) 
VALUES ('Atelier Palette', '2026-04-10 14:00:00', 15.00, 10, 0, 2);


INSERT INTO annonces (titre, statut_validation, id_user_auteur) 
VALUES ('Donne table basse', 0, 4);


INSERT INTO box (adresse, capacite_max) VALUES ('12 rue de Opéra, Paris', 50);
INSERT INTO types_abonnements (nom_offre, prix_mensuel_actuel) VALUES ('Premium Artisan', 29.99);
INSERT INTO transactions (montant, type_transac, id_user) VALUES (29.99, 'Revenu', 3);