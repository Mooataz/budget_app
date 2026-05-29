<?php
// ============================================================
// src/controllers/AuthController.php
// ============================================================

class AuthController {
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function showLogin(): void {
        require_once VIEWS . '/auth/login.php';
    }

    public function showRegister(): void {
        require_once VIEWS . '/auth/register.php';
    }

    public function login(): void {
        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Requête invalide (CSRF).');
            Response::redirect(BASE_URL . '/login');
        }
        $v = new Validator();
        $v->required('email', $_POST['email'] ?? '')
          ->email('email', $_POST['email'] ?? '')
          ->required('mot_de_passe', $_POST['mot_de_passe'] ?? '');

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            Response::redirect(BASE_URL . '/login');
        }
        $result = $this->authService->authentifier(
            trim($_POST['email']),
            $_POST['mot_de_passe']
        );
        if (!$result['ok']) {
            Session::flash('error', $result['message']);
            Response::redirect(BASE_URL . '/login');
        }
        $redirect = $result['role'] === 'ADMINISTRATEUR'
            ? BASE_URL . '/admin'
            : BASE_URL . '/dashboard';
        Response::redirect($redirect);
    }

    public function register(): void {
        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Requête invalide.');
            Response::redirect(BASE_URL . '/register');
        }
        $v = new Validator();
        $v->required('nom',    $_POST['nom'] ?? '')
          ->required('prenom', $_POST['prenom'] ?? '')
          ->required('email',  $_POST['email'] ?? '')
          ->email('email',     $_POST['email'] ?? '')
          ->required('mot_de_passe', $_POST['mot_de_passe'] ?? '')
          ->minLength('mot_de_passe', $_POST['mot_de_passe'] ?? '', 8);

        if ($_POST['mot_de_passe'] !== ($_POST['confirmation'] ?? '')) {
            Session::flash('error', 'Les mots de passe ne correspondent pas.');
            Response::redirect(BASE_URL . '/register');
        }
        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            Response::redirect(BASE_URL . '/register');
        }
        $result = $this->authService->inscrire(
            trim($_POST['nom']),
            trim($_POST['prenom']),
            trim($_POST['email']),
            $_POST['mot_de_passe']
        );
        if (!$result['ok']) {
            Session::flash('error', $result['message']);
            Response::redirect(BASE_URL . '/register');
        }
        Session::flash('success', 'Compte créé. En attente de validation par un administrateur.');
        Response::redirect(BASE_URL . '/login');
    }

    public function logout(): void {
        $this->authService->deconnecter();
        Response::redirect(BASE_URL . '/login');
    }

    public function showProfil(): void {
        Response::requireAuth();
        $userDao = new UtilisateurDAO();
        $user    = $userDao->findById(Session::userId());
        require_once VIEWS . '/auth/profil.php';
    }

    public function updateProfil(): void {
        Response::requireAuth();
        $userDao = new UtilisateurDAO();
        $userDao->updateProfil(Session::userId(), trim($_POST['nom']), trim($_POST['prenom']), trim($_POST['email']));
        Session::flash('success', 'Profil mis à jour.');
        Response::redirect(BASE_URL . '/profil');
    }

    public function changePassword(): void {
        Response::requireAuth();
        $result = $this->authService->changerMotDePasse(
            Session::userId(),
            $_POST['ancien_mdp'] ?? '',
            $_POST['nouveau_mdp'] ?? ''
        );
        if (!$result['ok']) {
            Session::flash('error', $result['message']);
        } else {
            Session::flash('success', 'Mot de passe modifié.');
        }
        Response::redirect(BASE_URL . '/profil');
    }
}


// ============================================================
// src/controllers/DashboardController.php
// ============================================================

class DashboardController {
    private DashboardService $svc;

    public function __construct() {
        $this->svc = new DashboardService();
    }

