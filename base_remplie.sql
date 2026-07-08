


SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- On repart d'une base propre au cas ou les tables existent deja
DROP TABLE IF EXISTS recoit_notif, inscriptions, documents, article_conseil, notifications,
  message_forums, mouvements_portefeuille, transactions, types_abonnements, evenements,
  etapes_projet, participants_projet, projets, prestations, demandes_depot, casiers, box,
  annonces, objets, traductions, categories, langues, utilisateurs, roles;

SET FOREIGN_KEY_CHECKS = 1;


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
    score_upcycling INT DEFAULT 0,
    abonnement VARCHAR(20) DEFAULT 'gratuit',
    date_fin_abonnement DATETIME NULL,
    abonnement_annule TINYINT DEFAULT 0,
    solde DECIMAL(10,2) DEFAULT 0,
    onesignal_player_id VARCHAR(255),
    est_actif TINYINT DEFAULT 1,
    est_verifie TINYINT DEFAULT 1,
    token_verification VARCHAR(64) NULL,
    reset_token VARCHAR(64) NULL,
    reset_token_expiry DATETIME NULL,
    id_role INT NOT NULL,
    recompense_reclamee TINYINT DEFAULT 0,
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

CREATE TABLE traductions (
    id_traduction INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL,
    id_langue INT NOT NULL,
    texte TEXT NOT NULL,
    UNIQUE KEY (cle, id_langue),
    FOREIGN KEY (id_langue) REFERENCES langues(id_langue) ON DELETE CASCADE
) ENGINE=InnoDB;


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
    motif_refus VARCHAR(255),
    photo VARCHAR(255),
    FOREIGN KEY (id_user_auteur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

CREATE TABLE box (
    id_box INT AUTO_INCREMENT PRIMARY KEY,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(100),
    capacite_max INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE casiers (
    id_casier INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL,
    statut VARCHAR(20) DEFAULT 'libre', -- libre / occupe
    id_box INT NOT NULL,
    FOREIGN KEY (id_box) REFERENCES box(id_box) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE demandes_depot (
    id_demande INT AUTO_INCREMENT PRIMARY KEY,
    statut_check VARCHAR(50) DEFAULT 'en_attente',
    code_ouverture VARCHAR(10),
    code_barre_scan VARCHAR(100),
    code_artisan VARCHAR(10),
    motif_refus VARCHAR(255),
    id_casier INT,
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_user INT NOT NULL,
    id_objet INT NOT NULL,
    id_box INT NOT NULL,
    id_artisan INT NULL,
    id_annonce INT NULL,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user),
    FOREIGN KEY (id_objet) REFERENCES objets(id_objet),
    FOREIGN KEY (id_box) REFERENCES box(id_box),
    FOREIGN KEY (id_casier) REFERENCES casiers(id_casier),
    FOREIGN KEY (id_annonce) REFERENCES annonces(id_annonce)
) ENGINE=InnoDB;

CREATE TABLE prestations (
    id_prestation INT AUTO_INCREMENT PRIMARY KEY,
    nom_prestation VARCHAR(150) NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    description TEXT,
    photo VARCHAR(255),
    id_createur INT,
    statut_validation TINYINT DEFAULT 0,
    motif_refus VARCHAR(255),
    vendu TINYINT DEFAULT 0,
    FOREIGN KEY (id_createur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

CREATE TABLE projets (
    id_projet INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description_generale TEXT,
    adresse VARCHAR(255),
    ville VARCHAR(100),
    statut VARCHAR(20) DEFAULT 'en_cours',
    photo_couverture VARCHAR(255),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_debut DATETIME NULL,
    date_fin DATETIME NULL,
    ouvert_participation TINYINT DEFAULT 1,
    est_sponsorise TINYINT DEFAULT 0,
    date_fin_sponsoring DATETIME NULL,
    id_createur INT NOT NULL,
    FOREIGN KEY (id_createur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

CREATE TABLE participants_projet (
    id_participation INT AUTO_INCREMENT PRIMARY KEY,
    id_projet INT NOT NULL,
    id_user INT NOT NULL,
    tache VARCHAR(255),
    statut VARCHAR(20) DEFAULT 'en_attente',
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_projet) REFERENCES projets(id_projet) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;


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
    titre VARCHAR(150),
    type_event VARCHAR(50),
    lieu VARCHAR(150),
    description TEXT,
    date_debut DATETIME,
    date_fin DATETIME,
    prix_actuel DECIMAL(10,2) NOT NULL,
    places_max INT NOT NULL,
    statut_validation TINYINT DEFAULT 0,
    motif_refus VARCHAR(255),
    id_animateur INT NOT NULL,
    FOREIGN KEY (id_animateur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;


CREATE TABLE types_abonnements (
    id_type_abo INT AUTO_INCREMENT PRIMARY KEY,
    nom_offre VARCHAR(100) NOT NULL,
    prix_mensuel_actuel DECIMAL(10, 2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE transactions (
    id_transac INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(10, 2) NOT NULL,
    reference_stripe VARCHAR(255),
    statut_paiement VARCHAR(50),
    date_transac DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_user INT,
    type VARCHAR(50) DEFAULT 'atelier',
    commission DECIMAL(10,2) DEFAULT 0,
    id_event INT NULL,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

CREATE TABLE mouvements_portefeuille (
    id_mouvement INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    type VARCHAR(30) NOT NULL,
    description VARCHAR(255),
    date_mouvement DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE message_forums (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    id_user_auteur INT NOT NULL,
    date_message DATETIME DEFAULT CURRENT_TIMESTAMP,
    est_modere TINYINT DEFAULT 0,
    categorie VARCHAR(50) DEFAULT 'Général',
    epingle TINYINT DEFAULT 0,
    titre VARCHAR(255),
    id_message_parent INT NULL,
    FOREIGN KEY (id_user_auteur) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;


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
    statut_validation TINYINT DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE documents (
    id_document INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    type VARCHAR(50),
    nom_fichier VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

CREATE TABLE inscriptions (
    id_inscription INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_event INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    present TINYINT DEFAULT 0
) ENGINE=InnoDB;

-- INSERTIONS INITIALES
INSERT INTO roles (libelle_role) VALUES ('Admin'), ('Salarie'), ('Professionnel et Artisan'), ('Particulier');
INSERT INTO langues (code_iso, nom_langue) VALUES ('fr', 'Français'), ('en', 'English');
INSERT INTO types_abonnements (nom_offre, prix_mensuel_actuel) VALUES ('Gratuit', 0.00), ('Premium Artisan', 29.99);

INSERT INTO `utilisateurs` (`id_user`, `nom`, `prenom`, `email`, `mot_de_passe`, `date_inscription`, `score_upcycling`, `abonnement`, `date_fin_abonnement`, `abonnement_annule`, `solde`, `onesignal_player_id`, `est_actif`, `est_verifie`, `token_verification`, `reset_token`, `reset_token_expiry`, `id_role`, `recompense_reclamee`) VALUES (1,'Fernando','Teshani','t.fernando@myskolae.fr','$2a$10$bOv16dQsnhjpBGzuXRFxo./DAqnRr6n29.4VZsM2lU/afiXuQl4Oa','2026-07-02 12:40:51',0,'gratuit',NULL,0,0.00,NULL,1,1,NULL,NULL,NULL,2,0);
INSERT INTO `utilisateurs` (`id_user`, `nom`, `prenom`, `email`, `mot_de_passe`, `date_inscription`, `score_upcycling`, `abonnement`, `date_fin_abonnement`, `abonnement_annule`, `solde`, `onesignal_player_id`, `est_actif`, `est_verifie`, `token_verification`, `reset_token`, `reset_token_expiry`, `id_role`, `recompense_reclamee`) VALUES (2,'Admin','Teshani','teshanifernandotf@gmail.com','$2a$10$PstdXoQPif2pvdxkyY2UH.C6F0J.d7533b1WC2iaM4MO108FADTry','2026-07-02 12:47:21',0,'gratuit',NULL,0,0.00,NULL,1,1,NULL,NULL,NULL,1,0);
INSERT INTO `utilisateurs` (`id_user`, `nom`, `prenom`, `email`, `mot_de_passe`, `date_inscription`, `score_upcycling`, `abonnement`, `date_fin_abonnement`, `abonnement_annule`, `solde`, `onesignal_player_id`, `est_actif`, `est_verifie`, `token_verification`, `reset_token`, `reset_token_expiry`, `id_role`, `recompense_reclamee`) VALUES (3,'Particulier','Teshani','teshanifernando@outlook.com','$2a$10$qHw1LiherzotE1UGD/VE4elP13Sx7GQCuS.Okf0WPH5KYYaajskRi','2026-07-02 12:52:30',10,'gratuit',NULL,0,18.60,NULL,1,1,NULL,NULL,NULL,4,0);
INSERT INTO `utilisateurs` (`id_user`, `nom`, `prenom`, `email`, `mot_de_passe`, `date_inscription`, `score_upcycling`, `abonnement`, `date_fin_abonnement`, `abonnement_annule`, `solde`, `onesignal_player_id`, `est_actif`, `est_verifie`, `token_verification`, `reset_token`, `reset_token_expiry`, `id_role`, `recompense_reclamee`) VALUES (4,'Artisan','Teshani','tfernando6@myges.fr','$2a$10$vtqTIWT90MtA0piCF2DO.uECl2bny8cSaZS./ta3Cnus45CAsKSVC','2026-07-03 05:35:47',5,'gratuit',NULL,0,0.00,NULL,1,1,NULL,NULL,NULL,3,0);


--  DONNEES DE DEMONSTRATION

-- --- Utilisateurs supplementaires (les comptes 1 a 4 sont deja crees plus haut) ---
-- Roles : 1=Admin, 2=Salarie, 3=Professionnel/Artisan, 4=Particulier
INSERT INTO utilisateurs (id_user, nom, prenom, email, mot_de_passe, score_upcycling, abonnement, solde, est_actif, est_verifie, id_role) VALUES
-- Salaries (animateurs / formateurs)
(5,'Mercier','Paul','paul.mercier@upcycle.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',0,'gratuit',0.00,1,1,2),
(6,'Faure','Lea','lea.faure@upcycle.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',0,'gratuit',0.00,1,1,2),
-- Professionnels / Artisans
(7,'Leroy','Thomas','thomas.leroy@boiscreatif.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',0,'premium',72.00,1,1,3),
(8,'Haddad','Nadia','nadia.haddad@metalrecup.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',0,'premium',89.90,1,1,3),
(9,'Rousseau','Ines','ines.rousseau@studiotextile.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',0,'gratuit',0.00,1,1,3),
(10,'Dubois','Marc','marc.dubois@paletteandco.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',0,'premium',415.00,1,1,3),
(11,'Roy','Camille','camille.roy@versverre.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',0,'gratuit',16.20,1,1,3),
-- Particuliers
(12,'Dubois','Marie','marie.dubois@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',45,'gratuit',12.00,1,1,4),
(13,'Martin','Lucas','lucas.martin@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',20,'gratuit',0.00,1,1,4),
(14,'Bernard','Sophie','sophie.bernard@outlook.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',60,'gratuit',5.50,1,1,4),
(15,'Benali','Karim','karim.benali@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',15,'gratuit',0.00,1,1,4),
(16,'Petit','Emma','emma.petit@yahoo.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',30,'gratuit',0.00,1,1,4),
(17,'Moreau','Julien','julien.moreau@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',8,'gratuit',0.00,1,1,4),
(18,'Girard','Chloe','chloe.girard@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',75,'gratuit',22.30,1,1,4),
(19,'Lefevre','Antoine','antoine.lefevre@free.fr','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',5,'gratuit',0.00,1,1,4),
(20,'Simon','Nathalie','nathalie.simon@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',50,'gratuit',0.00,1,1,4),
(21,'Fontaine','Hugo','hugo.fontaine@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',12,'gratuit',0.00,1,1,4),
(22,'Garnier','Manon','manon.garnier@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',40,'gratuit',0.00,1,1,4),
-- Compte pas encore active par email (est_verifie = 0)
(23,'Chevalier','Yanis','yanis.chevalier@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',0,'gratuit',0.00,1,0,4),
-- Compte banni par l'administration (est_actif = 0)
(24,'Blanc','Leo','leo.blanc@gmail.com','$2b$10$YPmZNewvrHbHiBC9uUcAXuJ68419fVDfz8.Q1VwNrloHLHC9eovqa',0,'gratuit',0.00,0,1,4);

-- --- Categories de materiaux (pour les objets) ---
INSERT INTO categories (id_cat, code_ref_cat) VALUES
(1,'BOIS'),(2,'TEXTILE'),(3,'METAL'),(4,'PLASTIQUE'),(5,'VERRE'),(6,'PALETTE'),(7,'ELECTRONIQUE'),(8,'MEUBLE');

-- --- Traductions (multilingue : francais id_langue=1, anglais id_langue=2) ---
INSERT INTO traductions (cle, id_langue, texte) VALUES
('accueil',1,'Accueil'),('accueil',2,'Home'),
('connexion',1,'Connexion'),('connexion',2,'Login'),
('deconnexion',1,'Deconnexion'),('deconnexion',2,'Logout'),
('annonces',1,'Annonces'),('annonces',2,'Listings'),
('mon_score',1,'Mon score'),('mon_score',2,'My score'),
('forum',1,'Forum'),('forum',2,'Forum'),
('conseils',1,'Conseils'),('conseils',2,'Tips');

-- --- Objets deposes (references par les demandes de depot) ---
INSERT INTO objets (id_objet, description, etat, poids_estime, id_cat) VALUES
(1,'Vieille chaise en bois a renover','bon',4.50,1),
(2,'Lot de chutes de tissu coton','neuf',1.20,2),
(3,'Barres metalliques usagees','usage',8.00,3),
(4,'Lot de 6 bocaux en verre','bon',3.00,5),
(5,'Palette EUR standard','usage',20.00,6),
(6,'Ancien meuble a tiroirs','a renover',15.00,8),
(7,'Cables electroniques divers','usage',2.00,7),
(8,'Bidons plastique alimentaires','bon',1.00,4);

-- --- Annonces (statut_validation : 0=en attente, 1=validee, 2=refusee) ---
INSERT INTO annonces (id_annonce, titre, statut_validation, id_user_auteur, description, categorie, type_annonce, prix, statut_annonce, motif_refus, photo) VALUES
(1,'Chaise en bois a renover',1,12,'Chaise ancienne, structure solide, a poncer et repeindre.','BOIS','don',0.00,'disponible',NULL,NULL),
(2,'Chutes de tissu coton',1,14,'Plusieurs metres de chutes, ideal couture et patchwork.','TEXTILE','don',0.00,'disponible',NULL,NULL),
(3,'Lot de bocaux en verre',1,3,'6 bocaux propres, parfaits pour rangement ou deco.','VERRE','vente',8.00,'disponible',NULL,NULL),
(4,'Barres metalliques',1,16,'Barres en acier, ideales pour structure ou sculpture.','METAL','vente',15.00,'vendu',NULL,NULL),
(5,'Palette bois EUR',1,18,'Palette en bon etat, recuperable pour meuble.','PALETTE','don',0.00,'disponible',NULL,NULL),
(6,'Vieux meuble a tiroirs',0,13,'Commode ancienne a renover, quelques tiroirs a fixer.','MEUBLE','don',0.00,'disponible',NULL,NULL),
(7,'Cables electroniques',0,15,'Divers cables et fils, a trier pour recuperation.','ELECTRONIQUE','don',0.00,'disponible',NULL,NULL),
(8,'Bidons plastique alimentaires',2,20,'Anciens bidons alimentaires.','PLASTIQUE','don',0.00,'disponible','Objet non conforme a la charte (contenants alimentaires usages).',NULL),
(9,'Ancienne fenetre bois et verre',1,22,'Fenetre ancienne avec cadre bois, verre intact.','BOIS','vente',25.00,'disponible',NULL,NULL),
(10,'Tissus d''ameublement',1,17,'Rouleaux de tissu epais pour projets deco.','TEXTILE','don',0.00,'disponible',NULL,NULL),
(11,'Cagettes en bois',1,21,'Lot de cagettes, parfaites pour etageres ou jardin.','BOIS','don',0.00,'disponible',NULL,NULL),
(12,'Plaques de metal rouillees',2,19,'Plaques metalliques.','METAL','don',0.00,'disponible','Photos trop floues, merci de reposter avec des images nettes.',NULL);

-- --- Box de depot (conteneurs) ---
INSERT INTO box (id_box, adresse, ville, capacite_max) VALUES
(1,'174 rue La Fayette','Paris 10',20),
(2,'12 rue du Faubourg','Paris 11',12),
(3,'8 avenue d''Italie','Paris 13',12),
(4,'25 rue de Paris','Montreuil',30);

-- --- Casiers (libre / occupe) ---
INSERT INTO casiers (id_casier, numero, statut, id_box) VALUES
(1,'C01','occupe',1),(2,'C02','libre',1),(3,'C03','libre',1),
(4,'C01','occupe',2),(5,'C02','libre',2),
(6,'C01','libre',3),(7,'C02','occupe',3),
(8,'C01','occupe',4),(9,'C02','occupe',4),(10,'C03','libre',4);

-- --- Demandes de depot (statut_check : en_attente / valide / depose / recupere / refuse) ---
INSERT INTO demandes_depot (id_demande, statut_check, code_ouverture, code_barre_scan, code_artisan, motif_refus, id_casier, id_user, id_objet, id_box, id_artisan, id_annonce) VALUES
(1,'valide','4821','UPC-0001',NULL,NULL,1,12,1,1,NULL,1),
(2,'depose','7364','UPC-0002',NULL,NULL,4,14,2,2,NULL,2),
(3,'recupere','1195','UPC-0003','9027',NULL,8,16,3,4,7,4),
(4,'en_attente',NULL,NULL,NULL,NULL,NULL,13,6,3,NULL,6),
(5,'refuse',NULL,NULL,NULL,'Objet trop volumineux pour nos casiers.',NULL,18,5,2,NULL,5),
(6,'depose','5540','UPC-0006',NULL,NULL,7,22,4,3,NULL,3);

-- --- Prestations des artisans (0=attente, 1=validee, 2=refusee ; vendu 0/1) ---
INSERT INTO prestations (id_prestation, nom_prestation, prix, description, id_createur, statut_validation, motif_refus, vendu) VALUES
(1,'Reparation de meubles en bois',45.00,'Remise en etat de meubles anciens, ponçage et finition.',7,1,NULL,0),
(2,'Luminaire en metal de recuperation',80.00,'Suspension design fabriquee a partir de metal recycle.',8,1,NULL,1),
(3,'Coussins en tissu upcycle',25.00,'Coussins fabriques a partir de chutes de tissu.',9,1,NULL,0),
(4,'Table basse en palette',120.00,'Table basse sur mesure en bois de palette poncee.',10,1,NULL,0),
(5,'Vase en verre recycle',18.00,'Vase decoratif realise a partir de verre recupere.',11,1,NULL,1),
(6,'Atelier reparation velo',60.00,'Prestation de reparation et entretien de velo.',7,0,NULL,0),
(7,'Sculpture metal sur mesure',200.00,'Sculpture personnalisee en metal recycle.',8,2,'Prix incoherent avec la description, merci de preciser.',0),
(8,'Etagere en bois massif',95.00,'Etagere robuste fabriquee en bois de recuperation.',4,1,NULL,0);

-- --- Projets d'upcycling (statut : en_cours / termine) ---
INSERT INTO projets (id_projet, titre, description_generale, adresse, ville, statut, ouvert_participation, est_sponsorise, id_createur) VALUES
(1,'Renovation d''un banc public','Remise a neuf d''un banc de quartier en bois recycle.','Place de la Republique','Paris 11','en_cours',1,0,12),
(2,'Fresque murale en materiaux recuperes','Creation d''une fresque collective a partir de dechets.','Rue de Paris','Montreuil','en_cours',1,1,7),
(3,'Mobilier pour jardin partage','Fabrication de bancs et bacs pour un jardin partage.','Square Vitruve','Paris 11','termine',0,0,18),
(4,'Atelier velo solidaire','Reconditionnement de velos pour les habitants.','Avenue d''Italie','Paris 13','en_cours',1,0,4),
(5,'Cabane pour enfants en palettes','Construction d''une cabane a partir de palettes.','Jardin communal','Paris 13','en_cours',1,0,14);

-- --- Etapes de projets ---
INSERT INTO etapes_projet (titre_etape, description_etape, ordre, id_projet) VALUES
('Demontage','Demontage complet de l''ancien banc et tri des pieces.',1,1),
('Ponçage','Ponçage des planches recuperees.',2,1),
('Peinture','Application d''une peinture ecologique.',3,1),
('Collecte des palettes','Recuperation des palettes aupres des commerces.',1,3),
('Assemblage','Assemblage des bancs et bacs.',2,3);

-- --- Participants aux projets (statut : en_attente / accepte / refuse) ---
INSERT INTO participants_projet (id_projet, id_user, tache, statut) VALUES
(1,13,'Ponçage des planches','accepte'),
(1,15,'Aide a la peinture','en_attente'),
(2,16,'Peinture de la fresque','accepte'),
(2,17,'Collecte de materiaux','refuse'),
(4,18,'Mecanique velo','accepte'),
(5,21,'Assemblage de la cabane','en_attente');

-- --- Evenements : ateliers, formations, conferences (0=attente, 1=valide, 2=refuse) ---
-- Dates 2026 : certains passes (pour les presences), d'autres a venir
INSERT INTO evenements (id_event, titre, type_event, lieu, description, date_debut, date_fin, prix_actuel, places_max, statut_validation, motif_refus, id_animateur) VALUES
(1,'Atelier recup bois debutant','atelier','Paris 10','Initiation a la recuperation et au travail du bois.','2026-08-15 14:00:00','2026-08-15 17:00:00',20.00,15,1,NULL,5),
(2,'Formation upcycling textile','formation','Paris 10','Apprendre a transformer les textiles usages.','2026-09-02 09:00:00','2026-09-02 12:00:00',50.00,12,1,NULL,6),
(3,'Conference economie circulaire','conference','Paris 10','Comprendre les enjeux de l''economie circulaire.','2026-08-20 18:00:00','2026-08-20 20:00:00',0.00,40,1,NULL,1),
(4,'Atelier creation luminaire','atelier','Montreuil','Fabriquer un luminaire a partir de metal recycle.','2026-06-10 14:00:00','2026-06-10 17:00:00',30.00,10,1,NULL,5),
(5,'Atelier peinture ecolo','atelier','Paris 11','Decouvrir les peintures naturelles.','2026-09-18 14:00:00','2026-09-18 16:00:00',25.00,20,0,NULL,6),
(6,'Formation soudure metal','formation','Montreuil','Bases de la soudure pour la recup metal.','2026-08-05 09:00:00','2026-08-05 13:00:00',70.00,8,2,'Creneau deja occupe, merci de proposer une autre date.',5),
(7,'Atelier couture zero dechet','atelier','Paris 10','Coudre ses accessoires zero dechet.','2026-09-25 14:00:00','2026-09-25 17:00:00',15.00,18,1,NULL,6),
(8,'Atelier reparation meubles','atelier','Paris 10','Reparer et renover un meuble ancien.','2026-06-01 14:00:00','2026-06-01 17:00:00',35.00,8,1,NULL,1);

-- --- Inscriptions aux evenements (present=1 pour les evenements passes suivis) ---
INSERT INTO inscriptions (id_user, id_event, present) VALUES
(12,1,0),(13,1,0),(16,1,0),
(14,2,0),(18,2,0),
(15,3,0),(17,3,0),(20,3,0),
(12,4,1),(18,4,1),(22,4,0),
(14,8,1),(16,8,1);

-- --- Transactions Stripe (statut : succeeded / refunded) ---
INSERT INTO transactions (montant, reference_stripe, statut_paiement, id_user, type, commission, id_event) VALUES
(20.00,'pi_demo0001','succeeded',12,'atelier',0.00,1),
(50.00,'pi_demo0002','succeeded',14,'atelier',0.00,2),
(29.99,'pi_demo0003','succeeded',7,'abonnement',0.00,NULL),
(29.99,'pi_demo0004','succeeded',8,'abonnement',0.00,NULL),
(8.00,'pi_demo0005','succeeded',3,'objet',0.80,NULL),
(15.00,'pi_demo0006','succeeded',16,'objet',1.50,NULL),
(80.00,'pi_demo0007','succeeded',12,'prestation',8.00,NULL),
(18.00,'pi_demo0008','succeeded',18,'prestation',1.80,NULL),
(30.00,'pi_demo0009','succeeded',12,'atelier',0.00,4),
(50.00,'pi_demo0010','refunded',20,'atelier',0.00,2),
(29.99,'pi_demo0011','succeeded',10,'abonnement',0.00,NULL);

-- --- Mouvements du portefeuille (credit / debit / vente / retrait) ---
INSERT INTO mouvements_portefeuille (id_user, montant, type, description) VALUES
(7,72.00,'vente','Vente prestation luminaire (net de commission)'),
(11,16.20,'vente','Vente prestation vase (net de commission)'),
(3,7.20,'vente','Vente annonce bocaux (net de commission)'),
(12,12.00,'credit','Recompense fidelite (score upcycling)'),
(7,-50.00,'retrait','Retrait vers compte bancaire'),
(18,22.30,'credit','Remboursement partiel atelier'),
(10,415.00,'vente','Ventes cumulees de prestations'),
(14,5.50,'credit','Bonus de bienvenue');

-- --- Forum : messages principaux + reponses (epingle / est_modere) ---
-- Messages principaux (id_message_parent = NULL)
INSERT INTO message_forums (id_message, contenu, id_user_auteur, est_modere, categorie, epingle, titre, id_message_parent) VALUES
(1,'Bonjour, quelqu''un a des conseils pour poncer un vieux meuble sans l''abimer ?',12,0,'Entraide',0,'Comment poncer un vieux meuble ?',NULL),
(2,'Petite astuce : pour proteger le bois naturellement, l''huile de lin fonctionne tres bien.',7,0,'Astuces',1,'Astuce : proteger le bois naturellement',NULL),
(3,'Je cherche des personnes motivees pour un projet de fresque a Montreuil.',14,0,'Projets',0,'Recherche partenaires projet fresque',NULL),
(4,'Achetez pas cher sur mon site externe !!!',20,1,'General',0,'Bon plan',NULL),
(5,'Bienvenue sur le forum UpcycleConnect ! Respectez la charte et partagez vos idees.',5,0,'General',1,'Bienvenue sur le forum',NULL);
-- Reponses (id_message_parent renseigne)
INSERT INTO message_forums (contenu, id_user_auteur, categorie, id_message_parent) VALUES
('Utilise du papier de verre grain 120 puis 240, ca marche bien.',7,'Entraide',1),
('Pense a bien depoussierer entre chaque passage.',13,'Entraide',1),
('Je suis partant, je suis a Montreuil !',18,'Projets',3),
('Super astuce, merci beaucoup !',16,'Astuces',2),
('Merci pour l''accueil !',15,'General',5);

-- --- Notifications + destinataires (est_lue 0/1) ---
INSERT INTO notifications (id_notif, titre, message) VALUES
(1,'Nouvel atelier disponible','L''atelier "Atelier recup bois debutant" est ouvert aux inscriptions.'),
(2,'Votre annonce a ete validee','Votre annonce est desormais en ligne sur la plateforme.'),
(3,'Votre annonce a ete refusee','Votre annonce a ete refusee, consultez le motif dans "Mes annonces".'),
(4,'Bienvenue chez UpcycleConnect','Decouvrez toutes les fonctionnalites de la plateforme !');

INSERT INTO recoit_notif (id_user, id_notif, est_lue) VALUES
(12,1,1),(13,1,0),(14,1,0),(16,1,0),
(12,2,1),
(20,3,1),
(12,4,1),(14,4,0),(16,4,0);

-- --- Articles de conseils / news (rediges par les salaries) ---
INSERT INTO article_conseil (titre, contenu, type, id_auteur, statut_validation) VALUES
('5 idees pour recycler vos palettes','Table basse, etagere, jardiniere... les palettes offrent mille possibilites.','Conseil',5,1),
('Les bases de l''upcycling textile','Transformer de vieux vetements en accessoires : nos conseils pour debuter.','Conseil',6,1),
('UpcycleConnect ouvre a Montreuil','Un nouvel entrepot ouvre ses portes pour la remise a neuf des materiaux.','news',1,1),
('Tutoriel : fabriquer une etagere','Suivez ce guide pas a pas pour realiser une etagere en bois recupere.','Tutoriel',5,1),
('Bien trier ses materiaux','Un bon tri facilite la reutilisation : voici comment vous organiser.','Conseil',6,1),
('Nouveau partenariat eco-responsable','UpcycleConnect s''associe a une marque de materiaux recycles.','news',1,1);

-- --- Documents generes (factures PDF / attestations) ---
INSERT INTO documents (id_user, type, nom_fichier) VALUES
(12,'facture','facture_atelier_1.pdf'),
(14,'facture','facture_atelier_2.pdf'),
(7,'facture','facture_abonnement_1.pdf'),
(12,'attestation','attestation_atelier_4.pdf'),
(18,'attestation','attestation_atelier_8.pdf');
