<?php
// views/admin/dashboard.php
$pageTitle = 'Administration — BudgetCollab';
require_once VIEWS . '/partials/header.php';
?>

<div class="page-header">
  <h1 class="page-title"><i data-lucide="shield"></i> Panneau d'administration</h1>
</div>

<!-- Stats Globales -->
<div class="kpi-grid">
  <div class="kpi-card kpi-blue">
    <div class="kpi-icon"><i data-lucide="users"></i></div>
    <div class="kpi-body">
      <span class="kpi-label">Utilisateurs</span>
      <span class="kpi-value"><?= count($allUsers) ?></span>
    </div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-icon"><i data-lucide="trending-up"></i></div>
    <div class="kpi-body">
      <span class="kpi-label">Revenus totaux</span>
      <span class="kpi-value"><?= number_format($stats['rev'],0,',',' ') ?> DA</span>
    </div>
  </div>
  <div class="kpi-card kpi-red">
    <div class="kpi-icon"><i data-lucide="trending-down"></i></div>
    <div class="kpi-body">
      <span class="kpi-label">Dépenses totales</span>
      <span class="kpi-value"><?= number_format($stats['dep'],0,',',' ') ?> DA</span>
    </div>
  </div>
  <div class="kpi-card kpi-purple">
    <div class="kpi-icon"><i data-lucide="wallet"></i></div>
    <div class="kpi-body">
      <span class="kpi-label">Budgets</span>
      <span class="kpi-value"><?= $stats['bgets'] ?></span>
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="tabs-container" style="margin-top:30px">
  <div class="tabs-header">
    <button class="tab-btn active" data-tab="en-attente">
      <i data-lucide="clock"></i> Comptes en attente (<?= count($enAttente) ?>)
    </button>
    <button class="tab-btn" data-tab="tous-comptes">
      <i data-lucide="users"></i> Tous les utilisateurs (<?= count($allUsers) ?>)
    </button>
    <button class="tab-btn" data-tab="budgets-partages">
      <i data-lucide="share-2"></i> Budgets partagés (<?= count($allBudgets) ?>)
    </button>
  </div>

  <!-- Tab: Comptes en attente -->
  <div class="tab-content active" id="tab-en-attente">
    <?php if (empty($enAttente)): ?>
      <div class="empty-state">
        <i data-lucide="check-circle"></i>
        <p>Aucun compte en attente de validation</p>
      </div>
    <?php else: ?>
    <div class="table-wrapper">
      <table class="data-table">
        <thead><tr>
          <th>Nom</th><th>Email</th><th>Date d'inscription</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($enAttente as $user): ?>
          <tr id="user-<?= $user['id'] ?>">
            <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
            <td class="actions">
              <button class="btn btn-sm btn-success" onclick="validerCompte(<?= $user['id'] ?>)">
                <i data-lucide="check"></i> Valider
              </button>
              <button class="btn btn-sm btn-outline" onclick="refuserCompte(<?= $user['id'] ?>)">
                <i data-lucide="x"></i> Refuser
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Tab: Tous les utilisateurs -->
  <div class="tab-content" id="tab-tous-comptes">
    <div class="table-wrapper">
      <table class="data-table">
        <thead><tr>
          <th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Dernière connexion</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($allUsers as $user): ?>
          <tr id="user-<?= $user['id'] ?>">
            <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
              <select class="select-inline" onchange="changerRole(<?= $user['id'] ?>, this.value)">
                <option value="UTILISATEUR" <?= $user['role']==='UTILISATEUR'?'selected':'' ?>>Utilisateur</option>
                <option value="ADMINISTRATEUR" <?= $user['role']==='ADMINISTRATEUR'?'selected':'' ?>>Admin</option>
              </select>
            </td>
            <td><span class="badge badge-<?= $user['statut']==='ACTIF'?'green':'red' ?>"><?= $user['statut'] ?></span></td>
            <td><?= $user['derniere_connexion'] ? date('d/m/Y H:i', strtotime($user['derniere_connexion'])) : '—' ?></td>
            <td class="actions">
              <?php if ($user['statut']==='ACTIF'): ?>
                <button class="btn-icon" onclick="suspendreCompte(<?= $user['id'] ?>, '<?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?>')">
                  <i data-lucide="pause-circle"></i>
                </button>
              <?php else: ?>
                <button class="btn-icon" onclick="activerCompte(<?= $user['id'] ?>)">
                  <i data-lucide="play-circle"></i>
                </button>
              <?php endif; ?>
              <button class="btn-icon btn-icon-red" onclick="supprimerCompte(<?= $user['id'] ?>, '<?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?>')">
                <i data-lucide="trash-2"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Tab: Budgets partagés -->
  <div class="tab-content" id="tab-budgets-partages">
    <div class="table-wrapper">
      <table class="data-table">
        <thead><tr>
          <th>Budget</th><th>Propriétaire</th><th>Membres</th><th>Total des transactions</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($allBudgets as $budget): ?>
          <tr>
            <td><?= htmlspecialchars($budget['nom']) ?></td>
            <td><?= htmlspecialchars($budget['prenom'] . ' ' . $budget['nom']) ?></td>
            <td><span class="badge badge-purple">N/A</span></td>
            <td>N/A</td>
            <td class="actions">
              <a href="<?= BASE_URL ?>/budgets/<?= $budget['id'] ?>" class="btn-icon" title="Voir">
                <i data-lucide="eye"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= Session::generateCsrf() ?>';
</script>

<?php require_once VIEWS . '/partials/footer.php'; ?>