    public function index(): void {
        Response::requireAuth();
        $periode = $_GET['periode'] ?? 'mois';
        [$debut, $fin] = $this->getPeriode($periode, $_GET['debut'] ?? null, $_GET['fin'] ?? null);
        $data = $this->svc->generer(Session::userId(), $debut, $fin);
        require_once VIEWS . '/dashboard/index.php';
    }

    private function getPeriode(string $periode, ?string $debut, ?string $fin): array {
        if ($periode === 'custom' && $debut && $fin) {
            return [$debut, $fin];
        }
        $now = new DateTime();
        return match($periode) {
            'semaine' => [(clone $now)->modify('monday this week')->format('Y-m-d'), $now->format('Y-m-d')],
            'annee'   => [$now->format('Y') . '-01-01', $now->format('Y-m-d')],
            default   => [$now->format('Y-m') . '-01', $now->format('Y-m-d')],
        };
    }
}


// ============================================================
// src/controllers/TransactionController.php
// ============================================================

class TransactionController {
    private TransactionService $svc;
    private TransactionDAO     $dao;
    private BudgetService      $budgetSvc;

    public function __construct() {
        $this->svc       = new TransactionService();
        $this->dao       = new TransactionDAO();
        $this->budgetSvc = new BudgetService();
    }

    public function index(): void {
        Response::requireAuth();
        $userId = Session::userId();
        $debut  = $_GET['debut'] ?? date('Y-m') . '-01';
        $fin    = $_GET['fin']   ?? date('Y-m-d');
        $transactions = $this->dao->findByUserAndPeriode($userId, $debut, $fin);
        $catDao  = new CategorieDAO();
        $categories = $catDao->findAll($userId);
        $budgets    = $this->budgetSvc->getForUser($userId);
        require_once VIEWS . '/transactions/index.php';
    }

    public function store(): void {
        Response::requireAuth();
        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Response::json(['ok' => false, 'message' => 'CSRF invalide'], 403);
        }
        $v = new Validator();
        $v->required('type',        $_POST['type'] ?? '')
          ->inArray('type',         $_POST['type'] ?? '', ['REVENU','DEPENSE'])
          ->required('montant',     $_POST['montant'] ?? '')
          ->numeric('montant',      $_POST['montant'] ?? '')
          ->required('date_op',     $_POST['date_op'] ?? '')
          ->date('date_op',         $_POST['date_op'] ?? '')
          ->required('budget_id',   $_POST['budget_id'] ?? '')
          ->required('categorie_id',$_POST['categorie_id'] ?? '');

        if ($v->fails()) {
            Response::json(['ok' => false, 'errors' => $v->errors()], 422);
        }
        // Vérifier accès au budget
        if (!$this->budgetSvc->hasAccess((int)$_POST['budget_id'], Session::userId())) {
            Response::json(['ok' => false, 'message' => 'Accès refusé.'], 403);
        }
        $result = $this->svc->ajouter(Session::userId(), $_POST);
        Response::json($result, 201);
    }

    public function update(string $id): void {
        Response::requireAuth();
        $result = $this->svc->modifier((int)$id, Session::userId(), $_POST);
        Response::json($result);
    }

    public function destroy(string $id): void {
        Response::requireAuth();
        $result = $this->svc->supprimer((int)$id, Session::userId());
        Response::json($result);
    }
}


// ============================================================
// src/controllers/BudgetController.php
// ============================================================

class BudgetController {
    private BudgetService $svc;
    private BudgetDAO     $dao;
    private MembreBudgetDAO $membreDao;

    public function __construct() {
        $this->svc = new BudgetService();
        $this->dao = new BudgetDAO();
        $this->membreDao = new MembreBudgetDAO();
    }

    public function index(): void {
        Response::requireAuth();
        $budgets = $this->svc->getForUser(Session::userId());
        require_once VIEWS . '/budgets/index.php';
    }

