<?php // views/partials/layout.php — utilisé par toutes les vues ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'BudgetCollab' ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="<?= Session::isLogged() ? 'has-sidebar' : '' ?>">

<?php if (Session::isLogged()): ?>
<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <span class="logo">💰 BudgetCollab</span>
    <button class="sidebar-toggle" id="sidebarToggle"><i data-lucide="menu"></i></button>
  </div>

  <nav class="sidebar-nav">
    <a href="<?= BASE_URL ?>/dashboard"    class="nav-item <?= str_contains($_SERVER['REQUEST_URI'],'/dashboard') ? 'active':'' ?>">
      <i data-lucide="layout-dashboard"></i><span>Tableau de bord</span></a>

    <a href="<?= BASE_URL ?>/mes-invitations" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'],'/mes-invitations') ? 'active':'' ?>">
      <i data-lucide="mail"></i><span>Invitations <span class="badge" id="sidebarInvBadge" style="display:none">0</span></span></a>

    <a href="<?= BASE_URL ?>/transactions" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'],'/transactions') ? 'active':'' ?>">
      <i data-lucide="arrow-left-right"></i><span>Transactions</span></a>

    <a href="<?= BASE_URL ?>/budgets"      class="nav-item <?= str_contains($_SERVER['REQUEST_URI'],'/budgets') ? 'active':'' ?>">
      <i data-lucide="wallet"></i><span>Budgets</span></a>

    <a href="<?= BASE_URL ?>/categories"   class="nav-item <?= str_contains($_SERVER['REQUEST_URI'],'/categories') ? 'active':'' ?>">
      <i data-lucide="tag"></i><span>Catégories</span></a>

    <?php if (Session::isAdmin()): ?>
    <div class="nav-divider"></div>
    <a href="<?= BASE_URL ?>/admin" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'],'/admin') ? 'active':'' ?>">
      <i data-lucide="shield"></i><span>Administration</span></a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= BASE_URL ?>/profil" class="nav-item">
      <i data-lucide="user"></i><span><?= htmlspecialchars(Session::get('user_nom')) ?></span></a>
    <a href="<?= BASE_URL ?>/logout" class="nav-item nav-logout">
      <i data-lucide="log-out"></i><span>Déconnexion</span></a>
  </div>
</aside>
<?php endif; ?>

<!-- ── MAIN CONTENT ── -->
<main class="main-content" id="mainContent">

<?php if (Session::isLogged()): ?>
<!-- Top bar -->
<header class="topbar">
  <button class="topbar-menu-btn" id="topbarMenu"><i data-lucide="menu"></i></button>
  <div class="topbar-right">
    <!-- Invitations -->
    <a href="<?= BASE_URL ?>/mes-invitations" class="topbar-icon" id="invitationBell" title="Mes invitations">
      <i data-lucide="mail"></i>
      <span class="badge" id="invitationBadge" style="display:none">0</span>
    </a>

    <!-- Cloche alertes -->
    <div class="alert-bell" id="alertBell">
      <i data-lucide="bell"></i>
      <span class="badge" id="alertBadge" style="display:none">0</span>
      <div class="alert-dropdown" id="alertDropdown"></div>
    </div>
    <span class="topbar-user"><?= htmlspecialchars(Session::get('user_nom')) ?></span>
  </div>
</header>
<?php endif; ?>

<!-- Flash messages -->
<?php foreach (Session::getFlash() as $flash): ?>
  <div class="flash flash-<?= $flash['type'] ?>">
    <?= htmlspecialchars($flash['message']) ?>
    <button onclick="this.parentElement.remove()">✕</button>
  </div>
<?php endforeach; ?>
