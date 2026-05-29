<?php
// views/categories/index.php
$pageTitle = 'Catégories — BudgetCollab';
require_once VIEWS . '/partials/header.php';
?>
<div class="page-header">
  <div>
    <h1 class="page-title">Catégories</h1>
    <p class="page-sub">Gérez vos catégories personnalisées</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('modalAddCategorie')">
    <i data-lucide="plus"></i> Ajouter une catégorie
  </button>
</div>

<div class="categories-grid">
<?php foreach ($categories as $cat): ?>
  <div class="categorie-card" id="cat-<?= $cat['id'] ?>">
    <div class="cat-icon"><i data-lucide="<?= htmlspecialchars($cat['icone']) ?>"></i></div>
    <div class="cat-name"><?= htmlspecialchars($cat['nom']) ?></div>
    <?php if (!$cat['est_defaut']): ?>
    <button class="btn-icon btn-icon-red" onclick="deleteCategorie(<?= $cat['id'] ?>)">
      <i data-lucide="trash-2"></i>
    </button>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<!-- Modal Ajouter -->
<div class="modal-overlay" id="modalAddCategorie">
  <div class="modal">
    <div class="modal-header">
      <h3><i data-lucide="plus-circle"></i> Nouvelle catégorie</h3>
      <button class="modal-close" onclick="closeModal('modalAddCategorie')"><i data-lucide="x"></i></button>
    </div>
    <form id="formAddCategorie" class="modal-body">
      <div class="field-group">
        <label>Nom</label>
        <input type="text" name="nom" placeholder="Ex: Loisirs" required>
      </div>
      <div class="field-group">
        <label>Icône (lucide-react)</label>
        <select name="icone">
          <option value="tag">Tag</option>
          <option value="shopping-cart">Shopping Cart</option>
          <option value="heart">Heart</option>
          <option value="book">Book</option>
          <option value="music">Music</option>
          <option value="home">Home</option>
          <option value="utensils">Utensils</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalAddCategorie')">Annuler</button>
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