    public function show(string $id): void {
        Response::requireAuth();
        if (!$this->svc->hasAccess((int)$id, Session::userId())) {
            Session::flash('error', 'Accès refusé.');
            Response::redirect(BASE_URL . '/budgets');
        }
        $budget    = $this->dao->findById((int)$id);
        $txDao     = new TransactionDAO();
        $transactions = $txDao->findByBudget((int)$id);
        $plafondsCats = $this->dao->getPlafondsCats((int)$id);
        $membreDao = new MembreBudgetDAO();
        $membres   = $membreDao->findByBudget((int)$id);
        require_once VIEWS . '/budgets/show.php';
    }

    public function store(): void {
        Response::requireAuth();
        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Response::json(['ok' => false, 'message' => 'CSRF invalide'], 403);
        }
        $v = new Validator();
        $v->required('nom',          $_POST['nom'] ?? '')
          ->required('plafond_global',$_POST['plafond_global'] ?? '')
          ->numeric('plafond_global', $_POST['plafond_global'] ?? '')
          ->required('periode',      $_POST['periode'] ?? '')
          ->inArray('periode',       $_POST['periode'] ?? '', ['MENSUEL','HEBDOMADAIRE','PERSONNALISE'])
          ->required('date_debut',   $_POST['date_debut'] ?? '')
          ->date('date_debut',       $_POST['date_debut'] ?? '');

        if ($v->fails()) {
            Response::json(['ok' => false, 'errors' => $v->errors()], 422);
        }
        $result = $this->svc->creer($_POST, Session::userId());
        if ($result['ok'] && !empty($_POST['emails_membres'])) {
            $emails = array_map('trim', explode(',', $_POST['emails_membres']));
            foreach ($emails as $email) {
                if (!empty($email)) {
                    $this->svc->inviterMembre((int)$result['budget_id'], $email, Session::userId());
                }
            }
        }
        Response::json($result, 201);
    }

    public function update(string $id): void {
        Response::requireAuth();
        if (!$this->svc->hasAccess((int)$id, Session::userId())) {
            Response::json(['ok' => false, 'message' => 'Accès refusé.'], 403);
        }
        $this->dao->update((int)$id, $_POST['nom'], (float)$_POST['plafond_global'], $_POST['periode']);
        Response::json(['ok' => true]);
    }

    public function destroy(string $id): void {
        Response::requireAuth();
        $this->dao->delete((int)$id);
        Response::json(['ok' => true]);
    }

    public function inviter(string $id): void {
        Response::requireAuth();
        $result = $this->svc->inviterMembre((int)$id, trim($_POST['email'] ?? ''), Session::userId());
        Response::json($result);
    }

    public function rejoindre(string $token): void {
        Response::requireAuth();
        $result = $this->svc->rejoindre($token, Session::userId());
        if ($result['ok']) {
            Session::flash('success', 'Vous avez rejoint le budget.');
            Response::redirect(BASE_URL . '/budgets/' . $result['budget_id']);
        } else {
            Session::flash('error', $result['message']);
            Response::redirect(BASE_URL . '/budgets');
        }
    }

    public function showInvitations(): void {
        Response::requireAuth();
        $invitations = $this->membreDao->findInvitationsByUser(Session::userId());
        require_once VIEWS . '/budgets/invitations.php';
    }

    public function accepterInvitation(string $id): void {
        Response::requireAuth();
        $inv = $this->membreDao->findById((int)$id);
        if (!$inv || (int)$inv['user_id'] !== Session::userId()) {
            Response::json(['ok' => false, 'message' => 'Invitation introuvable.'], 404);
        }
        $this->membreDao->accepterInvitation((int)$inv['budget_id'], Session::userId());
        Response::json(['ok' => true, 'message' => 'Invitation acceptée.']);
    }

    public function refuserInvitation(string $id): void {
        Response::requireAuth();
        $inv = $this->membreDao->findById((int)$id);
        if (!$inv || (int)$inv['user_id'] !== Session::userId()) {
            Response::json(['ok' => false, 'message' => 'Invitation introuvable.'], 404);
        }
        $this->membreDao->refuserInvitation((int)$inv['budget_id'], Session::userId());
        Response::json(['ok' => true, 'message' => 'Invitation refusée.']);
    }

    public function countInvitations(): void {
        Response::requireAuth();
        $count = $this->membreDao->countInvitationsByUser(Session::userId());
        Response::json(['count' => $count]);
    }
}


