<?php
$pageTitle = 'Tableau de bord — BudgetCollab';
require_once VIEWS . '/partials/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Tableau de bord</h1>
    <p class="page-sub">Bonjour, <?= htmlspecialchars(Session::get('user_nom')) ?> 👋</p>
  </div>
  <div class="header-actions">
    <form method="GET" action="<?= BASE_URL ?>/dashboard" class="period-form" id="periodForm">
      <select name="periode" onchange="this.form.submit()" class="select-period">
        <option value="semaine" <?= ($_GET['periode']??'mois')==='semaine'?'selected':'' ?>>Cette semaine</option>
        <option value="mois"    <?= ($_GET['periode']??'mois')==='mois'   ?'selected':'' ?>>Ce mois</option>
        <option value="annee"   <?= ($_GET['periode']??'mois')==='annee'  ?'selected':'' ?>>Cette année</option>
      </select>
    </form>
    <button class="btn btn-primary" onclick="openModal('modalAddTx')">
      <i data-lucide="plus"></i> Nouvelle transaction
    </button>
  </div>
</div>

<!-- ── KPI CARDS ── -->
<div class="kpi-grid">
  <div class="kpi-card kpi-green">
    <div class="kpi-icon"><i data-lucide="trending-up"></i></div>
    <div class="kpi-body">
      <span class="kpi-label">Revenus</span>
      <span class="kpi-value"><?= number_format($data['total_revenus'],2,',',' ') ?> DA</span>
    </div>
  </div>
  <div class="kpi-card kpi-red">
    <div class="kpi-icon"><i data-lucide="trending-down"></i></div>
    <div class="kpi-body">
      <span class="kpi-label">Dépenses</span>
      <span class="kpi-value"><?= number_format($data['total_depenses'],2,',',' ') ?> DA</span>
    </div>
  </div>
  <div class="kpi-card <?= $data['solde'] >= 0 ? 'kpi-blue' : 'kpi-orange' ?>">
    <div class="kpi-icon"><i data-lucide="wallet"></i></div>
    <div class="kpi-body">
      <span class="kpi-label">Solde</span>
      <span class="kpi-value"><?= number_format($data['solde'],2,',',' ') ?> DA</span>
    </div>
  </div>
  <div class="kpi-card kpi-purple">
    <div class="kpi-icon"><i data-lucide="bell"></i></div>
    <div class="kpi-body">
      <span class="kpi-label">Alertes actives</span>
      <span class="kpi-value"><?= count($data['alertes']) ?></span>
    </div>
  </div>
</div>

<!-- ── ALERTES ── -->
<?php foreach ($data['alertes'] as $alerte): ?>
<div class="alert-banner alert-<?= strtolower($alerte['niveau']) ?>" id="alerte-<?= $alerte['id'] ?>">
  <i data-lucide="<?= $alerte['niveau']==='CRITIQUE'?'alert-triangle':'alert-circle' ?>"></i>
  <strong><?= htmlspecialchars($alerte['budget_nom']) ?></strong> — <?= htmlspecialchars($alerte['message']) ?>
  <button onclick="dismissAlerte(<?= $alerte['id'] ?>)">✕</button>
</div>
<?php endforeach; ?>

<!-- ── GRAPHIQUES ── -->
<div class="charts-grid">
  <!-- Évolution -->
  <div class="chart-card chart-wide">
    <div class="chart-header">
      <h2><i data-lucide="line-chart"></i> Évolution des dépenses</h2>
    </div>
    <canvas id="chartEvolution" height="100"></canvas>
  </div>

  <!-- Répartition -->
  <div class="chart-card">
    <div class="chart-header">
      <h2><i data-lucide="pie-chart"></i> Répartition par catégorie</h2>
    </div>
    <canvas id="chartRepartition" height="200"></canvas>
  </div>
</div>

<!-- ── BUDGETS ── -->
<div class="section-header">
  <h2><i data-lucide="wallet"></i> Mes budgets</h2>
  <a href="<?= BASE_URL ?>/budgets" class="btn btn-outline btn-sm">Voir tout</a>
</div>
<div class="budgets-grid">
<?php foreach (array_slice($data['budgets'], 0, 4) as $b): ?>
  <a href="<?= BASE_URL ?>/budgets/<?= $b['id'] ?>" class="budget-card">
    <div class="budget-card-header">
      <span class="budget-name"><?= htmlspecialchars($b['nom']) ?></span>
      <span class="badge badge-<?= $b['type']==='PARTAGE'?'teal':'blue' ?>"><?= $b['type'] ?></span>
    </div>
    <div class="budget-progress-bar">
      <div class="progress-fill progress-<?= $b['taux']>=100?'red':($b['taux']>=80?'orange':'green') ?>"
           style="width:<?= min($b['taux'],100) ?>%"></div>
    </div>
    <div class="budget-card-footer">
      <span><?= number_format($b['montant_consomme'],0,',',' ') ?> / <?= number_format($b['plafond_global'],0,',',' ') ?> DA</span>
      <span class="taux-badge taux-<?= $b['taux']>=100?'red':($b['taux']>=80?'orange':'green') ?>"><?= $b['taux'] ?>%</span>
    </div>
  </a>
<?php endforeach; ?>
</div>

<!-- ── TRANSACTIONS RÉCENTES ── -->
<div class="section-header">
  <h2><i data-lucide="clock"></i> Transactions récentes</h2>
  <a href="<?= BASE_URL ?>/transactions" class="btn btn-outline btn-sm">Voir tout</a>
</div>
<div class="table-wrapper">
  <table class="data-table">
    <thead><tr>
      <th>Date</th><th>Description</th><th>Catégorie</th><th>Budget</th><th>Montant</th>
    </tr></thead>
    <tbody>
    <?php foreach ($data['transactions'] as $tx): ?>
      <tr>
        <td><?= date('d/m/Y', strtotime($tx['date_op'])) ?></td>
        <td><?= htmlspecialchars($tx['description'] ?: '—') ?></td>
        <td><span class="cat-pill"><?= htmlspecialchars($tx['categorie_nom']) ?></span></td>
        <td><?= htmlspecialchars($tx['budget_nom']) ?></td>
        <td class="tx-amount <?= $tx['type']==='REVENU'?'tx-pos':'tx-neg' ?>">
          <?= $tx['type']==='REVENU'?'+':'-' ?><?= number_format($tx['montant'],2,',',' ') ?> DA
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($data['transactions'])): ?>
      <tr><td colspan="5" class="empty-row">Aucune transaction sur cette période.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ── MODAL AJOUT TRANSACTION ── -->
<?php require_once VIEWS . '/transactions/_modal_add.php'; ?>

<script>
// Données pour Chart.js
const evolutionData = <?= json_encode($data['evolution'], JSON_UNESCAPED_UNICODE) ?>;
const repartitionData = <?= json_encode($data['repartition'], JSON_UNESCAPED_UNICODE) ?>;
 
</script>

<?php require_once VIEWS . '/partials/footer.php'; ?>
