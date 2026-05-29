<?php
$pageTitle = 'Mes invitations — BudgetCollab';
require_once VIEWS . '/partials/header.php';
?>
<div class="page-header">
  <div>
    <h1 class="page-title"><i data-lucide="mail"></i> Mes invitations</h1>
    <p class="page-sub"><?= count($invitations) ?> invitation(s) en attente</p>
  </div>
  <a href="<?= BASE_URL ?>/budgets" class="btn btn-outline btn-sm"><i data-lucide="arrow-left"></i> Retour aux budgets</a>
</div>

<?php if (empty($invitations)): ?>
  <div class="empty-state">
    <i data-lucide="mail" style="width:48px;height:48px;color:var(--text3)"></i>
    <p>Aucune invitation en attente.</p>
  </div>
<?php else: ?>
<div class="invitations-list">
  <?php foreach ($invitations as $inv): ?>
  <div class="invitation-card" id="invitation-<?= $inv['id'] ?>">
    <div class="invitation-icon">
      <i data-lucide="user-plus"></i>
    </div>
    <div class="invitation-body">
      <div class="invitation-title">
        <strong><?= htmlspecialchars($inv['budget_nom']) ?></strong>
        <span class="badge badge-<?= $inv['budget_type']==='PARTAGE'?'teal':'blue' ?>"><?= $inv['budget_type'] ?></span>
      </div>
      <div class="invitation-sub">
        Invité par <strong><?= htmlspecialchars($inv['proprio_prenom'] . ' ' . $inv['proprio_nom']) ?></strong>
        — <?= date('d/m/Y', strtotime($inv['created_at'])) ?>
      </div>
    </div>
    <div class="invitation-actions">
      <button class="btn btn-sm btn-success" onclick="accepterInvitation(<?= $inv['id'] ?>)">
        <i data-lucide="check"></i> Accepter
      </button>
      <button class="btn btn-sm btn-outline" onclick="refuserInvitation(<?= $inv['id'] ?>)">
        <i data-lucide="x"></i> Refuser
      </button>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= Session::generateCsrf() ?>';
</script>
<?php require_once VIEWS . '/partials/footer.php'; ?>
