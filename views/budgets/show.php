<?php
// views/budgets/show.php — Détail d'un budget
$pageTitle = htmlspecialchars($budget['nom']) . ' — BudgetCollab';
require_once VIEWS . '/partials/header.php';
?>

<div class="page-header">
  <div>
    <a href="<?= BASE_URL ?>/budgets" class="breadcrumb">← Retour</a>
    <h1 class="page-title"><?= htmlspecialchars($budget['nom']) ?></h1>
    <div class="budget-header-meta">
      <span class="badge badge-<?= $budget['type']==='PARTAGE'?'teal':'blue' ?>"><?= $budget['type'] ?></span>
      <span class="badge badge-gray"><?= $budget['periode'] ?></span>
      <span class="badge badge-gray">Créé le <?= date('d/m/Y', strtotime($budget['created_at'])) ?></span>
    </div>
  </div>
  <div class="header-actions">
    <button class="btn btn-outline" onclick="openModal('modalEditBudget')">
      <i data-lucide="edit-2"></i> Modifier
    </button>
  </div>
</div>

<!-- Budget Summary -->
<div class="budget-summary-card">
  <div class="summary-row">
    <div class="summary-stat">
      <span class="stat-label">Plafond global</span>
      <span class="stat-value"><?= number_format($budget['plafond_global'],2,',',' ') ?> DA</span>
    </div>
    <div class="summary-stat">
      <span class="stat-label">Consommé</span>
      <span class="stat-value" style="color: var(--red)"><?= number_format($budget['montant_consomme'],2,',',' ') ?> DA</span>
    </div>
    <div class="summary-stat">
      <span class="stat-label">Solde disponible</span>
      <span class="stat-value" style="color: var(--green)"><?= number_format($budget['plafond_global'] - $budget['montant_consomme'],2,',',' ') ?> DA</span>
    </div>
    <div class="summary-stat">
      <span class="stat-label">Taux d'utilisation</span>
      <span class="taux-badge taux-<?= $budget['montant_consomme'] >= $budget['plafond_global']?'red':($budget['montant_consomme'] >= $budget['plafond_global']*0.8?'orange':'green') ?>" style="font-size:18px">
        <?php $taux = $budget['plafond_global'] > 0 ? round(($budget['montant_consomme'] / $budget['plafond_global']) * 100, 1) : 0; echo $taux; ?>%
      </span>
    </div>
  </div>

  <div class="budget-progress-bar" style="margin-top:20px">
    <div class="progress-fill progress-<?= $taux>=100?'red':($taux>=80?'orange':'green') ?>"
         style="width:<?= min($taux,100) ?>%"></div>
  </div>
</div>