// ============================================================
// src/controllers/CategorieController.php
// ============================================================

class CategorieController {
    private CategorieDAO $dao;

    public function __construct() {
        $this->dao = new CategorieDAO();
    }

    public function index(): void {
        Response::requireAuth();
        $categories = $this->dao->findAll(Session::userId());
        require_once VIEWS . '/categories/index.php';
    }

    public function store(): void {
        Response::requireAuth();
        $v = new Validator();
        $v->required('nom', $_POST['nom'] ?? '');
        if ($v->fails()) {
            Response::json(['ok' => false, 'errors' => $v->errors()], 422);
        }
        $id = $this->dao->create(trim($_POST['nom']), trim($_POST['icone'] ?? 'tag'), Session::userId());
        Response::json(['ok' => true, 'id' => $id], 201);
    }

    public function update(string $id): void {
        Response::requireAuth();
        $this->dao->update((int)$id, trim($_POST['nom']), trim($_POST['icone'] ?? 'tag'));
        Response::json(['ok' => true]);
    }

    public function destroy(string $id): void {
        Response::requireAuth();
        $this->dao->delete((int)$id);
        Response::json(['ok' => true]);
    }

    public function list(): void {
        Response::requireAuth();
        $cats = $this->dao->findAll(Session::userId());
        Response::json($cats);
    }
}


// ============================================================
// src/controllers/AlerteController.php
// ============================================================

class AlerteController {
    private AlerteDAO $dao;

    public function __construct() {
        $this->dao = new AlerteDAO();
    }

    public function list(): void {
        Response::requireAuth();
        $alertes = $this->dao->findNonLuesByUser(Session::userId());
        Response::json($alertes);
    }

    public function marquerLue(string $id): void {
        Response::requireAuth();
        $this->dao->marquerLue((int)$id);
        Response::json(['ok' => true]);
    }

    public function marquerToutesLues(): void {
        Response::requireAuth();
        $this->dao->marquerToutesLues(Session::userId());
        Response::json(['ok' => true]);
    }
}


// ============================================================
// src/controllers/AdminController.php  — SC-05
// ============================================================

class AdminController {
    private AdminService $svc;

    public function __construct() {
        $this->svc = new AdminService();
    }

    public function dashboard(): void {
        Response::requireAdmin();
        $enAttente  = $this->svc->listerEnAttente();
        $userDao    = new UtilisateurDAO();
        $allUsers   = $userDao->findAll();
        $budgetDao  = new BudgetDAO();
        $allBudgets = $budgetDao->findAll();
        $stats      = $this->svc->statsGlobales();
        require_once VIEWS . '/admin/dashboard.php';
    }

    public function valider(string $id): void {
        Response::requireAdmin();
        $this->svc->validerCompte((int)$id);
        Response::json(['ok' => true, 'message' => 'Compte activé.']);
    }

    public function suspendre(string $id): void {
        Response::requireAdmin();
        $this->svc->suspendreCompte((int)$id);
        Response::json(['ok' => true]);
    }

    public function supprimer(string $id): void {
        Response::requireAdmin();
        $this->svc->supprimerCompte((int)$id);
        Response::json(['ok' => true]);
    }

    public function changerRole(string $id): void {
        Response::requireAdmin();
        $this->svc->changerRole((int)$id, $_POST['role'] ?? 'UTILISATEUR');
        Response::json(['ok' => true]);
    }
}
