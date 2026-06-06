-- ============================================================
-- SCHEMA SQL — Application Gestion Collaborative de Budget
-- Encodage : utf8mb4 | Moteur : InnoDB | MySQL 8.0+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS budget_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE budget_db;

-- ============================================================
-- TABLE : users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(100) NOT NULL,
    prenom        VARCHAR(100) NOT NULL,
    email         VARCHAR(180) NOT NULL UNIQUE,
    mdp_hash      VARCHAR(255) NOT NULL,
    role          ENUM('UTILISATEUR','ADMINISTRATEUR') NOT NULL DEFAULT 'UTILISATEUR',
    statut        ENUM('EN_ATTENTE','ACTIF','SUSPENDU')  NOT NULL DEFAULT 'EN_ATTENTE',
    echecs_connexion INT UNSIGNED NOT NULL DEFAULT 0,
    derniere_connexion DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : categories
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(100) NOT NULL,
    icone         VARCHAR(50)  NOT NULL DEFAULT 'tag',
    est_defaut    TINYINT(1)   NOT NULL DEFAULT 0,
    user_id       INT UNSIGNED NULL,   -- NULL = catégorie système
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : budgets
-- ============================================================
CREATE TABLE IF NOT EXISTS budgets (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom               VARCHAR(150) NOT NULL,
    plafond_global    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    periode           ENUM('MENSUEL','HEBDOMADAIRE','PERSONNALISE') NOT NULL DEFAULT 'MENSUEL',
    date_debut        DATE NOT NULL,
    date_fin          DATE NULL,
    type              ENUM('INDIVIDUEL','PARTAGE') NOT NULL DEFAULT 'INDIVIDUEL',
    montant_consomme  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    proprietaire_id   INT UNSIGNED NOT NULL,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proprietaire_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : budget_categories  (plafonds par catégorie)
-- ============================================================
CREATE TABLE IF NOT EXISTS budget_categories (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    budget_id         INT UNSIGNED NOT NULL,
    categorie_id      INT UNSIGNED NOT NULL,
    plafond           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    montant_consomme  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    UNIQUE KEY uk_bc (budget_id, categorie_id),
    FOREIGN KEY (budget_id)    REFERENCES budgets(id)    ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : budget_members
-- ============================================================
CREATE TABLE IF NOT EXISTS budget_members (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    budget_id         INT UNSIGNED NOT NULL,
    user_id           INT UNSIGNED NOT NULL,
    role_budget       ENUM('PROPRIETAIRE','MEMBRE') NOT NULL DEFAULT 'MEMBRE',
    statut_invitation ENUM('EN_ATTENTE','ACCEPTEE','REFUSEE') NOT NULL DEFAULT 'EN_ATTENTE',
    token_invitation  VARCHAR(64) NULL,
    date_adhesion     DATETIME NULL,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_bm (budget_id, user_id),
    FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : transactions
-- ============================================================
CREATE TABLE IF NOT EXISTS transactions (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    budget_id     INT UNSIGNED NOT NULL,
    categorie_id  INT UNSIGNED NOT NULL,
    type          ENUM('REVENU','DEPENSE') NOT NULL,
    montant       DECIMAL(12,2) NOT NULL,
    date_op       DATE NOT NULL,
    description   VARCHAR(255) NOT NULL DEFAULT '',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)      REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (budget_id)    REFERENCES budgets(id)     ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id)  ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : alertes
-- ============================================================
CREATE TABLE IF NOT EXISTS alertes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    budget_id   INT UNSIGNED NOT NULL,
    niveau      ENUM('INFORMATION','AVERTISSEMENT','CRITIQUE') NOT NULL,
    message     VARCHAR(255) NOT NULL,
    lue         TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DONNÉES DE BASE (seed)
-- ============================================================

-- Admin par défaut (mdp: password) — À CHANGER EN PRODUCTION
INSERT INTO users (nom, prenom, email, mdp_hash, role, statut) VALUES
('Admin', 'Système', 'admin@budget.local',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHqu4ioiK',
 'ADMINISTRATEUR', 'ACTIF');

-- Catégories par défaut
INSERT INTO categories (nom, icone, est_defaut, user_id) VALUES
('Alimentation',   'utensils',        1, NULL),
('Transport',      'car',             1, NULL),
('Logement',       'home',            1, NULL),
('Santé',          'heart-pulse',     1, NULL),
('Loisirs',        'gamepad',         1, NULL),
('Études',         'graduation-cap',  1, NULL),
('Vêtements',      'shirt',           1, NULL),
('Abonnements',    'repeat',          1, NULL),
('Épargne',        'piggy-bank',      1, NULL),
('Autres',         'ellipsis',        1, NULL);

-- ============================================================
-- INDEX DE PERFORMANCE
-- ============================================================
CREATE INDEX idx_tx_user_date ON transactions(user_id, date_op);
CREATE INDEX idx_tx_budget_type ON transactions(budget_id, type);
CREATE INDEX idx_bm_user_statut ON budget_members(user_id, statut_invitation);
CREATE INDEX idx_alertes_budget_lue ON alertes(budget_id, lue);
CREATE INDEX idx_categories_defaut ON categories(est_defaut);

SET FOREIGN_KEY_CHECKS = 1;