<!-- Tabs: Transactions, Plafonds, Membres -->
<div class="tabs-container">
  <div class="tabs-header">
    <button class="tab-btn active" data-tab="transactions">
      <i data-lucide="arrow-left-right"></i> Transactions (<?= count($transactions) ?>)
    </button>
    <button class="tab-btn" data-tab="plafonds">
      <i data-lucide="settings"></i> Plafonds par catégorie
    </button>
    <?php if ($budget['type']==='PARTAGE'): ?>
    <button class="tab-btn" data-tab="membres">
      <i data-lucide="users"></i> Membres (<?= count($membres) ?>)
    </button>
    <?php endif; ?>
  </div>

  <!-- Tab: Transactions -->
  <div class="tab-content active" id="tab-transactions">
    <div class="table-wrapper">
      <table class="data-table">
        <thead><tr>
          <th>Date</th>
          <th>Utilisateur</th>
          <th>Description</th>
          <th>Catégorie</th>
          <th>Type</th>
          <th>Montant</th>
        </tr></thead>
        <tbody>
        <?php foreach ($transactions as $tx): ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($tx['date_op'])) ?></td>
            <td><?= htmlspecialchars($tx['user_nom'] . ' ' . $tx['user_prenom']) ?></td>
            <td><?= htmlspecialchars($tx['description'] ?: '—') ?></td>
            <td><?= htmlspecialchars($tx['categorie_nom']) ?></td>
            <td><span class="badge badge-<?= $tx['type']==='REVENU'?'green':'red' ?>"><?= $tx['type'] ?></span></td>
            <td class="tx-amount <?= $tx['type']==='REVENU'?'tx-pos':'tx-neg' ?>">
              <?= $tx['type']==='REVENU'?'+':'-' ?><?= number_format($tx['montant'],2,',',' ') ?> DA
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($transactions)): ?>
          <tr><td colspan="6" class="empty-row">Aucune transaction pour ce budget.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Tab: Plafonds par catégorie -->
  <div class="tab-content" id="tab-plafonds">
    <div class="plafonds-grid">
    <?php foreach ($plafondsCats as $pc): ?>
      <div class="plafond-card">
        <div class="plafond-header">
          <h4><?= htmlspecialchars($pc['nom']) ?></h4>
          <span class="plafond-max"><?= number_format($pc['plafond'],2,',',' ') ?> DA</span>
        </div>
        <div class="budget-progress-bar">
          <div class="progress-fill progress-<?= $pc['montant_consomme'] >= $pc['plafond']?'red':'green' ?>"
               style="width:<?= min(($pc['montant_consomme']/$pc['plafond'])*100,100) ?>%"></div>
        </div>
        <span class="plafond-consumed"><?= number_format($pc['montant_consomme'],2,',',' ') ?> / <?= number_format($pc['plafond'],2,',',' ') ?> DA</span>
      </div>
    <?php endforeach; ?>
    </div>
  </div>

  <!-- Tab: Membres (si partagé) -->
  <?php if ($budget['type']==='PARTAGE'): ?>
  <div class="tab-content" id="tab-membres">
    <div class="membres-management">
      <button class="btn btn-outline" onclick="openModal('modalInviterMembre')">
        <i data-lucide="user-plus"></i> Inviter un membre
      </button>

      <div class="membres-list-full">
      <?php foreach ($membres as $m): ?>
        <div class="membre-item">
          <div class="membre-avatar"><?= strtoupper(substr($m['prenom'],0,1).substr($m['nom'],0,1)) ?></div>
          <div class="membre-info">
            <div class="membre-name"><?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></div>
            <div class="membre-email"><?= htmlspecialchars($m['email']) ?></div>
          </div>
          <div class="membre-role">
            <span class="badge badge-<?= $m['role_budget']==='PROPRIETAIRE'?'purple':'blue' ?>">
              <?= $m['role_budget'] ?>
            </span>
          </div>
          <?php if ($m['role_budget'] !== 'PROPRIETAIRE'): ?>
          <button class="btn-icon btn-icon-red" onclick="removeMembre(<?= $m['user_id'] ?>)">
            <i data-lucide="trash-2"></i>
          </button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Modal Inviter -->
  <div class="modal-overlay" id="modalInviterMembre">
    <div class="modal">
      <div class="modal-header">
        <h3><i data-lucide="user-plus"></i> Inviter un membre</h3>
        <button class="modal-close" onclick="closeModal('modalInviterMembre')"><i data-lucide="x"></i></button>
      </div>
      <form id="formInviterMembre" class="modal-body">
        <div class="field-group">
          <label>Adresse email</label>
          <div class="input-icon">
            <i data-lucide="mail"></i>
            <input type="email" name="email" placeholder="utilisateur@exemple.com" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal('modalInviterMembre')">Annuler</button>
          <button type="submit" class="btn btn-primary"><i data-lucide="send"></i> Envoyer l'invitation</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Modifier Budget -->
<div class="modal-overlay" id="modalEditBudget">
  <div class="modal">
    <div class="modal-header">
      <h3><i data-lucide="edit-2"></i> Modifier le budget</h3>
      <button class="modal-close" onclick="closeModal('modalEditBudget')"><i data-lucide="x"></i></button>
    </div>
    <form id="formEditBudget" class="modal-body">
      <div class="field-group">
        <label>Nom</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($budget['nom']) ?>" required>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label>Plafond global (DA)</label>
          <input type="number" name="plafond_global" step="0.01" value="<?= $budget['plafond_global'] ?>" required>
        </div>
        <div class="field-group">
          <label>Période</label>
          <select name="periode" required>
            <option value="MENSUEL" <?= $budget['periode']==='MENSUEL'?'selected':'' ?>>Mensuel</option>
            <option value="HEBDOMADAIRE" <?= $budget['periode']==='HEBDOMADAIRE'?'selected':'' ?>>Hebdomadaire</option>
            <option value="PERSONNALISE" <?= $budget['periode']==='PERSONNALISE'?'selected':'' ?>>Personnalisé</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEditBudget')">Annuler</button>
        <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= Session::generateCsrf() ?>';
const BUDGET_ID = <?= $budget['id'] ?>;
</script>

<?php require_once VIEWS . '/partials/footer.php'; ?>
