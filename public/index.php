<?php
// ============================================================
// public/index.php — Front Controller
// ============================================================

define('ROOT',  dirname(__DIR__));
define('VIEWS', ROOT . '/views');

require_once ROOT . '/config/config.php';
require_once ROOT . '/src/core/Core.php';
require_once ROOT . '/src/core/Database.php';
require_once ROOT . '/src/dao/AllDAO.php';
require_once ROOT . '/src/services/AllServices.php';
require_once ROOT . '/src/controllers/AllControllers.php';

Session::start();

// ============================================================
// ROUTEUR
// ============================================================
$router = new Router();

// Auth
$router->get('/login',    fn() => (new AuthController())->showLogin());
$router->post('/login',   fn() => (new AuthController())->login());
$router->get('/register', fn() => (new AuthController())->showRegister());
$router->post('/register',fn() => (new AuthController())->register());
$router->get('/logout',   fn() => (new AuthController())->logout());
$router->get('/profil',   fn() => (new AuthController())->showProfil());
$router->post('/profil',  fn() => (new AuthController())->updateProfil());
$router->post('/profil/password', fn() => (new AuthController())->changePassword());

// Dashboard
$router->get('/dashboard', fn() => (new DashboardController())->index());
$router->get('/',          fn() => (new DashboardController())->index());

// Transactions
$router->get('/transactions',                fn() => (new TransactionController())->index());
$router->post('/transactions',               fn() => (new TransactionController())->store());
$router->post('/transactions/{id}/update',   fn($id) => (new TransactionController())->update($id));
$router->post('/transactions/{id}/delete',   fn($id) => (new TransactionController())->destroy($id));

// Budgets
$router->get('/budgets',                      fn() => (new BudgetController())->index());
$router->post('/budgets',                     fn() => (new BudgetController())->store());
$router->get('/budgets/{id}',                 fn($id) => (new BudgetController())->show($id));
$router->post('/budgets/{id}/update',         fn($id) => (new BudgetController())->update($id));
$router->post('/budgets/{id}/delete',         fn($id) => (new BudgetController())->destroy($id));
$router->post('/budgets/{id}/inviter',        fn($id) => (new BudgetController())->inviter($id));
$router->get('/budgets/rejoindre/{token}',    fn($token) => (new BudgetController())->rejoindre($token));
$router->get('/mes-invitations',              fn() => (new BudgetController())->showInvitations());
$router->post('/invitations/{id}/accepter',   fn($id) => (new BudgetController())->accepterInvitation($id));
$router->post('/invitations/{id}/refuser',    fn($id) => (new BudgetController())->refuserInvitation($id));
$router->get('/api/invitations/count',        fn() => (new BudgetController())->countInvitations());

// Catégories
$router->get('/categories',               fn() => (new CategorieController())->index());
$router->post('/categories',              fn() => (new CategorieController())->store());
$router->post('/categories/{id}/update',  fn($id) => (new CategorieController())->update($id));
$router->post('/categories/{id}/delete',  fn($id) => (new CategorieController())->destroy($id));
$router->get('/api/categories',           fn() => (new CategorieController())->list());

// Alertes
$router->get('/api/alertes',                  fn() => (new AlerteController())->list());
$router->post('/api/alertes/{id}/lue',        fn($id) => (new AlerteController())->marquerLue($id));
$router->post('/api/alertes/tout-lire',       fn() => (new AlerteController())->marquerToutesLues());

// Admin
$router->get('/admin',                        fn() => (new AdminController())->dashboard());
$router->post('/admin/comptes/{id}/valider',  fn($id) => (new AdminController())->valider($id));
$router->post('/admin/comptes/{id}/suspendre',fn($id) => (new AdminController())->suspendre($id));
$router->post('/admin/comptes/{id}/supprimer',fn($id) => (new AdminController())->supprimer($id));
$router->post('/admin/comptes/{id}/role',     fn($id) => (new AdminController())->changerRole($id));

$router->dispatch();
