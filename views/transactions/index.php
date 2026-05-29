<?php
$pageTitle = 'Transactions — BudgetCollab';
require_once VIEWS . '/partials/header.php';
?>
<div class="page-header">
  <div>
    <h1 class="page-title">Mes transactions</h1>
    <p class="page-sub"><?= count($transactions) ?> transaction(s)</p>
  </div>
  <div class="header-actions">
    <form method="GET" class="filter-form">
      <input type="date" name="debut" value="<?= $debut ?>" class="input-sm">
      <span>→</span>
      <input type="date" name="fin" value="<?= $fin ?>" class="input-sm">
      <button type="submit" class="btn btn-outline btn-sm"><i data-lucide="filter"></i></button>
    </form>
    <button class="btn btn-primary" onclick="openModal('modalAddTx')">
      <i data-lucide="plus"></i> Ajouter
    </button>
  </div>
</div>

<div class="table-wrapper">
  <table class="data-table" id="txTable">
    <thead><tr>
      <th>Date</th>
      <th>Type</th>
      <th>Description</th>
      <th>Catégorie</th>
      <th>Budget</th>
      <th>Montant</th>
      <th>Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($transactions as $tx): ?>
      <tr id="tx-<?= $tx['id'] ?>">
        <td><?= date('d/m/Y', strtotime($tx['date_op'])) ?></td>
        <td>
          <span class="badge badge-<?= $tx['type']==='REVENU'?'green':'red' ?>">
            <?= $tx['type'] ?>
          </span>
        </td>
        <td><?= htmlspecialchars($tx['description'] ?: '—') ?></td>
        <td>
          <span class="cat-pill">
            <i data-lucide="<?= htmlspecialchars($tx['categorie_icone']) ?>"></i>
            <?= htmlspecialchars($tx['categorie_nom']) ?>
          </span>
        </td>
        <td><?= htmlspecialchars($tx['budget_nom']) ?></td>
        <td class="tx-amount <?= $tx['type']==='REVENU'?'tx-pos':'tx-neg' ?>">
          <?= $tx['type']==='REVENU'?'+':'-' ?><?= number_format($tx['montant'],2,',',' ') ?> DA
        </td>
        <td class="actions">
          <button class="btn-icon" title="Modifier"
            onclick="editTx(<?= $tx['id'] ?>, '<?= $tx['type'] ?>',<?= $tx['montant'] ?>,'<?= $tx['date_op'] ?>','<?= addslashes($tx['description']) ?>',<?= $tx['categorie_id'] ?>,<?= $tx['budget_id'] ?>)">
            <i data-lucide="edit-2"></i>
          </button>
          <button class="btn-icon btn-icon-red" title="Supprimer"
            onclick="deleteTx(<?= $tx['id'] ?>)">
            <i data-lucide="trash-2"></i>
          </button>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($transactions)): ?>
      <tr><td colspan="7" class="empty-row">Aucune transaction trouvée.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Ajout -->
<?php $data = ['budgets' => $budgets]; require_once VIEWS . '/transactions/_modal_add.php'; ?>

<!-- Modal Modification -->
<div class="modal-overlay" id="modalEditTx">
  <div class="modal">
    <div class="modal-header">
      <h3><i data-lucide="edit-2"></i> Modifier la transaction</h3>
      <button class="modal-close" onclick="closeModal('modalEditTx')"><i data-lucide="x"></i></button>
    </div>
    <form id="formEditTx" class="modal-body">
      <input type="hidden" id="editTxId">
      <div class="type-toggle">
        <label class="type-btn type-depense" id="editTypeDepense">
          <input type="radio" name="type" value="DEPENSE"> <i data-lucide="trending-down"></i> Dépense
        </label>
        <label class="type-btn type-revenu" id="editTypeRevenu">
          <input type="radio" name="type" value="REVENU"> <i data-lucide="trending-up"></i> Revenu
        </label>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label>Montant (DA)</label>
          <input type="number" id="editMontant" name="montant" step="0.01" min="0.01" required>
        </div>
        <div class="field-group">
          <label>Date</label>
          <input type="date" id="editDate" name="date_op" required>
        </div>
      </div>
      <div class="field-group">
        <label>Description</label>
        <input type="text" id="editDesc" name="description">
      </div>
      <div class="field-group">
        <label>Catégorie</label>
        <select id="editCategorie" name="categorie_id" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEditTx')">Annuler</button>
        <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= Session::generateCsrf() ?>';
</script>
<?php require_once VIEWS . '/partials/footer.php'; ?>
