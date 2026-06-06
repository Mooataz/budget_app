<?php
// ============================================================
// src/dao/UtilisateurDAO.php
// ============================================================

class UtilisateurDAO {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findByEmail(string $email): ?array {
        $st = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        return $st->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function findByStatut(string $statut): array {
        $st = $this->db->prepare('SELECT id,nom,prenom,email,role,statut,created_at FROM users WHERE statut = ? ORDER BY created_at DESC');
        $st->execute([$statut]);
        return $st->fetchAll();
    }

    public function findAll(): array {
        return $this->db->query('SELECT id,nom,prenom,email,role,statut,created_at FROM users ORDER BY created_at DESC')->fetchAll();
    }

    public function create(string $nom, string $prenom, string $email, string $hash): int {
        $st = $this->db->prepare('INSERT INTO users (nom,prenom,email,mdp_hash) VALUES (?,?,?,?)');
        $st->execute([$nom, $prenom, $email, $hash]);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatut(int $id, string $statut): void {
        $this->db->prepare('UPDATE users SET statut=? WHERE id=?')->execute([$statut, $id]);
    }

    public function updateRole(int $id, string $role): void {
        $this->db->prepare('UPDATE users SET role=? WHERE id=?')->execute([$role, $id]);
    }

    public function updateProfil(int $id, string $nom, string $prenom, string $email): void {
        $this->db->prepare('UPDATE users SET nom=?,prenom=?,email=? WHERE id=?')->execute([$nom, $prenom, $email, $id]);
    }

    public function updatePassword(int $id, string $hash): void {
        $this->db->prepare('UPDATE users SET mdp_hash=? WHERE id=?')->execute([$hash, $id]);
    }

    public function updateLastLogin(int $id): void {
        $this->db->prepare('UPDATE users SET derniere_connexion=NOW(), echecs_connexion=0 WHERE id=?')->execute([$id]);
    }

    public function incrementEchecs(int $id): void {
        $this->db->prepare('UPDATE users SET echecs_connexion=echecs_connexion+1 WHERE id=?')->execute([$id]);
    }

    public function delete(int $id): void {
        $this->db->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
    }
}


// ============================================================
// src/dao/TransactionDAO.php
// ============================================================

class TransactionDAO {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findById(int $id): ?array {
        $st = $this->db->prepare('SELECT t.*,c.nom as categorie_nom FROM transactions t JOIN categories c ON c.id=t.categorie_id WHERE t.id=?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function findByUserAndPeriode(int $userId, string $debut, string $fin): array {
        $st = $this->db->prepare(
            'SELECT t.*,c.nom as categorie_nom,c.icone as categorie_icone,b.nom as budget_nom
             FROM transactions t
             JOIN categories c ON c.id=t.categorie_id
             JOIN budgets b ON b.id=t.budget_id
             WHERE t.user_id=? AND t.date_op BETWEEN ? AND ?
             ORDER BY t.date_op DESC'
        );
        $st->execute([$userId, $debut, $fin]);
        return $st->fetchAll();
    }

    public function findByBudget(int $budgetId): array {
        $st = $this->db->prepare(
            'SELECT t.*,c.nom as categorie_nom,u.nom as user_nom,u.prenom as user_prenom
             FROM transactions t
             JOIN categories c ON c.id=t.categorie_id
             JOIN users u ON u.id=t.user_id
             WHERE t.budget_id=?
             ORDER BY t.date_op DESC'
        );
        $st->execute([$budgetId]);
        return $st->fetchAll();
    }

