<?php
$pageTitle = 'Inscription — BudgetCollab';
require_once VIEWS . '/partials/header.php';
$csrf = Session::generateCsrf();
$errs = Session::get('errors') ?? [];
$err  = Session::get('error')  ?? '';
$suc  = Session::get('success') ?? '';
?>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">💰 BudgetCollab</div>
    <h1 class="auth-title">Créer un compte</h1>
    <p class="auth-sub">Votre compte sera activé par un administrateur</p>

    <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if ($suc): ?><div class="alert alert-success"><?= htmlspecialchars($suc) ?></div><?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/register" class="auth-form" id="registerForm">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

      <div class="field-row">
        <div class="field-group">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" placeholder="Jean" required
                 value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
          <?php if (isset($errs['prenom'])): ?><span class="field-error"><?= $errs['prenom'] ?></span><?php endif; ?>
        </div>
        <div class="field-group">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" placeholder="Dupont" required
                 value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
          <?php if (isset($errs['nom'])): ?><span class="field-error"><?= $errs['nom'] ?></span><?php endif; ?>
        </div>
      </div>

      <div class="field-group">
        <label for="email">Adresse email</label>
        <div class="input-icon">
          <i data-lucide="mail"></i>
          <input type="email" id="email" name="email" placeholder="vous@exemple.com" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <?php if (isset($errs['email'])): ?><span class="field-error"><?= $errs['email'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="mot_de_passe">Mot de passe <small>(8 caractères min.)</small></label>
        <div class="input-icon">
          <i data-lucide="lock"></i>
          <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" required minlength="8">
          <button type="button" class="toggle-pwd" data-target="mot_de_passe"><i data-lucide="eye"></i></button>
        </div>
        <div class="pwd-strength" id="pwdStrength"></div>
        <?php if (isset($errs['mot_de_passe'])): ?><span class="field-error"><?= $errs['mot_de_passe'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="confirmation">Confirmer le mot de passe</label>
        <div class="input-icon">
          <i data-lucide="lock"></i>
          <input type="password" id="confirmation" name="confirmation" placeholder="••••••••" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">
        <i data-lucide="user-plus"></i> Créer mon compte
      </button>
    </form>
    <p class="auth-link">Déjà un compte ? <a href="<?= BASE_URL ?>/login">Se connecter</a></p>
  </div>
</div>
<?php require_once VIEWS . '/partials/footer.php'; ?>
