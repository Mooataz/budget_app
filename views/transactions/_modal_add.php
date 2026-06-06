<?php // views/transactions/_modal_add.php ?>
<div class="modal-overlay" id="modalAddTx">
  <div class="modal">
    <div class="modal-header">
      <h3><i data-lucide="plus-circle"></i> Nouvelle transaction</h3>
      <button class="modal-close" onclick="closeModal('modalAddTx')"><i data-lucide="x"></i></button>
    </div>
    <form id="formAddTx" class="modal-body">
      <input type="hidden" name="csrf_token" id="txCsrf" value="">

      <!-- Type -->
      <div class="type-toggle">
        <label class="type-btn type-depense active" data-type="DEPENSE">
          <input type="radio" name="type" value="DEPENSE" checked>
          <i data-lucide="trending-down"></i> Dépense
        </label>
        <label class="type-btn type-revenu" data-type="REVENU">
          <input type="radio" name="type" value="REVENU">
          <i data-lucide="trending-up"></i> Revenu
        </label>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label>Montant (DT)</label>
          <input type="number" name="montant" step="0.01" min="0.01" placeholder="0.00" required>
        </div>
        <div class="field-group">
          <label>Date</label>
          <input type="date" name="date_op" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <div class="field-group">
        <label>Description</label>
        <input type="text" name="description" placeholder="Ex: Courses du marché">
      </div>

      <div class="field-row">
        <div class="field-group">
          <label>Catégorie</label>
          <select name="categorie_id" id="selectCategorie" required>
            <option value="">— Choisir —</option>
          </select>
        </div>
        <div class="field-group">
          <label>Budget</label>
          <select name="budget_id" id="selectBudget" required>
            <option value="">— Choisir —</option>
            <?php foreach ($data['budgets'] ?? $budgets ?? [] as $b): ?>
              <option value="<?= $b['id'] ?>" data-solde="<?= (float)$b['plafond_global'] - (float)$b['montant_consomme'] ?>">
                <?= htmlspecialchars($b['nom']) ?> (<?= number_format((float)$b['plafond_global'] - (float)$b['montant_consomme'], 0, ',', ' ') ?> DT)
              </option>
            <?php endforeach; ?>
          </select>
          <small id="budgetSoldeInfo" class="field-hint"></small>
        </div>
      </div>

      <div class="field-error" id="txError"></div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalAddTx')">Annuler</button>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="check"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>