    public function save(int $userId, int $budgetId, int $catId, string $type, float $montant, string $date, string $desc): int {
        $st = $this->db->prepare('INSERT INTO transactions (user_id,budget_id,categorie_id,type,montant,date_op,description) VALUES (?,?,?,?,?,?,?)');
        $st->execute([$userId, $budgetId, $catId, $type, $montant, $date, $desc]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $catId, string $type, float $montant, string $date, string $desc): void {
        $this->db->prepare('UPDATE transactions SET categorie_id=?,type=?,montant=?,date_op=?,description=? WHERE id=?')
                 ->execute([$catId, $type, $montant, $date, $desc, $id]);
    }

    public function delete(int $id): void {
        $this->db->prepare('DELETE FROM transactions WHERE id=?')->execute([$id]);
    }

    public function sumByBudget(int $budgetId, string $type): float {
        $st = $this->db->prepare('SELECT COALESCE(SUM(montant),0) FROM transactions WHERE budget_id=? AND type=?');
        $st->execute([$budgetId, $type]);
        return (float)$st->fetchColumn();
    }

    public function repartitionParCategorie(int $userId, string $debut, string $fin): array {
        $st = $this->db->prepare(
            'SELECT c.nom,c.icone, SUM(t.montant) as total
             FROM transactions t JOIN categories c ON c.id=t.categorie_id
             WHERE t.user_id=? AND t.type="DEPENSE" AND t.date_op BETWEEN ? AND ?
             GROUP BY c.id ORDER BY total DESC'
        );
        $st->execute([$userId, $debut, $fin]);
        return $st->fetchAll();
    }

    public function evolutionParJour(int $userId, string $debut, string $fin): array {
        $st = $this->db->prepare(
            'SELECT date_op, type, SUM(montant) as total
             FROM transactions WHERE user_id=? AND date_op BETWEEN ? AND ?
             GROUP BY date_op, type ORDER BY date_op ASC'
        );
        $st->execute([$userId, $debut, $fin]);
        return $st->fetchAll();
    }
}


// ============================================================
// src/dao/BudgetDAO.php
// ============================================================

class BudgetDAO {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findById(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM budgets WHERE id=?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function findByUser(int $userId): array {
        $st = $this->db->prepare(
            'SELECT DISTINCT b.* FROM budgets b
             LEFT JOIN budget_members bm ON bm.budget_id=b.id AND bm.statut_invitation="ACCEPTEE"
             WHERE b.proprietaire_id=? OR bm.user_id=?
             ORDER BY b.created_at DESC'
        );
        $st->execute([$userId, $userId]);
        return $st->fetchAll();
    }

    public function findAll(): array {
        return $this->db->query('SELECT b.*,u.nom,u.prenom FROM budgets b JOIN users u ON u.id=b.proprietaire_id ORDER BY b.created_at DESC')->fetchAll();
    }

    public function create(string $nom, float $plafond, string $periode, string $dateDebut, ?string $dateFin, string $type, int $proprietaireId): int {
        $st = $this->db->prepare('INSERT INTO budgets (nom,plafond_global,periode,date_debut,date_fin,type,proprietaire_id) VALUES (?,?,?,?,?,?,?)');
        $st->execute([$nom, $plafond, $periode, $dateDebut, $dateFin, $type, $proprietaireId]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $nom, float $plafond, string $periode): void {
        $this->db->prepare('UPDATE budgets SET nom=?,plafond_global=?,periode=? WHERE id=?')->execute([$nom, $plafond, $periode, $id]);
    }

    public function updateMontantConsomme(int $id, float $montant): void {
        $this->db->prepare('UPDATE budgets SET montant_consomme=? WHERE id=?')->execute([$montant, $id]);
    }

    public function delete(int $id): void {
        $this->db->prepare('DELETE FROM budgets WHERE id=?')->execute([$id]);
    }

    // Plafonds par catégorie
    public function savePlafondCategorie(int $budgetId, int $catId, float $plafond): void {
        $st = $this->db->prepare('INSERT INTO budget_categories (budget_id,categorie_id,plafond) VALUES (?,?,?) ON DUPLICATE KEY UPDATE plafond=?');
        $st->execute([$budgetId, $catId, $plafond, $plafond]);
    }

    public function getPlafondsCats(int $budgetId): array {
        $st = $this->db->prepare('SELECT bc.*,c.nom FROM budget_categories bc JOIN categories c ON c.id=bc.categorie_id WHERE bc.budget_id=?');
        $st->execute([$budgetId]);
        return $st->fetchAll();
    }

    public function updateConsommeCat(int $budgetId, int $catId, float $montant): void {
        $this->db->prepare('UPDATE budget_categories SET montant_consomme=? WHERE budget_id=? AND categorie_id=?')->execute([$montant, $budgetId, $catId]);
    }
}


// ============================================================
// src/dao/CategorieDAO.php
// ============================================================

class CategorieDAO {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findAll(?int $userId = null): array {
        $st = $this->db->prepare('SELECT * FROM categories WHERE est_defaut=1 OR user_id=? ORDER BY est_defaut DESC, nom ASC');
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public function findById(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM categories WHERE id=?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function create(string $nom, string $icone, int $userId): int {
        $st = $this->db->prepare('INSERT INTO categories (nom,icone,est_defaut,user_id) VALUES (?,?,0,?)');
        $st->execute([$nom, $icone, $userId]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $nom, string $icone): void {
        $this->db->prepare('UPDATE categories SET nom=?,icone=? WHERE id=?')->execute([$nom, $icone, $id]);
    }

    public function delete(int $id): void {
        $this->db->prepare('DELETE FROM categories WHERE id=? AND est_defaut=0')->execute([$id]);
    }
}


// ============================================================
// src/dao/AlerteDAO.php
// ============================================================

class AlerteDAO {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findByBudget(int $budgetId): array {
        $st = $this->db->prepare('SELECT * FROM alertes WHERE budget_id=? ORDER BY created_at DESC');
        $st->execute([$budgetId]);
        return $st->fetchAll();
    }

    public function findNonLuesByUser(int $userId): array {
        $st = $this->db->prepare(
            'SELECT a.*,b.nom as budget_nom FROM alertes a
             JOIN budgets b ON b.id=a.budget_id
             LEFT JOIN budget_members bm ON bm.budget_id=b.id AND bm.user_id=?
             WHERE (b.proprietaire_id=? OR bm.user_id=?) AND a.lue=0
             ORDER BY a.created_at DESC LIMIT 20'
        );
        $st->execute([$userId, $userId, $userId]);
        return $st->fetchAll();
    }

    public function findActiveBudget(int $budgetId): ?array {
        $st = $this->db->prepare('SELECT * FROM alertes WHERE budget_id=? AND lue=0 ORDER BY created_at DESC LIMIT 1');
        $st->execute([$budgetId]);
        return $st->fetch() ?: null;
    }

    public function upsert(int $budgetId, string $niveau, string $message): int {
        // Supprimer les anciennes alertes non lues du même budget
        $this->db->prepare('DELETE FROM alertes WHERE budget_id=? AND lue=0')->execute([$budgetId]);
        $st = $this->db->prepare('INSERT INTO alertes (budget_id,niveau,message) VALUES (?,?,?)');
        $st->execute([$budgetId, $niveau, $message]);
        return (int)$this->db->lastInsertId();
    }

    public function marquerLue(int $id): void {
        $this->db->prepare('UPDATE alertes SET lue=1 WHERE id=?')->execute([$id]);
    }

    public function marquerToutesLues(int $userId): void {
        $st = $this->db->prepare(
            'UPDATE alertes a
             JOIN budgets b ON b.id=a.budget_id
             LEFT JOIN budget_members bm ON bm.budget_id=b.id
             SET a.lue=1
             WHERE b.proprietaire_id=? OR bm.user_id=?'
        );
        $st->execute([$userId, $userId]);
    }
}


// ============================================================
// src/dao/MembreBudgetDAO.php
// ============================================================

class MembreBudgetDAO {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findByBudget(int $budgetId): array {
        $st = $this->db->prepare(
            'SELECT bm.*,u.nom,u.prenom,u.email FROM budget_members bm
             JOIN users u ON u.id=bm.user_id WHERE bm.budget_id=?'
        );
        $st->execute([$budgetId]);
        return $st->fetchAll();
    }

    public function findByBudgets(array $budgetIds): array {
        if (empty($budgetIds)) return [];
        $placeholders = implode(',', array_fill(0, count($budgetIds), '?'));
        $st = $this->db->prepare(
            "SELECT bm.*,u.nom,u.prenom,u.email FROM budget_members bm
             JOIN users u ON u.id=bm.user_id WHERE bm.budget_id IN ($placeholders)"
        );
        $st->execute($budgetIds);
        $result = [];
        foreach ($st->fetchAll() as $row) {
            $result[(int)$row['budget_id']][] = $row;
        }
        return $result;
    }

    public function findMembre(int $budgetId, int $userId): ?array {
        $st = $this->db->prepare('SELECT * FROM budget_members WHERE budget_id=? AND user_id=? LIMIT 1');
        $st->execute([$budgetId, $userId]);
        return $st->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM budget_members WHERE id=? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function addMembre(int $budgetId, int $userId, string $role, string $token): void {
        $st = $this->db->prepare('INSERT IGNORE INTO budget_members (budget_id,user_id,role_budget,token_invitation) VALUES (?,?,?,?)');
        $st->execute([$budgetId, $userId, $role, $token]);
    }

    public function accepterInvitation(int $budgetId, int $userId): void {
        $this->db->prepare('UPDATE budget_members SET statut_invitation="ACCEPTEE",date_adhesion=NOW() WHERE budget_id=? AND user_id=?')
                 ->execute([$budgetId, $userId]);
    }

    public function refuserInvitation(int $budgetId, int $userId): void {
        $this->db->prepare('UPDATE budget_members SET statut_invitation="REFUSEE" WHERE budget_id=? AND user_id=?')
                 ->execute([$budgetId, $userId]);
    }

    public function findByToken(string $token): ?array {
        $st = $this->db->prepare('SELECT * FROM budget_members WHERE token_invitation=? LIMIT 1');
        $st->execute([$token]);
        return $st->fetch() ?: null;
    }

    public function remove(int $budgetId, int $userId): void {
        $this->db->prepare('DELETE FROM budget_members WHERE budget_id=? AND user_id=?')->execute([$budgetId, $userId]);
    }

    public function isMembre(int $budgetId, int $userId): bool {
        $st = $this->db->prepare('SELECT id FROM budget_members WHERE budget_id=? AND user_id=? AND statut_invitation="ACCEPTEE" LIMIT 1');
        $st->execute([$budgetId, $userId]);
        return (bool)$st->fetch();
    }

    public function findInvitationsByUser(int $userId): array {
        $st = $this->db->prepare(
            'SELECT bm.*,b.nom as budget_nom,b.type as budget_type,
                    u.prenom as proprio_prenom,u.nom as proprio_nom
             FROM budget_members bm
             JOIN budgets b ON b.id=bm.budget_id
             JOIN users u ON u.id=b.proprietaire_id
             WHERE bm.user_id=? AND bm.statut_invitation="EN_ATTENTE"
             ORDER BY bm.created_at DESC'
        );
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public function countInvitationsByUser(int $userId): int {
        $st = $this->db->prepare('SELECT COUNT(*) FROM budget_members WHERE user_id=? AND statut_invitation="EN_ATTENTE"');
        $st->execute([$userId]);
        return (int)$st->fetchColumn();
    }
}
