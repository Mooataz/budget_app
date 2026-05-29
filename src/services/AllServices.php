<?php
// ============================================================
// src/services/AuthService.php
// ============================================================

class AuthService {
    private UtilisateurDAO $userDao;

    public function __construct() {
        $this->userDao = new UtilisateurDAO();
    }

    /** DSS-01 / SC-02 — Inscription */
    public function inscrire(string $nom, string $prenom, string $email, string $mdp): array {
        // Unicité email
        if ($this->userDao->findByEmail($email)) {
            return ['ok' => false, 'error' => 'email', 'message' => 'Cet email est déjà utilisé.'];
        }
        $hash = password_hash($mdp, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $id   = $this->userDao->create($nom, $prenom, $email, $hash);
        // TODO: envoyer email admin (EmailService)
        return ['ok' => true, 'user_id' => $id];
    }

    /** DSS-02 / SC-02 — Connexion */
    public function authentifier(string $email, string $mdp): array {
        $user = $this->userDao->findByEmail($email);

        if (!$user || $user['statut'] !== 'ACTIF') {
            return ['ok' => false, 'message' => 'Identifiants incorrects ou compte inactif.'];
        }
        if (!password_verify($mdp, $user['mdp_hash'])) {
            $this->userDao->incrementEchecs((int)$user['id']);
            return ['ok' => false, 'message' => 'Identifiants incorrects.'];
        }
        $this->userDao->updateLastLogin((int)$user['id']);

        // Créer la session
        Session::set('user_id',    (int)$user['id']);
        Session::set('user_role',  $user['role']);
        Session::set('user_nom',   $user['prenom'] . ' ' . $user['nom']);
        Session::generateCsrf();

        return ['ok' => true, 'role' => $user['role']];
    }

    public function deconnecter(): void {
        Session::destroy();
    }

    public function changerMotDePasse(int $userId, string $ancien, string $nouveau): array {
        $user = $this->userDao->findById($userId);
        if (!$user || !password_verify($ancien, $user['mdp_hash'])) {
            return ['ok' => false, 'message' => 'Mot de passe actuel incorrect.'];
        }
        $hash = password_hash($nouveau, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $this->userDao->updatePassword($userId, $hash);
        return ['ok' => true];
    }
}


// ============================================================
// src/services/BudgetService.php
// ============================================================

class BudgetService {
    private BudgetDAO       $budgetDao;
    private TransactionDAO  $txDao;
    private AlerteService   $alerteService;
    private MembreBudgetDAO $membreDao;

    public function __construct() {
        $this->budgetDao     = new BudgetDAO();
        $this->txDao         = new TransactionDAO();
        $this->membreDao     = new MembreBudgetDAO();
        $this->alerteService = new AlerteService();
    }

    /** DSS-04 — Créer un budget (individuel ou partagé) */
    public function creer(array $data, int $userId): array {
        $id = $this->budgetDao->create(
            $data['nom'],
            (float)$data['plafond_global'],
            $data['periode'],
            $data['date_debut'],
            $data['date_fin'] ?? null,
            $data['type'],
            $userId
        );
        // Ajouter le créateur comme propriétaire dans budget_members
        $this->membreDao->addMembre($id, $userId, 'PROPRIETAIRE', '');
        $this->membreDao->accepterInvitation($id, $userId);

        // Plafonds par catégorie
        if (!empty($data['plafonds_cats'])) {
            foreach ($data['plafonds_cats'] as $catId => $plafond) {
                $this->budgetDao->savePlafondCategorie($id, (int)$catId, (float)$plafond);
            }
        }
        return ['ok' => true, 'budget_id' => $id];
    }

    /** Recalculer montant consommé + déclencher alertes */
    public function recalculer(int $budgetId): float {
        $depenses = $this->txDao->sumByBudget($budgetId, 'DEPENSE');
        $this->budgetDao->updateMontantConsomme($budgetId, $depenses);

        $budget = $this->budgetDao->findById($budgetId);
        $taux   = $budget['plafond_global'] > 0
            ? round(($depenses / $budget['plafond_global']) * 100, 1)
            : 0;

        $this->alerteService->evaluerEtNotifier($budgetId, $taux);
        return $taux;
    }

    public function getForUser(int $userId): array {
        $budgets = $this->budgetDao->findByUser($userId);
        foreach ($budgets as &$b) {
            $b['taux'] = $b['plafond_global'] > 0
                ? round(($b['montant_consomme'] / $b['plafond_global']) * 100, 1)
                : 0;
            $b['solde']  = $b['plafond_global'] - $b['montant_consomme'];
            $b['membres'] = $this->membreDao->findByBudget((int)$b['id']);
        }
        return $budgets;
    }

    /** Vérifier que l'utilisateur a accès au budget */
    public function hasAccess(int $budgetId, int $userId): bool {
        $budget = $this->budgetDao->findById($budgetId);
        if (!$budget) return false;
        if ((int)$budget['proprietaire_id'] === $userId) return true;
        return $this->membreDao->isMembre($budgetId, $userId);
    }

    public function inviterMembre(int $budgetId, string $email, int $inviteurId): array {
        $userDao = new UtilisateurDAO();
        $membre  = $userDao->findByEmail($email);
        if (!$membre) {
            return ['ok' => false, 'message' => 'Utilisateur introuvable.'];
        }
        $token = bin2hex(random_bytes(TOKEN_LENGTH));
        $this->membreDao->addMembre($budgetId, (int)$membre['id'], 'MEMBRE', $token);
        // TODO: EmailService::envoyerInvitation($membre['email'], $token);
        return ['ok' => true, 'token' => $token];
    }

    public function rejoindre(string $token, int $userId): array {
        $inv = $this->membreDao->findByToken($token);
        if (!$inv || (int)$inv['user_id'] !== $userId) {
            return ['ok' => false, 'message' => 'Invitation invalide ou expirée.'];
        }
        $this->membreDao->accepterInvitation((int)$inv['budget_id'], $userId);
        return ['ok' => true, 'budget_id' => (int)$inv['budget_id']];
    }
}


// ============================================================
// src/services/TransactionService.php  — SC-01
// ============================================================

class TransactionService {
    private TransactionDAO $txDao;
    private BudgetService  $budgetService;

    public function __construct() {
        $this->txDao         = new TransactionDAO();
        $this->budgetService = new BudgetService();
    }

    public function ajouter(int $userId, array $dto): array {
        $id   = $this->txDao->save(
            $userId,
            (int)$dto['budget_id'],
            (int)$dto['categorie_id'],
            $dto['type'],
            (float)$dto['montant'],
            $dto['date_op'],
            $dto['description'] ?? ''
        );
        $taux = $this->budgetService->recalculer((int)$dto['budget_id']);
        return ['ok' => true, 'transaction_id' => $id, 'taux' => $taux];
    }

    public function modifier(int $txId, int $userId, array $dto): array {
        $tx = $this->txDao->findById($txId);
        if (!$tx || (int)$tx['user_id'] !== $userId) {
            return ['ok' => false, 'message' => 'Transaction introuvable.'];
        }
        $this->txDao->update($txId, (int)$dto['categorie_id'], $dto['type'], (float)$dto['montant'], $dto['date_op'], $dto['description'] ?? '');
        $taux = $this->budgetService->recalculer((int)$tx['budget_id']);
        return ['ok' => true, 'taux' => $taux];
    }

    public function supprimer(int $txId, int $userId): array {
        $tx = $this->txDao->findById($txId);
        if (!$tx || (int)$tx['user_id'] !== $userId) {
            return ['ok' => false, 'message' => 'Transaction introuvable.'];
        }
        $this->txDao->delete($txId);
        $this->budgetService->recalculer((int)$tx['budget_id']);
        return ['ok' => true];
    }
}


// ============================================================
// src/services/AlerteService.php  — SC-04
// ============================================================

class AlerteService {
    private AlerteDAO       $alerteDao;

    public function __construct() {
        $this->alerteDao = new AlerteDAO();
    }

    /** Évaluer le taux et créer/supprimer l'alerte — DSS-06 */
    public function evaluerEtNotifier(int $budgetId, float $taux): ?array {
        if ($taux >= SEUIL_CRITIQUE) {
            $msg   = "Budget dépassé ! Consommation : {$taux}%";
            $niveau = 'CRITIQUE';
        } elseif ($taux >= SEUIL_AVERTISSEMENT) {
            $msg   = "Budget à {$taux}% — Proche de la limite.";
            $niveau = 'AVERTISSEMENT';
        } else {
            // Supprimer l'alerte active si budget revenu sous le seuil
            $existing = $this->alerteDao->findActiveBudget($budgetId);
            if ($existing) {
                $this->alerteDao->marquerLue((int)$existing['id']);
            }
            return null;
        }

        $id = $this->alerteDao->upsert($budgetId, $niveau, $msg);
        return ['id' => $id, 'niveau' => $niveau, 'message' => $msg];
    }
}


// ============================================================
// src/services/DashboardService.php  — SC-03
// ============================================================

class DashboardService {
    private TransactionDAO $txDao;
    private BudgetDAO      $budgetDao;
    private AlerteDAO      $alerteDao;

    public function __construct() {
        $this->txDao     = new TransactionDAO();
        $this->budgetDao = new BudgetDAO();
        $this->alerteDao = new AlerteDAO();
    }

    public function generer(int $userId, string $debut, string $fin): array {
        $transactions = $this->txDao->findByUserAndPeriode($userId, $debut, $fin);

        $totalRevenus  = 0.0;
        $totalDepenses = 0.0;
        foreach ($transactions as $t) {
            if ($t['type'] === 'REVENU')  $totalRevenus  += (float)$t['montant'];
            else                           $totalDepenses += (float)$t['montant'];
        }

        $budgetService = new BudgetService();
        $budgets  = $budgetService->getForUser($userId);
        $repartition = $this->txDao->repartitionParCategorie($userId, $debut, $fin);
        $evolution   = $this->txDao->evolutionParJour($userId, $debut, $fin);
        $alertes     = $this->alerteDao->findNonLuesByUser($userId);

        return [
            'total_revenus'  => $totalRevenus,
            'total_depenses' => $totalDepenses,
            'solde'          => $totalRevenus - $totalDepenses,
            'transactions'   => array_slice($transactions, 0, 10),
            'budgets'        => $budgets,
            'repartition'    => $repartition,
            'evolution'      => $evolution,
            'alertes'        => $alertes,
            'periode_debut'  => $debut,
            'periode_fin'    => $fin,
        ];
    }
}


// ============================================================
// src/services/AdminService.php  — SC-05
// ============================================================

class AdminService {
    private UtilisateurDAO $userDao;

    public function __construct() {
        $this->userDao = new UtilisateurDAO();
    }

    public function listerEnAttente(): array {
        return $this->userDao->findByStatut('EN_ATTENTE');
    }

    public function validerCompte(int $compteId): void {
        $this->userDao->updateStatut($compteId, 'ACTIF');
        // TODO: EmailService::envoyerConfirmation()
    }

    public function suspendreCompte(int $compteId): void {
        $this->userDao->updateStatut($compteId, 'SUSPENDU');
    }

    public function supprimerCompte(int $compteId): void {
        $this->userDao->delete($compteId);
    }

    public function changerRole(int $compteId, string $role): void {
        $this->userDao->updateRole($compteId, $role);
    }

    public function statsGlobales(): array {
        $db    = Database::getInstance();
        $users = (int)$db->query('SELECT COUNT(*) FROM users WHERE role="UTILISATEUR"')->fetchColumn();
        $txs   = (int)$db->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
        $bgets = (int)$db->query('SELECT COUNT(*) FROM budgets')->fetchColumn();
        $rev   = (float)$db->query('SELECT COALESCE(SUM(montant),0) FROM transactions WHERE type="REVENU"')->fetchColumn();
        $dep   = (float)$db->query('SELECT COALESCE(SUM(montant),0) FROM transactions WHERE type="DEPENSE"')->fetchColumn();
        return compact('users','txs','bgets','rev','dep');
    }
}
