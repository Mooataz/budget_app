<?php
// views/auth/profil.php
$pageTitle = 'Mon Profil — BudgetCollab';
require_once VIEWS . '/partials/header.php';
?>

<div class="page-header">
  <h1 class="page-title"><i data-lucide="user"></i> Mon Profil</h1>
</div>

<div style="max-width:600px; margin:0 auto;">
  <!-- Informations Personnelles -->
  <div class="card" style="background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding:24px; margin-bottom:24px;">
    <h2 style="font-size:18px; margin-bottom:20px;">Informations Personnelles</h2>
    <form method="POST" action="<?= BASE_URL ?>/profil">
      <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
      <div class="field-row">
        <div class="field-group">
          <label>Prénom</label>
          <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
        </div>
        <div class="field-group">
          <label>Nom</label>
          <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
        </div>
      </div>
      <div class="field-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>
      <div style="display:flex; gap:12px;">
        <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Enregistrer</button>
      </div>
    </form>
  </div>

  <!-- Changer le mot de passe -->
  <div class="card" style="background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding:24px;">
    <h2 style="font-size:18px; margin-bottom:20px;">Sécurité</h2>
    <form method="POST" action="<?= BASE_URL ?>/profil/password">
      <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
      <div class="field-group">
        <label>Mot de passe actuel</label>
        <input type="password" name="ancien_mdp" required>
      </div>
      <div class="field-group">
        <label>Nouveau mot de passe</label>
        <input type="password" name="nouveau_mdp" minlength="8" required>
      </div>
      <div class="field-group">
        <label>Confirmer</label>
        <input type="password" name="confirmation" minlength="8" required>
      </div>
      <button type="submit" class="btn btn-primary"><i data-lucide="lock"></i> Changer le mot de passe</button>
    </form>
  </div>

  <!-- Info Compte -->
  <div class="card" style="background: var(--surface-light); border: 1px solid var(--border); border-radius: 8px; padding:16px; margin-top:24px;">
    <div style="font-size:12px; color:var(--text-secondary);">
      <p><strong>Rôle:</strong> <?= $user['role'] ?></p>
      <p><strong>Statut:</strong> <?= $user['statut'] ?></p>
      <p><strong>Inscrit le:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
      <?php if ($user['derniere_connexion']): ?>
        <p><strong>Dernière connexion:</strong> <?= date('d/m/Y H:i', strtotime($user['derniere_connexion'])) ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once VIEWS . '/partials/footer.php'; ?>
