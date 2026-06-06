<?php
$pageTitle = 'Connexion — BudgetCollab';
require_once VIEWS . '/partials/header.php';
$csrf = Session::generateCsrf();
?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo"> BudgetCollab</div>
    <h1 class="auth-title">Connexion</h1>
    <p class="auth-sub">Gérez votre budget en toute simplicité</p>

    <!-- Erreurs globales -->
    <?php $errs = Session::get('errors') ?? []; $err = Session::get('error') ?? ''; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/login" class="auth-form" id="loginForm">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

      <div class="field-group">
        <label for="email">Adresse email</label>
        <div class="input-icon">
          <i data-lucide="mail"></i>
          <input type="email" id="email" name="email" placeholder="vous@exemple.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
        </div>
        <?php if (isset($errs['email'])): ?><span class="field-error"><?= $errs['email'] ?></span><?php endif; ?>
      </div>

      <div class="field-group">
        <label for="mot_de_passe">Mot de passe</label>
        <div class="input-icon">
          <i data-lucide="lock"></i>
          <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" required autocomplete="current-password">
          <button type="button" class="toggle-pwd" data-target="mot_de_passe"><i data-lucide="eye"></i></button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">
        <i data-lucide="log-in"></i> Se connecter
      </button>
    </form>

    <p class="auth-link">Pas encore de compte ? <a href="<?= BASE_URL ?>/register">S'inscrire</a></p>
  </div>
</div>

<?php require_once VIEWS . '/partials/footer.php'; ?>
