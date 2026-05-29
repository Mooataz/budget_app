<?php
// views/budgets/index.php
$pageTitle = 'Budgets — BudgetCollab';
require_once VIEWS . '/partials/header.php';
?>
<div class="page-header">
  <div>
    <h1 class="page-title">Mes budgets</h1>
    <p class="page-sub"><?= count($budgets) ?> budget(s)</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('modalAddBudget')">
    <i data-lucide="plus"></i> Nouveau budget
  </button>
</div>

<div class="budgets-full-grid">
<?php foreach ($budgets as $b): ?>
  <div class="budget-full-card">
    <div class="budget-full-header">
      <div>
        <h3><?= htmlspecialchars($b['nom']) ?></h3>
        <div class="budget-meta">
          <span class="badge badge-<?= $b['type']==='PARTAGE'?'teal':'blue' ?>"><?= $b['type'] ?></span>
          <span class="badge badge-gray"><?= $b['periode'] ?></span>
        </div>
      </div>
      <div class="budget-card-actions">
        <a href="<?= BASE_URL ?>/budgets/<?= $b['id'] ?>" class="btn btn-outline btn-sm">Détails</a>
        <button class="btn-icon btn-icon-red" onclick="deleteBudget(<?= $b['id'] ?>)">
          <i data-lucide="trash-2"></i>
        </button>
      </div>
    </div>

    <!-- Barre de progression -->
    <div class="budget-progress-section">
      <div class="progress-header">
        <span><?= number_format($b['montant_consomme'],0,',',' ') ?> DA</span>
        <span class="taux-badge taux-<?= $b['taux']>=100?'red':($b['taux']>=80?'orange':'green') ?>"><?= $b['taux'] ?>%</span>
        <span><?= number_format($b['plafond_global'],0,',',' ') ?> DA</span>
      </div>
      <div class="budget-progress-bar">
        <div class="progress-fill progress-<?= $b['taux']>=100?'red':($b['taux']>=80?'orange':'green') ?>"
             style="width:<?= min($b['taux'],100) ?>%"></div>
      </div>
      <div class="progress-footer">
        <span>Solde disponible : <strong><?= number_format($b['solde'],0,',',' ') ?> DA</strong></span>
        <span class="indicator indicator-<?= $b['taux']>=100?'red':($b['taux']>=80?'orange':'green') ?>">
          <?= $b['taux']>=100?'⛔ Dépassé':($b['taux']>=80?'⚠️ Proche limite':'✅ Maîtrisé') ?>
        </span>
      </div>
    </div>

    <!-- Membres (si partagé) -->
    <?php if ($b['type']==='PARTAGE' && !empty($b['membres'])): ?>
    <div class="membres-list">
      <?php foreach ($b['membres'] as $m): ?>
        <span class="membre-avatar" title="<?= htmlspecialchars($m['prenom'].' '.$m['nom']) ?>">
          <?= strtoupper(substr($m['prenom'],0,1).substr($m['nom'],0,1)) ?>
        </span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<?php if (empty($budgets)): ?>
  <div class="empty-state">
    <i data-lucide="wallet" style="width:48px;height:48px;color:var(--text3)"></i>
    <p>Aucun budget. Créez votre premier budget !</p>
  </div>
<?php endif; ?>
</div>

<!-- Modal Nouveau Budget -->
<div class="modal-overlay" id="modalAddBudget">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3><i data-lucide="wallet"></i> Nouveau budget</h3>
      <button class="modal-close" onclick="closeModal('modalAddBudget')"><i data-lucide="x"></i></button>
    </div>
    <form id="formAddBudget" class="modal-body">
      <!-- Type de budget -->
      <div class="type-toggle">
        <label class="type-btn active" data-type="INDIVIDUEL">
          <input type="radio" name="type" value="INDIVIDUEL" checked>
          <i data-lucide="user"></i> Individuel
        </label>
        <label class="type-btn" data-type="PARTAGE">
          <input type="radio" name="type" value="PARTAGE">
          <i data-lucide="users"></i> Partagé
        </label>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label>Nom du budget</label>
          <input type="text" name="nom" placeholder="Ex: Budget ménage" required>
        </div>
        <div class="field-group">
          <label>Plafond global (DA)</label>
          <input type="number" name="plafond_global" step="0.01" min="1" placeholder="50000" required>
        </div>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label>Période</label>
          <select name="periode" required>
            <option value="MENSUEL">Mensuel</option>
            <option value="HEBDOMADAIRE">Hebdomadaire</option>
            <option value="PERSONNALISE">Personnalisé</option>
          </select>
        </div>
        <div class="field-group">
          <label>Date de début</label>
          <input type="date" name="date_debut" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <!-- Invitation membres (partagé) -->
      <div id="membresSection" style="display:none">
        <label>Inviter des membres (emails)</label>
        <div class="email-tags" id="emailTags"></div>
        <div class="input-icon">
          <i data-lucide="mail"></i>
          <input type="email" id="emailInput" placeholder="email@exemple.com" class="input-email-tag">
          <button type="button" onclick="addEmailTag()">Ajouter</button>
        </div>
        <input type="hidden" name="emails_membres" id="emailsMembres">
      </div>

      <div class="field-error" id="budgetError"></div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalAddBudget')">Annuler</button>
        <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Créer</button>
      </div>
    </form>
  </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= Session::generateCsrf() ?>';
</script>
<?php require_once VIEWS . '/partials/footer.php'; ?>
