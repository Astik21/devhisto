-- Table des rôles
CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL,  -- Nom du rôle (en anglais)
    description VARCHAR(255)         -- Description du rôle
) ENGINE=InnoDB;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT UNSIGNED,  -- Correspond à `roles.id`
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table des entreprises
CREATE TABLE entreprises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_entreprise VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Table des devis
CREATE TABLE devis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entreprise_id INT,  -- Référence à l'entreprise
    user_id INT,  -- Référence à l'utilisateur qui a créé le devis
    entreprise VARCHAR(255),
    montant_total DECIMAL(10,2),
    status ENUM('pending', 'validated', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table des corps d'état
CREATE TABLE corps_etat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_corps_etat VARCHAR(255) NOT NULL  -- Ex : Plâtrerie, Peinture, Plomberie
) ENGINE=InnoDB;

-- Table des lignes de prix
CREATE TABLE lignes_prix (
    id INT AUTO_INCREMENT PRIMARY KEY,
    devis_id INT,  -- Référence au devis
    corps_etat_id INT,  -- Référence au corps d'état
    description VARCHAR(255),  -- Ex : Receveur de douche 80x80
    prix_unitaire DECIMAL(10,2),
    quantite INT,
    prix_total DECIMAL(10,2),  -- Prix unitaire * Quantité
    FOREIGN KEY (devis_id) REFERENCES devis(id) ON DELETE CASCADE,
    FOREIGN KEY (corps_etat_id) REFERENCES corps_etat(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des ouvrages
CREATE TABLE ouvrages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    devis_id INT,  -- Référence au devis
    type_ouvrage VARCHAR(255),  -- Ex : Douche complète 80x80
    prix_total DECIMAL(10,2),
    FOREIGN KEY (devis_id) REFERENCES devis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des pièces
CREATE TABLE pieces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    devis_id INT,  -- Référence au devis
    nom_piece VARCHAR(255),  -- Ex : Salle de bain, Cuisine
    prix_total DECIMAL(10,2),
    FOREIGN KEY (devis_id) REFERENCES devis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des chantiers
CREATE TABLE chantiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_chantier VARCHAR(255)  -- Ex : Rénovation complète, Salle de bain
) ENGINE=InnoDB;

-- Table des détails des chantiers
CREATE TABLE chantiers_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chantier_id INT,  -- Référence à la table `chantiers`
    entreprise_id INT,  -- Référence à la table `entreprises`
    nom_client VARCHAR(255) NOT NULL,
    type_chantier VARCHAR(255),  -- Référence au type de chantier de la table `chantiers`
    pieces_refaites TEXT,  -- Liste des pièces refaites
    FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des logs (actions effectuées sur les devis)
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,  -- Référence à l'utilisateur
    action VARCHAR(255),  -- Ex : Création de devis, Validation
    details TEXT,  -- Détails de l'action effectuée
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Table des fichiers (PDF associés aux devis)
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    devis_id INT,  -- Référence au devis
    file_path VARCHAR(255),  -- Chemin du fichier sur le serveur
    file_name VARCHAR(255),  -- Nom du fichier
    file_size INT,  -- Taille du fichier en octets
    status ENUM('pending', 'validated', 'error') DEFAULT 'pending',  -- Statut du fichier
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (devis_id) REFERENCES devis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insérer les rôles dans la table
INSERT INTO roles (role_name, description) VALUES 
('admin', 'Administrator: Full access to all features and settings'),
('manager', 'Manager: Can manage and view data, but limited settings access'),
('user', 'User: Can view data and interact with the application, but cannot change settings');

-- Indexes pour optimiser les recherches fréquentes
CREATE INDEX idx_devis_user_id ON devis(user_id);
CREATE INDEX idx_lignes_prix_devis_id ON lignes_prix(devis_id);
CREATE INDEX idx_lignes_prix_corps_etat_id ON lignes_prix(corps_etat_id);
CREATE INDEX idx_chantiers_detail_chantier_id ON chantiers_detail(chantier_id);
CREATE INDEX idx_chantiers_detail_entreprise_id ON chantiers_detail(entreprise_id);
